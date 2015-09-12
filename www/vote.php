<?php
  #
  # $Id: vote.php,v 1.2 2007-04-02 03:34:14 dan Exp $
  #
  # Copyright (c) 1998-2003 DVL Software Limited
  #

  require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
  require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
  require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
  require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');
  require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/design-voting/voting.php');
  $Debug = 0;

  $Title = "FreshPorts Design Contest - Voting";

  freshports_Start($Title,
                    'freshports - new ports, applications',
                    'FreeBSD, index, applications, ports');
?>
<?php echo freshports_MainTable(); ?>

<tr><td valign="top" width="100%" colspan="2">
<table width="100%" border="0">
<tr>
    <? echo freshports_PageBannerText($Title); ?>
    </tr>
</table>
</td></tr>
<tr><td valign="top" width="100%">
<?php                        
  if (!$User->id) {
    echo 'You must login to vote.';
  } else {
    if (AlreadyVoted($db, $User->id)) {
      echo 'You have already voted.  Thank you';
    } else {
?>
<p>
Read about the <a href="http://news.freshports.org/2007/01/30/freshports-design-contest/">FreshPorts Design Contest</a> first. Then
<a href="/DesignContestVoting/">review the options</a> for the future.  You can vote for the two submissions
or for the status quo (option C).
<p>
NOTE: You may vote only once.  You cannot amend your vote.
Play nice.
</p>
<?php
      DisplayVotingForm();
    }
  }
                                            
?>
</td><td>
<?php
  echo freshports_SideBar();
?>
</table>
                
<?php
echo freshports_ShowFooter();
?>                