<?PHP /* Advanced Delete */ ?>
<?PHP
  function ShowIDs () {
    global $DB, $q, $ForumTableName ;

    $sqlstr  = "select id,datestamp,author,subject,thread from $ForumTableName where id<>thread order by datestamp desc" ;
    $q->query($DB, $sqlstr) ;
    if($q->numrows()>0){
      echo "Listed By ID:<br>\n";
      echo "<select name=\"idlist\" size=\"10\" multiple class=\"BIG\">\n" ;
      $index = 1 ;
      while ( $row = $q->getrow() ) {
        echo "<option value=$row->id>$row->subject by $row->author on $row->datestamp [$row->id, $row->thread]</option>\n";
        $index++ ;
      }
      echo "</select>\n" ;
      echo "<input type=hidden name=idcount value=$index>\n" ;  
    }
  }

  function ShowThreads () {
    global $DB, $q, $ForumTableName ;

    $sqlstr  = "select id,datestamp,thread,author,subject from $ForumTableName where id=thread order by datestamp desc" ;
    $q->query($DB, $sqlstr) ;
    if($q->numrows()>0){
      echo "Listed By Thread:<br>\n";
      echo "<select size=10 multiple name=threadlist[] class=\"BIG\">\n" ;
      $index = 1 ;
      while ( $row = $q->getrow() ) {
        echo "<option value=$row->thread>$row->subject by $row->author on $row->datestamp [$row->id, $row->thread]</option>\n" ;
        $index++ ;
      }
      echo "</select>\n" ;
      echo "<input type=hidden name=threadcount value=$index>\n" ;
    }
  }
?>
<form action="<? echo $myname; ?>" method="POST">
<input type="Hidden" name="action" value="del">
<input type="Hidden" name="num" value="<?PHP echo $num; ?>">
<input type="hidden" name="type" value="adv">
<center>
<table border="1" cellspacing="0" cellpadding="3">
<tr>
<td align="center" valign="middle" bgcolor="#000080"><font face="Arial,Helvetica" color="#FFFFFF"><b>Advanced Delete: <?PHP echo $ForumName; ?></b></font></td>
</tr>
<tr>
<td align="center" valign="middle" bgcolor="#FFFFFF"><font face="Arial,Helvetica">
<?PHP ShowThreads(); ?><p>
<?PHP ShowIDs(); ?><p>
</font></td>
</tr>
</table>
<p>
<center><input type="Submit" name="submit" value="Delete" class="BUTTON"></center><p>
<table width="50%" cellspacing="2" cellpadding="2" border="0">
<tr><td>
<font face="Arial,Helvetica"><b>Instructions:</b> Multiple select messages by ID or messages by Thread.  You can select
messages in both list boxes.  Messages that are the start of a thread are not displayed
in the list box for message by ID, they are displayed in the listbox for Threads only.<p>
Bracketed numbers are message id and thread id respectively.</font>
</td>
</tr>
</table>
</center>
</font></form>
