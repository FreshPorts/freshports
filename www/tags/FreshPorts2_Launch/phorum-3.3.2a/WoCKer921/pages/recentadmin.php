<table border="0" cellspacing="0" cellpadding="3" class="box-table">
<TR>
    <TD HEIGHT=21 WIDTH="100%" class="table-header">&nbsp;<?php echo $lTopics;?></TD>
    <TD HEIGHT=21 NOWRAP WIDTH=150 class="table-header"><?php echo $lAuthor;?>&nbsp;</TD>
    <TD HEIGHT=21 NOWRAP WIDTH=40 class="table-header"><?php echo $lDate;?></TD>
    <TD HEIGHT=21 NOWRAP WIDTH=40 class="table-header">Actions</TD>
</TR>

<?php
require "./common.php";
$nav = '';
if (!isset($navigate) || empty($navigate)) $navigate = 0;

if (isset($q)) {
  $sSQL="SELECT id, name, table_name, parent, folder, description FROM ".$pho_main." WHERE active=1 AND id=$num";
  if ($SortForums) $sSQL.=" ORDER BY name";
  $q->query($DB, $sSQL);
  $rec=$q->getrow();
}
else {
  $rec = '';
}

if (is_array($rec)) {
  $empty=false;
  $name=$rec["name"];
  $table=$rec["table_name"];
  $i++;
  $num=$rec["id"];
  if (!$rec["folder"]) {
    $sSQL = "SELECT * from $table WHERE approved='N' ORDER BY datestamp DESC";
    $pq=new query($DB, $sSQL);
    $pq->query($DB, $sSQL);
    $x=1;
    while ($tam=$pq->getrow()) {
      $subject=$tam["subject"];
      $id=$tam["id"];
      $topic=$tam["thread"];
      $person=$tam["author"];
      $datestamp = date_format($tam["datestamp"]);
      $approved = $tam["approved"];
      if (($x%2)==0) { $bgcolor=$ForumTableBodyColor1; }
      else { $bgcolor=$ForumTableBodyColor2; }
      $x++;
      $nav.='<TR><TD '.bgcolor($bgcolor).'>';
      $nav.="<A HREF=\"$forum_url/$read_page.$ext?admview=1&f=$num&i=".$tam["id"]."&t=${topic}\">";
      $nav.="$subject</A></TD>";
      $nav.='<TD '.bgcolor($bgcolor).">$person</TD><TD ".bgcolor($bgcolor).">";
      $nav.="$datestamp</TD>";
      $nav.='<TD '.bgcolor($bgcolor)."><A HREF=\"${myname}?page=recentadmin&action=del&type=quick&id=${id}";
      $nav.="&num=${num}&navigate=${navigate}&thread=${topic}\">Delete</A>&nbsp;|&nbsp;";
      $nav.="<A HREF=\"${myname}?page=edit&srcpage=recentadmin&id=${id}&num=${num}&navigate=${navigate}&mythread=${topic}\">";
      $nav.="Edit</A>&nbsp;|&nbsp;";
      $nav.="<A HREF=\"${myname}?page=recentadmin&action=moderate&approved=${approved}&id=${id}&num=${num}&navigate=${navigate}";
      $nav.="&mythread=${topic}\">";
      if ($approved == 'Y') { $nav.="Hide"; } else { $nav.="Approve"; }
      $nav.="</A></TD></TR>\n";
    }
  }
  $rec=$q->getrow();
}
else {
  $nav.="No active forums";
}

$nav.='</TABLE>';
print "$nav";
?>
