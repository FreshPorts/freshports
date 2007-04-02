<?php

function ValidChoice($value) {
  return $value === 'A' || $value === 'B' || $value === 'C';
}

function ValidHash($value, $hash) {
#  echo "\$value='$value'<br>\n";
#  echo "\$hash='$hash'<br>\n";
#  echo "myhash($value)='" . myhash($value) . "'<br>\n";
  return $hash == myhash($value);
}

function ProcessVote($db, $UserID, $choice1, $choice2, $choice3) {
  if (empty($choice1) || empty($choice2) || empty($choice3)) {
    return 'Please make a selection for each choice.';
  }

  if ($choice1 === $choice2 || $choice1 === $choice3 || $choice2 == $choice3) {
    return 'Please do not vote for the same choice twice.';
  }
}

function AlreadyVoted($db, $ID) {
  $sql = "SELECT count(*) FROM design_results WHERE user_id = $ID";
  return false;
}

function myhash($value) {
  $hash = md5('Florida' . $value . 'Voting123');
  return $hash;
}

function DisplayVotingForm() {
?>
<form action="/voting.php">
My first choice is:
<input type=radio name="choice1"   VALUE="A">A
<input type=radio name="choice1"   VALUE="B">B
<input type=radio name="choice1"   VALUE="C">C

<hr>

My second choice is:
<input type=radio name="choice2"   VALUE="A">A
<input type=radio name="choice2"   VALUE="B">B
<input type=radio name="choice2"   VALUE="C">C

<hr>

My third choice is:

<input type=radio name="choice3"   VALUE="A">A
<input type=radio name="choice3"   VALUE="B">B
<input type=radio name="choice3"   VALUE="C">C

<INPUT TYPE=hidden name="hash" VALUE="<?php GLOBAL $User; echo myhash($User->id); ?>">

<hr>

<INPUT TYPE=SUBMIT VALUE="submit">
</form>
<?php
}
?>
