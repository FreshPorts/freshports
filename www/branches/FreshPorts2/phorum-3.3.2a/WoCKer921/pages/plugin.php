<?php check_security(); ?>
<?php
/* Plugin Managment */

if(@$do == "props" && !empty($plugin)){
  $plugindirname = $plugin;
  $pluginprops = TRUE;
  include("./plugin/$plugin/admin.php");
} else {
  if(isset($action) && isset($plugin)){
    if($action=="activate_plugin"){
        $PHORUM["plugins"][$plugin]=true;
        QueMessage("Plugin Activated.");
    } elseif($action=="deactivate_plugin"){
        $PHORUM["plugins"][$plugin]=false;
        QueMessage("Plugin Deactivated.");
    }
    writefile();
  }
?>
<p>
<table border="0" cellspacing="0" cellpadding="3" class="box-table">
<tr>
<td colspan="2" align="center" valign="middle" class="table-header">Manage Plugins</td>
</tr>
<tr>
<?php
  $dir = opendir("./plugin/");
  $num=0;
  while($plugindirname = readdir($dir)) {
    if($plugindirname[0] != ".") {
      if(@file_exists("./plugin/$plugindirname/plugin.php")) {
        unset($pluginname); unset($plugindesc); unset($pluginversion);
        include("./plugin/$plugindirname/info.php");
        if(isset($pluginname) && isset($plugindesc) && isset($pluginversion)) {
          echo "<tr><td align=\"left\" valign=\"middle\" bgcolor=\"#FFFFFF\"><b>$pluginname v$pluginversion<br>$plugindesc</td>";
          echo "<td align=\"left\" valign=\"middle\" bgcolor=\"#FFFFFF\">";
          if(file_exists("./plugin/$plugindirname/admin.php")){
              echo "<a href=\"$myname?page=plugin&plugin=$plugindirname&do=props\">Properties</a> | ";
          }
          if(!empty($PHORUM["plugins"][$plugindirname])){
            echo "<a href=\"$myname?page=plugin&plugin=$plugindirname&action=deactivate_plugin\">Deactivate</a>";
          }
          else{
            echo "<a href=\"$myname?page=plugin&plugin=$plugindirname&action=activate_plugin\">Activate</a>";
          }
          echo "</td></tr>\n";
          $num++;
        }
      }
    }
  }
  closedir($dir);
  if($num == 0) {
    echo("<tr><td align=\"left\" valign=\"middle\" bgcolor=\"#FFFFFF\">");
    echo("There are no plugins with admin support installed.");
    echo("</td>\n<tr>");
  }
  echo "</tr>\n</table>";
}
?>