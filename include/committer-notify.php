<?php
	#
	# Copyright (c) 2026 Dan Langille
	#
	# Committer opt-in, confirmed by mail.
	#
	# A committer who opts in on committer-opt-in.php gets a mail from
	# ingress (committer_opt_in.pm in ingress-modules) when FreshPorts has
	# trouble processing one of their commits.  That mail goes to
	# <login>@FreeBSD.org.  The login is typed into the form and the
	# account's own address is not confirmed, so the opt-in itself has to be
	# confirmed from that mailbox: a new or changed opt-in is stored as
	# pending (committer_notify.status 'P') and a link is mailed to
	# <login>@FreeBSD.org.  committer-notify.php turns it on (status 'A')
	# when the link is used, and ingress mails only active opt-ins.  The
	# same link, and the one at the end of every sanity-test mail, stops
	# the mails.
	#

	require_once('/usr/local/share/phpmailer/PHPMailer.php');
	require_once('/usr/local/share/phpmailer/SMTP.php');
	require_once('/usr/local/share/phpmailer/Exception.php');

	# how long the link in the confirmation mail works
	const COMMITTER_NOTIFY_CONFIRM_DAYS = 7;

	# no second confirmation mail for the same pending opt-in within this time,
	# so the form cannot be used to flood a committer's mailbox
	const COMMITTER_NOTIFY_RESEND_MINUTES = 60;

	# a FreeBSD login: lower case letters, digits, '_' and '-'
	function freshports_CommitterLoginValid(string $Login) : bool {
		return preg_match('/^[a-z0-9][a-z0-9_-]{0,31}$/', $Login) === 1;
	}

	# a token from a committer-notify.php link: 64 hex digits
	function freshports_CommitterNotifyTokenValid($Token) : bool {
		return is_string($Token) && preg_match('/^[0-9a-f]{64}$/', $Token) === 1;
	}

	#
	# Subscribe, or change the login of, this user's opt-in.  It becomes
	# pending, with a new token, and the confirmation mail goes out.
	# Returns the message to show on the page.
	#
	function freshports_CommitterNotifyRequest($db, $User, string $Committer) : string {
		$sql = "SELECT committer, status,
		               token_created > now() - make_interval(mins => $2) AS recent
		          FROM committer_notify
		         WHERE user_id = $1";
		$result = pg_query_params($db, $sql, array($User->id, COMMITTER_NOTIFY_RESEND_MINUTES)) or die(freshports_QueryFailed($db));
		$row = pg_num_rows($result) ? pg_fetch_array($result, 0) : null;

		if ($row && $row['committer'] === $Committer) {
			if ($row['status'] === 'A') {
				return sprintf(_('Nothing changed: you are already subscribed as %s.'), $Committer);
			}
			if ($row['recent'] === 't') {
				return sprintf(_('A confirmation was sent to %s@FreeBSD.org less than an hour ago.  Please use the link in that message.'), $Committer);
			}
		}

		$sql = "INSERT INTO committer_notify (user_id, committer, status)
		        VALUES ($1, $2, 'P')
		        ON CONFLICT (user_id) DO UPDATE
		           SET committer     = EXCLUDED.committer,
		               status        = 'P',
		               token         = encode(gen_random_bytes(32), 'hex'),
		               token_created = now()
		        RETURNING token";
		$result = pg_query_params($db, $sql, array($User->id, $Committer)) or die(freshports_QueryFailed($db));
		$Token = pg_fetch_result($result, 0, 'token');

		syslog(LOG_NOTICE, "committer opt-in requested: user '" . $User->name . "' (" . $User->id . ") for '$Committer'; confirmation mailed");

		if (!freshports_CommitterNotifySendConfirmation($Committer, $Token, $User->name)) {
			return sprintf(_('Your opt-in for %s is waiting for confirmation, but the confirmation mail could not be sent.  Please try again later.'), $Committer);
		}

		return sprintf(_('We sent a link to %1$s@FreeBSD.org.  The opt-in starts when that link is used.  It works for %2$d days.'), $Committer, COMMITTER_NOTIFY_CONFIRM_DAYS);
	}

	function freshports_CommitterNotifySendConfirmation(string $Committer, string $Token, string $AccountName) : bool {
		GLOBAL $FreshPortsSlogan;

		$URL = 'https://' . $_SERVER['HTTP_HOST'] . '/committer-notify.php?token=' . $Token;

		# sent in the language the visitor is using now - re #678
		// TRANSLATORS: keep the line breaks short, this is a plain text email.  %1$s is a FreshPorts account name, %2$s the committer's login
		$message = sprintf(_("The FreshPorts account '%1\$s' asked FreshPorts to mail\n" .
		                     "%2\$s@FreeBSD.org when FreshPorts has trouble processing\n" .
		                     "a port change committed by %2\$s."), $AccountName, $Committer) . "\n\n" .
		           _("Nothing will be sent unless you confirm.  To confirm, or to\nrefuse, use this link:") . "\n\n" .
		           $URL . "\n\n" .
		           sprintf(_("The link works for %d days.  If you did not ask for this,\nyou can ignore this message."), COMMITTER_NOTIFY_CONFIRM_DAYS) . "\n\n" .
		           sprintf(_("The request came from %s"), $_SERVER['REMOTE_ADDR']) . "\n" .
		           "\n" .
		           "-- \n" .
		           WEBSITE_NAME . " - https://" . $_SERVER["HTTP_HOST"] . "/ -- $FreshPortsSlogan";

		try {
			$mail = new PHPMailer\PHPMailer\PHPMailer;

			$mail->IsSMTP();
			$mail->Host       = MAIL_SERVER;
			$mail->Port       = 25;
			$mail->SMTPDebug  = 0;

			$mail->ContentType = 'text/plain';
			$mail->CharSet     = 'UTF-8';  # translations need more than Latin-1
			$mail->Subject     = sprintf(_('%s - confirm committer opt-in'), WEBSITE_NAME);
			$mail->Body        = $message;

			$mail->setFrom   (PROBLEM_SOLVER_EMAIL_ADDRESS, WEBSITE_NAME);
			$mail->addReplyTo(PROBLEM_SOLVER_EMAIL_ADDRESS, WEBSITE_NAME);

			$mail->addAddress($Committer . '@FreeBSD.org');

			if ($mail->send()) {
				return true;
			}
			syslog(LOG_ERR, "committer opt-in confirmation to '$Committer' send() failed with: " . $mail->ErrorInfo);
		} catch (PHPMailer\PHPMailer\Exception $e) {
			syslog(LOG_ERR, "committer opt-in confirmation to '$Committer' failed with PHPMailer: " . $e->getMessage());
		}

		return false;
	}
