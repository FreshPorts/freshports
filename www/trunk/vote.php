<?php
  #
  # $Id: vote.php,v 1.1 2007-04-02 01:53:18 dan Exp $
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