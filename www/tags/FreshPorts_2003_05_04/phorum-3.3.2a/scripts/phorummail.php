#!/usr/local/bin/php -q
<?php
  // This script can take an email and post the contents into a forum.  To do
  // this you will have to direct your mail to this script.  This is done
  // differently in different mail servers.  Consult your mail server docs or
  // your systems admin on how to redirect mail to a script.  At some point in
  // that process you will need to know what command to issue when redirecting
  // the mail.  The usage info is below.

  // usage:
  //  phorummail forum=FORUM_ID [path=PATH_TO_PHORUM]

  // FORUM_ID is the id of the forum you want the messages posted to.  You see
  // this when a new forum is created.  You can also find it in the URL for a
  // forum as the value of f (f=X, X would be the forum id).

  // PATH_TO_PHORUM is the just that.  If you have just one Phorum install you
  // can enter this in the variables below and not have to worry about it on
  // the command line later.  Putting a different value on the command line will
  // overwrite what is put in the script itself.

  // The path must be supplied in either the file or on the command line.

  // sample command line:
  // /path/to/script/phorummail forum=1 path=/usr/www/phorum

  // some vars
  $PhorumMail=true; // Do not touch.
  $phorum_path="";  // this the path to the main Phorum dir.  You can send this on the command line as well.
  $admin_email="";  // This should match the default email for Phorum.

  // read args
  reset($argv);
  while(list(,$arg) = each($argv)) {
    if(ereg("^forum=([0-9]+)", $arg, $regs)){
      $num=$regs[1];
    }
    elseif(ereg("^path=(.+)", $arg, $regs)){
      $phorum_path=$regs[1];
    }
    elseif(ereg("^verbose", $arg)){
      $verbose=true;
    }
  }

  // Get input

  $stdin=file("php://stdin");
