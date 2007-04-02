<?php
  #
  # $Id: voting.php,v 1.1 2007-04-02 01:53:18 dan Exp $
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
  $choice1 = '';
  $choice2 = '';
  $choice3 = '';
  $hash    = '';
  
  if (IsSet($_GET['choice1']) && ValidChoice($_GET['choice1'])) {
    $choice1 = $_GET['choice1'];
  }

  if (IsSet($_GET['choice2']) && ValidChoice($_GET['choice2'])) {
    $choice2 = $_GET['choice2'];
  }

  if (IsSet($_GET['choice3']) && ValidChoice($_GET['choice3'])) {
    $choice3 = $_GET['choice3'];
  }

  if (IsSet($_GET['hash'])) {
    $hash = $_GET['hash'];
  }
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
      if (ValidHash($User->id, $hash)) {
        $result = ProcessVote($db, $User->id, $choice1, $choice2, $choice3);
        echo $result;
      } else {
        echo 'You are not the person you claim to be.  We know of your evil plan and have thwarted it.  Be gone!';
        syslog(LOG_ERR, 'hash checksum failed for ' . $User->id . ' with ' . $_SERVER['REQUEST_URI']);
      }
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