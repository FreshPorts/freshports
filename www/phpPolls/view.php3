<?php
//
// This script displays a poll's results using poll_viewResults()
//
if(!isset($pollID))
  $pollID = 1;
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
    <td valign="top" width="100%">
<h3>This is a quick test of the results display interface for the poll #<?php echo $pollID; ?></h3>
<p>The following interface (not including horizontal separators) is generated by a call to poll_viewResults($pollID):</p>
<hr>
<?php poll_viewResults($pollID); ?>
<hr>
<p><a href=list.php3>All polls</a></p>
    </td>
  </tr>
</table>
</body>
</html>
