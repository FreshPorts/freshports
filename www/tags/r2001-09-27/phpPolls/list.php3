<? 
//
// this script displays a list of all available polls
// 
require('phpPollConfig.php3');
require('phpPollUI.php3');
?>
<html>
<head>
<title>Quick Polls History</title>
<style type="text/css">
<!--
body {  font-family: Arial, Helvetica, sans-serif; font-size: 10pt}
p { font-family: Arial, Helvetica, sans-serif; font-size: 10pt }
td { font-family: Arial, Helvetica, sans-serif; font-size: 10pt }
th { font-family: Arial, Helvetica, sans-serif; font-size: 10pt }
h1 {  font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 16pt; color: #000099}
h2 {  font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 14pt; color: #000099}
-->
</style>
</head>
<body>
<h1>Quick Polls History</h1>

<table>
<tr>
  <td colspan=2>
    <h3>Here are all the polls you created.</h3>
  </td>
</tr>

<?
$allPolls = poll_listPolls();
for ($count = 0; $count < count($allPolls); $count++) {
  $id = $allPolls[$count][0];
  $pollTitle = $allPolls[$count][1];
  echo("<tr>\n");
  echo("<td>$pollTitle</td>\n");
  echo("<td><a href=vote.php3?pollID=$id>vote</a></td><td><a href=view.php3?pollID=$id>view results</a></td><td><a href=results.php3?pollID=$id>alternative results</a></td>\n");
  echo("</tr>\n");
}
?>

</table>

<p><a href=phpPollAdmin.php3>Main Menu</a></p>

</body>
</html>
