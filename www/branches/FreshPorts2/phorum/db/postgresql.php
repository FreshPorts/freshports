<?PHP 

if ( !defined( "_DB_LAYER" ) ){
  define("_DB_LAYER", 1 );

class db {

  var $connect_id;
  var $type;

  function db($database_type="postgresql") {
      $this->type=$database_type;
  }

  function open($database, $host, $user, $password) {
    $connect_string="";

    if(!$database) return 0;

    $host = split(":", $host);
    if ($host[0]) $connect_string .= "host=$host[0]";
    if (isset($host[1])) $connect_string .= " port=$host[1]";

    if ($user) $connect_string .= " user=$user ";
    if ($password) $connect_string .= " password=$password ";

    $connect_string .= " dbname=$database";
	$this->connect_id=pg_Connect($connect_string);
	if($this->connect_id){
		pg_exec($this->connect_id, "SET DateStyle TO 'ISO'");
	}
	return $this->connect_id;
  }

  function drop_sequence($sequence){
    $esequence=$sequence."_seq";
    $sSQL="DROP SEQUENCE $esequence";
    $query=new query($this, $sSQL);
    return $query->error();  
  }

  function reset_sequence($sequence, $newval){
    $this->nextid($sequence);
    $esequence=$sequence."_seq";
    $sSQL="setval('$eseqquence', $newval)";
    $query=new query($this, $sSQL);
    return $query->error();  
  }

  function nextid($sequence) {
    $esequence=$sequence."_seq";
    $query=new query($this, "select nextval('$esequence') as nextid");
    if ($query->result){
      $nextid=$query->field("nextid", 0);
    }
    else{
      $query->query($this, "create sequence $esequence");
      if ($query->result){
        $nextid=$this->nextid($sequence);
      }
      else{
        $nextid=0;
      }
    }
    return $nextid;
  }           
     
  function close() {
  // Closes the database connection and frees any query results left.

    $query=new query($this, "commit");
    if ($this->query_id && is_array($this->query_id)) {
      while (list($key,$val)=each($this->query_id)) {
        @@pg_free_result($val);
      }
    }
    $result=@@pg_close($this->connect_id);
    return $result;
  }

  function addquery($query_id) {
  // Function used by the constructor of query. Notifies the
  // this object of the existance of a query_result for later cleanup
  // internal function, don't use it yourself.

    $this->query_id[]=$query_id;
  }
  
};

/*********************************** QUERY *********************************/

class query {

  var $result;
  var $row;
  var $curr_row;

  function query(&$db, $query="") {
  // Constructor of the query object.
  // executes the query, notifies the db object of the query result to clean
  // up later
    if($query!=""){
      if (!empty($this->result)) {
        $this->free(); // query not called as constructor therefore there may
                       // be something to clean up.
      }
      $this->result=@pg_Exec($db->connect_id, $query);
      $db->addquery($this->result);
      $this->curr_row=0;
    }
  }
  
  function getrow() {
  // Gets the next row for processing with $this->field function later.

    $this->row=@pg_fetch_array($this->result, $this->curr_row);
    $this->curr_row++;
    return $this->row;
  }
   
  function numrows() {
  // Gets the number of rows returned in the query
  
    $result=@pg_numrows($this->result);
    return $result;
  }

  function error() {
  // Gets the last error message reported for this query
  
    $result=@pg_errormessage();
    return $result;
  }

  function field($field, $row="-1") {
  // get the value of the field with name $field
  // in the current row or in row $row if supplied

    if($row!=-1){
      $result=@pg_result($this->result, $row, $field);
    }
    else{
      $result=$this->row[$field];
    }

    return $result;
  }

  function firstrow() {
  // return the current row pointer to the first row 
  // (CAUTION: may execute the query again!! (e.g. for oracle))
 
    $this->curr_row=0;
    return $this->getrow();
  }

  function free() {
  // free the postgresql result tables

    return @@pg_FreeResult($this->result);
  }

}; // End class

// Custom Create Table Section

  function create_table(&$DB, $table, $table_name){
    GLOBAL $q;
    if($table=="main"){
      $sSQL="CREATE TABLE $table_name (id INT4 DEFAULT '0' NOT NULL, datestamp datetime NOT NULL, thread INT4 DEFAULT '0' NOT NULL, parent INT4 DEFAULT '0' NOT NULL, author char(37) DEFAULT '' NOT NULL, subject char(50) DEFAULT '' NOT NULL, email char(50) DEFAULT '' NOT NULL, host char(50) DEFAULT '' NOT NULL, email_reply char(1) NOT NULL DEFAULT 'N', approved char(1) NOT NULL DEFAULT 'N', CONSTRAINT ".$table_name."pri_key PRIMARY KEY(id))";
      $q->query($DB, $sSQL);
      if(!$q->error()){
        $sSQL="CREATE INDEX ".$table_name."_author on $table_name(author)";
//        echo "<!-- $sSQL -->\n\n";
        $q->query($DB, $sSQL);
        $sSQL="CREATE INDEX ".$table_name."_datestamp on $table_name(datestamp)";
//        echo "<!-- $sSQL -->\n\n";
        $q->query($DB, $sSQL);
        $sSQL="CREATE INDEX ".$table_name."_subject on $table_name(subject)";
//        echo "<!-- $sSQL -->\n\n";
        $q->query($DB, $sSQL);
        $sSQL="CREATE INDEX ".$table_name."_thread on $table_name(thread)";
//        echo "<!-- $sSQL -->\n\n";
        $q->query($DB, $sSQL);
        $sSQL="CREATE INDEX ".$table_name."_parent on $table_name(parent)";
//        echo "<!-- $sSQL -->\n\n";
        $q->query($DB, $sSQL);
        $sSQL="CREATE INDEX ".$table_name."_approved on $table_name(approved)";
//        echo "<!-- $sSQL -->\n\n";
        $q->query($DB, $sSQL);
      }
      else{
        return $q->error();
      }
    }
    elseif($table=="bodies"){
      $sSQL="CREATE TABLE ".$table_name."_bodies (id INT4 DEFAULT '0' NOT NULL, body text DEFAULT '' NOT NULL, thread INT4 DEFAULT '0' NOT NULL)";
      $q->query($DB, $sSQL);
      $sSQL="CREATE INDEX ".$table_name."_bodies_thread on ".$table_name."_bodies(thread)";
      $q->query($DB, $sSQL);
      return $q->error();
    }        
  }
}
?>
