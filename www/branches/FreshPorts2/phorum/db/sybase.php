<?PHP
// sybase database abstraction file for phorum (based on mysql file)

if ( !defined( "_DB_LAYER" ) ){
  define("_DB_LAYER", 1 );

class db {

  var $connect_id;
  var $type;

  function db($database_type="sybase") {
    $this->type=$database_type;
  }

  function open($database, $host, $user, $password) {
    $this->connect_id=sybase_pconnect($host, $user, $password);
    if ($this->connect_id) {
      $result=@sybase_select_db($database);
      if (!$result) {
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
    $sSQL="UPDATE $esequence SET nextval=$newval";
    $query=new query($this, $sSQL);
    return $query->error();
  }

  function nextid($sequence) {
    $esequence=ereg_replace("'","''",$sequence)."_seq";
    $query=new query($this, "BEGIN TRANSACTION");
    $query->query($this, "UPDATE $esequence SET nextval=nextval+1");
    if ($query->result) {
    	$query->query($this, "SELECT * from $esequence");
	$result=$query->field("nextval", 1);
	$query->query($this, "COMMIT TRANSACTION");
    } else {
      // sybase can't create tables within a transaction....
      $query->query($this, "COMMIT TRANSACTION");
      $query->query($this, "CREATE TABLE $esequence (nextval int)");
      $query->query($this, "INSERT INTO $esequence (nextval) VALUES(1)");
      $result=1;
    }
    return $result;
  }

  function close() {
  // frees any query results left. (does not close persistant db conn.)
    $query=new query($this, "commit");
    if ($this->query_id && is_array($this->query_id)) {
       while (list($key,$val)=each($this->query_id)) {
         @sybase_free_result($val);
      }
    }
    return 0;
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
  var $querystring;
  var $was_error;

  function query(&$db, $query="") {
    $this->was_error=0;
    $this->querystring=$query;
    if(0) { // SQL trace
      $fh=fopen("/usr/local/apache/htdocs/phorum/db/db.txt","a");
      fwrite($fh,$query."\n");
      fclose($fh);
    }
    if($this->result=@sybase_query($query, $db->connect_id)) {
      $db->addquery($this->result);
    } else $this->was_error=1;
  }

  function getrow() {
    $this->was_error=0;
    if(!$this->row=@sybase_fetch_array($this->result)) $this->was_error=1;
    return $this->row;
  }

  function numrows() {
    $result=@sybase_num_rows($this->result);
    return $result;
  }

  function error() {
    if($this->was_error)
      $result="Oops! something went wrong somewhere whilst running this query: $this->querystring";
    else
      $result=0;
    return $result;
  }

  function field($field, $row="-1") {
  // get the value of the field with name $field
  // in the current row or in row $row if supplied

    if($row!=-1){
      $result=@sybase_result($this->result, $row-1, $field);
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
    $result=@sybase_data_seek($this->result,0);
    if($result){
      $result=$this->getrow();
      return $this->row;
    }
    else{
      return 0;
    }
  }

  function seek($seek_row) {
    @sybase_data_seek($this->result,$seek_row);
  }

  function free() {
    return @sybase_free_result($this->result);
  }

}; // End class





function create_table(&$DB, $table, $table_name){
    GLOBAL $q;
    if($table=="main"){

    // numeric fields reduced to int- php always displays numerics as n.0
    // which stops article threading working  properly....
    $sSQL="CREATE TABLE $table_name (
	id int DEFAULT 0 NOT NULL PRIMARY KEY,
	datestamp varchar(19) DEFAULT '0000-00-00 00:00:00' NOT NULL,
	thread int DEFAULT 0 NOT NULL,
	parent int DEFAULT 0 NOT NULL,
	author varchar(37) DEFAULT '' NOT NULL,
	subject varchar(255) DEFAULT '' NOT NULL,
	email varchar(200) DEFAULT '' NOT NULL,
	attachment varchar(64) DEFAULT '' NOT NULL,
	host varchar(50) DEFAULT '' NOT NULL,
	email_reply char(1) DEFAULT 'N' NOT NULL,
	approved char(1) DEFAULT 'N' NOT NULL,
	msgid char(100) DEFAULT '' NOT NULL)";

    $q->query($DB, $sSQL);
    if(!$q->error()){
      $sSQL="CREATE INDEX ".$table_name."_author on $table_name(author)";
      $q->query($DB, $sSQL);
      $sSQL="CREATE INDEX ".$table_name."_datestamp on $table_name(datestamp)";
      $q->query($DB, $sSQL);
      $sSQL="CREATE INDEX ".$table_name."_subject on $table_name(subject)";
      $q->query($DB, $sSQL);
      $sSQL="CREATE INDEX ".$table_name."_thread on $table_name(thread)";
      $q->query($DB, $sSQL);
      $sSQL="CREATE INDEX ".$table_name."_parent on $table_name(parent)";
      $q->query($DB, $sSQL);
      $sSQL="CREATE INDEX ".$table_name."_approved on $table_name(approved)";
      $q->query($DB, $sSQL);
      $sSQL="CREATE INDEX ".$table_name."_msgid on $table_name(msgid)";
      $q->query($DB, $sSQL);

      $sSQL="CREATE TABLE ".$table_name."_bodies (
        id int DEFAULT 0 NOT NULL PRIMARY KEY,
        body text DEFAULT '' NOT NULL,
        thread int DEFAULT '0' NOT NULL)";

        $q->query($DB, $sSQL);
        if(!$q->error()){
          $sSQL="CREATE INDEX ".$table_name."_bodies_thread on ".$table_name."_bodies(thread)";
          $q->query($DB, $sSQL);
          return "";
	} else {
          $errormsg = $q->error();
          $sSQL="DROP TABLE ".$table_name;
          $q->query($DB, $sSQL);
          return $errormsg;
        }
      } else {
        return $q->error();
      }
    }
    elseif($table=="forums"){
      $sSQL="CREATE TABLE ".$table_name." (
        id int DEFAULT 0 NOT NULL PRIMARY KEY,
        name varchar(50) DEFAULT '' NOT NULL,
        active smallint DEFAULT 0 NOT NULL,
        description varchar(255) DEFAULT '' NOT NULL,
        config_suffix varchar(50) DEFAULT '' NOT NULL,
        folder tinyint DEFAULT 0 NOT NULL,
        parent int DEFAULT 0 NOT NULL,
        display int DEFAULT 0 NOT NULL,
        table_name varchar(50) DEFAULT '' NOT NULL,
        moderation char(1) DEFAULT 'n' NOT NULL,
        mod_email varchar(50) DEFAULT '' NOT NULL,
        mod_pass varchar(50) DEFAULT '' NOT NULL,
        email_list varchar(50) DEFAULT '' NOT NULL,
        email_return varchar(50) DEFAULT '' NOT NULL,
        email_tag varchar(50) DEFAULT '' NOT NULL,
        check_dup smallint DEFAULT 0 NOT NULL,
        multi_level smallint DEFAULT 0 NOT NULL,
        collapse smallint DEFAULT 0 NOT NULL,
        flat smallint DEFAULT 0 NOT NULL,
        staff_host varchar(50) DEFAULT '' NOT NULL,
        lang varchar(50) DEFAULT '' NOT NULL,
        html varchar(40) DEFAULT 'N' NOT NULL,
        table_width varchar(4) DEFAULT '' NOT NULL,
        table_header_color varchar(7) DEFAULT '' NOT NULL,
	table_header_font_color varchar(7) DEFAULT '' NOT NULL,
        table_body_color_1 varchar(7) DEFAULT '' NOT NULL,
        table_body_color_2 char(7) DEFAULT '' NOT NULL,
        table_body_font_color_1 varchar(7) DEFAULT '' NOT NULL,
        table_body_font_color_2 varchar(7) DEFAULT '' NOT NULL,
        nav_color varchar(7) DEFAULT '' NOT NULL,
        nav_font_color varchar(7) DEFAULT '' NOT NULL,
	allow_uploads char(1) DEFAULT 'N' NOT NULL
	)";

      $q->query($DB, $sSQL);
      if(!$q->error()) {
        $sSQL="CREATE INDEX ".$table_name."_parent on ".$table_name."(parent)";
        $q->query($DB, $sSQL);
        $sSQL="CREATE INDEX ".$table_name."_name on ".$table_name."(name)";
        $q->query($DB, $sSQL);
        $sSQL="CREATE INDEX ".$table_name."_active on ".$table_name."(active)";
        $q->query($DB, $sSQL);
      } else {
        return $q->error();
    }
  }
}

} // end if_defined

?>