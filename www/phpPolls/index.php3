        <table border="0" align="CENTER" cellspacing="5" cellpadding="5">
          <tr>
            <td>
              <div align="CENTER">
                <h1>phpPolls 1.0</h1>
              </div>
              <p align="LEFT">
                 phpPolls is intended to setup a complete voting booth on your website, 
                 featuring the ability to setup, administer, conduct and view polls in an easy manner.
              </p><p>
              <p align="LEFT"><a href="/phpPolls/phpPolls_1.0.0.tar.gz" class="navigation">Download 
                Version 1.0</a></p>
            </td>
          </tr>
        </table>

<?
require ("phpPollConfig.php3");
require ("phpPollUI.php3");

echo "<p>This is a sample poll to demonstrate the features of phpPolls. Please vote!</p>";

poll_viewResults(1, "<table>", "<tr>", "<td>", "</td>", "</tr>", "</table>");

echo "<p>To vote, please enter your choice below:</p>";

poll_generateUI(1, "http://phpwizard.net/phpPolls/");

echo "<p>After voting, you will be forwarded back to this page.</p>";
?>