//  $stdin=file("/dev/stdin");  use if PHP version is less than 3.0.15
  $message=implode("", $stdin);

  // check for all we need.
  @chdir($phorum_path);
  $badpath=true;
  if(file_exists("./common.php")){
    $badpath=false;
    include "./common.php";
    include "$include_path/post_functions.php";
    $phorummailcode = strtolower($PhorumMailCode);
    $email=$DefaultEmail;
  }
  if($message=="" || empty($num) || $badpath){
    if(empty($num) || $badpath){
      $error ="PhorumMail could not run for the following reason(s):\n\n";
      if(empty($num)) $error.="     The forum id was not specified.\n";
      if($badpath){
        $email=$admin_email;
        $error.="     The path supplied to PhorumMail could not be found.\n";
        $error.="     If not supplied in the file, it must be on the command line.\n";
      }
      $error.="\n";
      $error.="An example of a correct PhorumMail command line is:\n\n";
      $error.="  /usr/local/bin/phorummail forum=5 path=/usr/home/www/phorum\n\n";
      $error.="A copy of the message is included below.\n\n";
      $error.="====================================================================\n\n";
      $error.="$message";
      mail($email, "PhorumMail failure", $error, "From: PhorumMail <$email>\nReturn-Path: Phorummail <$email>");
    }
    exit();
  }

  // read in headers
  reset($stdin);
  // Would be nice to use array_shift() here, but that's PHP4 only.
  while (list($linenum,$line) = each($stdin)) {
    $line = trim($line);
    $parts=explode(": ", $line);
    $type=$parts[0];
    unset($parts[0]);
    $value=trim(implode(": ", $parts));
    // Use strtolower to avoid case problems
    $eHeaders[strtolower($type)]=$value;
    unset($stdin[$linenum]);
    if (empty($line)) {
      break;
    }
  }

  // read in the body
  ## We do the \r\n -> \n conversion in the post function now.
  $body=trim(implode("", $stdin));
  $body.="\n\n";
  $body.="------------------------------------------\n";
  $body.="Posted to Phorum via PhorumMail\n";


  if (empty($eHeaders["date"])) {
    $datestamp = date("Y-m-d H:i:s");
  } else {
    $datestamp = date("Y-m-d H:i:s", strtotime($eHeaders["date"], time()));
  }


  if(@ereg("([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)", $eHeaders["received"], $regs)){
    $ip=$regs[0];
  } else{
    $ip="PhorumMail";
  }
  $host = @gethostbyaddr($ip);

  if (empty($eHeaders["from"])) {
    $author='';
    $email='';
  } else {
    if(ereg('"([^"]+)" <([^>]+)>', $eHeaders["from"], $regs)){
      $author=$regs[1];
      $email=$regs[2];
    } else if(ereg('([^<]+)<([^>]+)>', $eHeaders["from"], $regs)){
      $author=trim($regs[1]);
      $email=$regs[2];
    } else if(substr($eHeaders["from"],0,1)=="<"){
      $author=substr($eHeaders["from"], 1, -1);
      $email=$author;
    } else{
      $author=$eHeaders["from"];
      $email=$eHeaders["from"];
    }
  }

  $thread = 0;
  $parent = 0;
  if (empty($eHeaders["subject"])) {
    $subject = '';
  } else {
    $subject = trim($eHeaders["subject"]);
    // Strip out [$ForumEmailTag] if it exists.
    // Use eregi, because some software seems to tamper with the case of the tag!
    if (eregi("^([^[]*)\\[$ForumEmailTag\\](.*)$", $subject, $regs)) {
      $subject = trim($regs[1]) . ' ' . trim($regs[2]);
      $subject = trim($subject);
    }
    // Look for "[forum:thread:parent]" at the end of the subject
    // We aren't actually using these anymore, but their presence would
    // screw up the threading.
    if (ereg('^(.+) +\[([0-9]+):([0-9]+):([0-9]+)\]$', $subject, $regs)) {
      $subject = $regs[1];
//      $forum=$regs[2];
//      $thread=$regs[3];
//      $parent=$regs[4];
    }
  }

  $forum = $num;
  if (!empty($eHeaders["x-phorum-$phorummailcode-forum"])
     && (strcmp($eHeaders["x-phorum-$phorummailcode-forum"],$ForumName) == 0)) {
    if (!empty($eHeaders["x-phorum-$phorummailcode-thread"])) {
      $thread = $eHeaders["x-phorum-$phorummailcode-thread"];
    }
    if (!empty($eHeaders["x-phorum-$phorummailcode-parent"])) {
      $parent = $eHeaders["x-phorum-$phorummailcode-parent"];
    }
  }

  $action="post";
  $toaddress = empty($eHeaders["to"]) ? '' : $eHeaders["to"];

  $msgid = empty($eHeaders["message-id"]) ? '' : $eHeaders["message-id"];

  // The email this is a reply to should be in In-Reply-To, but some
  // mailers seem to use References instead.
  // Both fields can have multiple message ids in them, so just grab the first.
  $inreplyto = '';
  if (@ereg('^(<[^>]+>)', $eHeaders["in-reply-to"], $regs)) {
    $inreplyto = $regs[1];
  } else if (@ereg('^(<[^>]+>)', $eHeaders["references"], $regs)) {
    $inreplyto = $regs[1];
  }

  $IsError = check_data($host, $author, $subject, $body, $email);
  if (!empty($IsError)) {
    violation();
  }

  $author=trim($author);
  $subject=trim($subject);
  $email=trim($email);
  $body=chop($body);

  list($author, $subject, $email, $body) = censor($author, $subject, $email, $body);

  $author = addslashes($author);
  $email = addslashes($email);
  $subject = addslashes($subject);
  $body = addslashes($body);

  $plain_author=stripslashes($author);
  $plain_subject=stripslashes(ereg_replace("<[^>]+>", "", $subject));
  $plain_body=stripslashes(ereg_replace("<[^>]+>", "", $body));

  $author = htmlspecialchars($author);
  $email = htmlspecialchars($email);
  $subject = htmlspecialchars($subject);

  $org_attachment = '';

  $body=eregi_replace("</*HTML>", "", $body);
  if($ForumAllowHTML=="Y"){
    $body="<HTML>$body</HTML>";
  }

  // If there are no Phorum headers, put it in the database.
  if(empty($eHeaders["x-phorum-$phorummailcode-version"])){
    post_to_database();
  }

  // Notify people who wanted replies sent to them
  post_to_email();

?>
