<?PHP

if ( !defined( "_DB_LAYER" ) ){
  define("_DB_LAYER", 1 );

class db {

  var $connect_id;
  var $type;

  function db($database_type="mysql") { 
    $this->type=$database_type;
  }

  function open($database, $host, $user, $password) {
    $this->connect_id=mysql_pconnect($host, $user, $password); 
    if ($this->connect_id) {
      $result=@mysql_select_db($database);
      if (!$result) {
        @mysql_close($this->connect_id);
        $this->connect_id=$result;
      }
      return $this->connect_id;
    }
    else{
      return 0;
    }
  }

  function drop_sequence($sequence){
    $esequence=$sequence."_seq";
    $sSQL="DROP TABLE $esequence";
    $query=new query($this, $sSQL);
    return $query->error();  
  }

  function reset_sequence($sequence, $newval){
    $this->nextid($sequence);
    $esequence=$sequence."_seq";
    $sSQL="Replace into $esequence values ('', $newval)";
    $query=new query($this, $sSQL);
    return $query->error();
  }

  function nextid($sequence) {
  // Function returns the next available id for $sequence, if it's not
  // already defined, the first id will start at 1. 
  // This function will create a table for each sequence called
  // '{sequence_name}_seq' in the current database.
    $esequence=ereg_replace("'","''",$sequence)."_seq";
    $query=new query($this, "Select * from $esequence limit 1");
    $query->query($this, "REPLACE INTO $esequence values ('', nextval+1)");
    if ($query->result) {
      $result=@mysql_insert_id($this->connect_id);
    } else {
      $query->query($this, "CREATE TABLE $esequence ( seq char(1) DEFAULT '' NOT NULL, nextval bigint(20) unsigned DEFAULT '0' NOT NULL auto_increment, PRIMARY KEY (seq), KEY (nextval) )");
      $query->query($this, "REPLACE INTO $esequence values ('', nextval+1)");  
      $result=@mysql_insert_id($this->connect_id);
    }
    return $result;
  }           
     
  function close() {
  // Closes the database connection and frees any query results left.

    if ($this->query_id && is_array($this->query_id)) {
      while (list($key,$val)=each($this->query_id)) {
        @mysql_free_result($val);
      }
    }
    $result=@mysql_close($this->connect_id);
    return $result;
  }

  function addquery($query_id) {
  // Function used by the constructor of query. Notifies the
  // this object of the existance of a query_result for later cleanup
  // internal function, don't use it yourself.

    $this->query_id[]=$query_id;
  }
  
};

/************************************** QUERY ***************************/

class query {

  var $result;
  var $row;

  function query(&$db, $query="") {
  // Constructor of the query object.
  // executes the query, notifies the db object of the query result to clean
  // up later
    if($query!=""){
//      if ($this->result) {
//        $this->free(); // query not called as constructor therefore there may
                       // be something to clean up.
//      }
      $this->result=@mysql_query($query, $db->connect_id);
      $db->addquery($this->result);
    }
  }
  
  function getrow() {
  // Gets the next row for processing with $this->field function later.

    $this->row=@mysql_fetch_array($this->result);
    return $this->row;
  }
   
  function numrows() {
  // Gets the number of rows returned in the query
  
    $result=@mysql_num_rows($this->result);
    return $result;
  }

  function error() {
  // Gets the last error message reported for this query
  
    $result=@mysql_error();
    return $result;
  }

  function field($field, $row="-1") {
  // get the value of the field with name $field
  // in the current row or in row $row if supplied

    if($row!=-1){
      $result=@mysql_result($this->result, $row, $field);
    }
    else{
      $result=$this->row[$field];
    }

    if(isset($result)){
      return $result;
    }
    else{
      return '0';
    }
  }

  function firstrow() {
  // return the current row pointer to the first row 
  // (CAUTION: other versions may execute the query again!! (e.g. for oracle))
 
    $result=@mysql_data_seek($this->result,0);
    if($result){
      $result=$this->getrow();
      return $this->row;
    }
    else{
      return 0;
    }
  }

  function free() {
  // free the mysql result tables

    return @mysql_free_result($this->result);
  }

}; // End class

// Custom Create Table Section

  function create_table(&$DB, $table, $table_name){
    GLOBAL $q;
    if($table=="main"){
      $sSQL="CREATE TABLE $table_name (id bigint(20) unsigned DEFAULT '0' NOT NULL, datestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL, thread int(11) DEFAULT '0' NOT NULL, parent int(11) DEFAULT '0' NOT NULL, author char(37) DEFAULT '' NOT NULL, subject char(50) DEFAULT '' NOT NULL, email char(50) DEFAULT '' NOT NULL, host char(50) DEFAULT '' NOT NULL, email_reply char(1) NOT NULL DEFAULT 'N', approved char(1) NOT NULL DEFAULT 'N', PRIMARY KEY (id), KEY author (author), KEY datestamp (datestamp), KEY subject (subject), KEY thread (thread), KEY parent (parent), KEY approved (approved))";
//      echo "\n<!--$sSQL-->\n";
      $q->query($DB, $sSQL);
      return $q->error();
    }
    elseif($table=="bodies"){
      $sSQL="CREATE TABLE ".$table_name."_bodies (id bigint(20) unsigned DEFAULT '0' NOT NULL, body text DEFAULT '' NOT NULL, thread int(11) DEFAULT '0' NOT NULL, PRIMARY KEY (id), KEY thread (thread))";
//      echo "\n<!--$sSQL-->\n";
      $q->query($DB, $sSQL);
      return $q->error();
    }
  }
}
?>