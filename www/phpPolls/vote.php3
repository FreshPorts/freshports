<? 
//
// this script displays a UI for the $pollID.
// http://your-web-server/phpPolls/vote.php3?pollID=1
//
if(!isset($pollID))
  $pollID = 1;
if(!isset($url))
  $url = sprintf("view.php3?pollID=%d", $pollID);

require('phpPollConfig.php3');
require('phpPollUI.php3');
?>
<html>
<head>
<title>Quick Poll Test</title>
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
<h1>Quick Poll Test</h1>
<table width="600" border="0" cellpadding="3">
  <tr>
    <td valign="top" width="100%"><h3>This is a quick test of the voting interface for the poll #<? echo $pollID; ?></h3>
    <p>On your right is the user interface created by the phpPolls function poll_generateUI($poll_id,
    $poll_forwarder).&nbsp; A web site visitor can vote by pressing the button. As a result the
    selection will be submitted to the phpPolls poll collector module.&nbsp; After that, the user will
    be redirected to the url $poll_forwarder which in this case is &quot;<? echo($url); ?>&quot;.
    &nbsp; The production site will forward each visitor to the page displaying poll results.</p>
    <p>&nbsp;</td>
    <td valign="top"><table border="1" cellspacing="0">
      <tr>
        <td bgcolor="#C0C0C0" align="center"><b>Quick Poll</b></td>
      </tr>
      <tr>
        <td nowrap>
<?poll_generateUI($pollID, $url);?>
        </td>
      </tr>
    </table>
    </td>
  </tr>
</table>
</body>
</html>
