<?php

if ( defined( "_DB_LAYER" ) ) return;

define("_DB_LAYER", 1 );

if(!defined("PHORUM_ADMIN") && !function_exists("pg_connect"))
  echo "<b>Error: You have configured Phorum to use PostgreSQL.  PostgreSQL support is not available to PHP on this server.</b>";

class db {

  var $connect_id;
  var $type;

  function db($database_type="postgresql") {
    global $PGTYPE;
    if(!empty($PGTYPE)) $database_type=$PGTYPE;
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
  $this->connect_id=@pg_Connect($connect_string);
  if($this->connect_id){
    @pg_exec($this->connect_id, "SET DateStyle TO 'ISO'");
  }
  return $this->connect_id;
  }

  function drop_sequence($sequence){
    $esequence=$sequence."_seq";
    $SQL="DROP SEQUENCE $esequence";
    $query=new query($this, $SQL);
    return $query->error();
  }

  function reset_sequence($sequence, $newval){
    $this->nextid($sequence);
    $esequence=$sequence."_seq";
    $SQL="setval('$esequence', $newval)";
    $query=new query($this, $SQL);
    return $query->error();
  }

  function nextid($sequence) {
    $esequence=$sequence."_seq";
    $query=new query($this, "select nextval('$esequence') as nextid");
    if ($query->result){
      $row=$query->getrow();
      $nextid=$row["nextid"];
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
  var $query;

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
      $this->query=$query;
    }
  }

  function getrow() {
    $row=0;
    if($row=@pg_fetch_array($this->result, $this->curr_row, PGSQL_ASSOC)){
      $this->curr_row++;
    }
    $this->row=$row;
    return $row;
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

  function seek($row) {
    $this->curr_row=$row;
  }

  function free() {
  // free the postgresql result tables

    return @pg_FreeResult($this->result);
  }

}; // End class

// Custom Create Table Section

  function create_table(&$DB, $table, $table_name){
    global $q;
    switch($table){
      case "main":
        $SQL="CREATE TABLE $table_name (
                  id INT4 DEFAULT '0' NOT NULL,
                  datestamp datetime NOT NULL,
                  thread INT4 DEFAULT '0' NOT NULL,
                  parent INT4 DEFAULT '0' NOT NULL,
                  author char(37) DEFAULT '' NOT NULL,
                  subject char(255) DEFAULT '' NOT NULL,
                  email char(200) DEFAULT '' NOT NULL,
                  attachment char(64) DEFAULT '',
                  host char(50) DEFAULT '' NOT NULL,
                  email_reply char(1) NOT NULL DEFAULT 'N',
                  approved char(1) NOT NULL DEFAULT 'N',
                  msgid char(100) NOT NULL DEFAULT '',
                  modifystamp INT4 DEFAULT '0' NOT NULL,
                  userid int4 DEFAULT '0' NOT NULL,
                  CONSTRAINT ".$table_name."pri_key PRIMARY KEY(id))";
        $q->query($DB, $SQL);
        if(!$q->error()){
          $SQL="CREATE INDEX ".$table_name."_author on $table_name(author)";
          $q->query($DB, $SQL);
          $SQL="CREATE INDEX ".$table_name."_userid on $table_name(userid)";
          $q->query($DB, $SQL);
          $SQL="CREATE INDEX ".$table_name."_datestamp on $table_name(datestamp)";
          $q->query($DB, $SQL);
          $SQL="CREATE INDEX ".$table_name."_subject on $table_name(subject)";
          $q->query($DB, $SQL);
          $SQL="CREATE INDEX ".$table_name."_thread on $table_name(thread)";
          $q->query($DB, $SQL);
          $SQL="CREATE INDEX ".$table_name."_parent on $table_name(parent)";
          $q->query($DB, $SQL);
          $SQL="CREATE INDEX ".$table_name."_approved on $table_name(approved)";
          $q->query($DB, $SQL);
          $SQL="CREATE INDEX ".$table_name."_msgid on $table_name(msgid)";
          $q->query($DB, $SQL);
          $SQL="CREATE INDEX ".$table_name."_modifystamp on $table_name(modifystamp)";
          $q->query($DB, $SQL);
          $SQL="CREATE TABLE ".$table_name."_bodies (
                  id INT4 DEFAULT '0' NOT NULL,
                  body text DEFAULT '' NOT NULL,
                  thread INT4 DEFAULT '0' NOT NULL)";
          $q->query($DB, $SQL);
          if(!$q->error()){
            $SQL="CREATE INDEX ".$table_name."_bodies_thread on ".$table_name."_bodies(thread)";
            $q->query($DB, $SQL);
            return "";
          } else {
            $errormsg = $q->error();
            $SQL="DROP TABLE ".$table_name;
            $q->query($DB, $SQL);
            return $errormsg;
          }
        } else {
          return $q->error();
        }
        break;
      case "forums":
        $SQL = "CREATE TABLE ".$table_name." (
                  id int4 DEFAULT '0' NOT NULL PRIMARY KEY,
                  name varchar(50) DEFAULT '' NOT NULL,
                  active int2 DEFAULT 0 NOT NULL,
                  description varchar(255) DEFAULT '' NOT NULL,
                  config_suffix varchar(50) DEFAULT '' NOT NULL,
                  folder char DEFAULT '0' NOT NULL,
                  parent int4 DEFAULT 0 NOT NULL,
                  display int4 DEFAULT 0 NOT NULL,
                  table_name varchar(50) DEFAULT '' NOT NULL,
                  moderation char DEFAULT 'n' NOT NULL,
                  email_list varchar(50) DEFAULT '' NOT NULL,
                  email_return varchar(50) DEFAULT '' NOT NULL,
                  email_tag varchar(50) DEFAULT '' NOT NULL,
                  check_dup int2 DEFAULT 0 NOT NULL,
                  multi_level int2 DEFAULT 0 NOT NULL,
                  collapse int2 DEFAULT 0 NOT NULL,
                  flat int2 DEFAULT 0 NOT NULL,
                  lang varchar(50) DEFAULT '' NOT NULL,
                  html varchar(40) DEFAULT 'N' NOT NULL,
                  table_width varchar(4) DEFAULT '' NOT NULL,
                  table_header_color varchar(7) DEFAULT '' NOT NULL,
                  table_header_font_color varchar(7) DEFAULT '' NOT NULL,
                  table_body_color_1 varchar(7) DEFAULT '' NOT NULL,
                  table_body_color_2 varchar(7) DEFAULT '' NOT NULL,
                  table_body_font_color_1 varchar(7) DEFAULT '' NOT NULL,
                  table_body_font_color_2 varchar(7) DEFAULT '' NOT NULL,
                  nav_color varchar(7) DEFAULT '' NOT NULL,
                  nav_font_color varchar(7) DEFAULT '' NOT NULL,
                  allow_uploads char DEFAULT 'N' NOT NULL,
                  upload_types varchar(100) DEFAULT 'N' NOT NULL,
                  upload_size int4 DEFAULT '0' NOT NULL,
                  max_uploads int4 DEFAULT '0' NOT NULL,
                  security int4 DEFAULT '0' NOT NULL,
                  showip int2 DEFAULT 1 NOT NULL,
                  emailnotification int2 DEFAULT 1 NOT NULL,
                  body_color varchar(7) DEFAULT '' NOT NULL,
                  body_link_color varchar(7) DEFAULT '' NOT NULL,
                  body_alink_color varchar(7) DEFAULT '' NOT NULL,
                  body_vlink_color varchar(7) DEFAULT '' NOT NULL
                  )";
        $q->query($DB, $SQL);
        if(!$q->error()) {
          $SQL="CREATE INDEX ".$table_name."_name on ".$table_name."(name)";
          $q->query($DB, $SQL);
          $SQL="CREATE INDEX ".$table_name."_active on ".$table_name."(active)";
          $q->query($DB, $SQL);
          $SQL="CREATE INDEX ".$table_name."_parent on ".$table_name."(parent)";
          $q->query($DB, $SQL);
          $SQL="CREATE INDEX ".$table_name."_security on ".$table_name."(security)";
          $q->query($DB, $SQL);
        } else {
          return $q->error();
        }
        break;
      case "auth":
        $SQL="CREATE TABLE ".$table_name."_auth (
                   id SERIAL,
                   sess_id varchar(32) DEFAULT '' NOT NULL,
                   name varchar(50) DEFAULT '' NOT NULL,
                   username varchar(50) DEFAULT '' NOT NULL,
                   password varchar(50) DEFAULT '' NOT NULL,
                   email varchar(200) DEFAULT '' NOT NULL,
                   webpage varchar(200) DEFAULT '' NOT NULL,
                   image varchar(200) DEFAULT '' NOT NULL,
                   icq varchar(50) DEFAULT '' NOT NULL,
                   aol varchar(50) DEFAULT '' NOT NULL,
                   yahoo varchar(50) DEFAULT '' NOT NULL,
                   msn varchar(50) DEFAULT '' NOT NULL,
                   jabber varchar(50) DEFAULT '' NOT NULL,
                   signature varchar(255) DEFAULT '' NOT NULL,
                   CONSTRAINT ".$table_name."_pri_key PRIMARY KEY (id))";
        $q->query($DB, $SQL);
        $SQL="CREATE UNIQUE INDEX name_".$table_name."_ukey ON ".$table_name." (name)";
        $q->query($DB, $SQL);
        $SQL="CREATE UNIQUE INDEX username_".$table_name."_ukey ON ".$table_name." (username)";
        $q->query($DB, $SQL);
        $SQL="CREATE INDEX sess_id_".$table_name."_key ON ".$table_name." (sess_id)";
        $q->query($DB, $SQL);
        $SQL="CREATE INDEX password_".$table_name."_key ON ".$table_name." (password)";
        $q->query($DB, $SQL);
        break;
      case "moderators":
        // moderator xref
        $SQL="CREATE TABLE ".$table_name."_moderators (
                  user_id int4 DEFAULT '0' NOT NULL,
                  forum_id int4 DEFAULT '0' NOT NULL,
                  CONSTRAINT ".$table_name."pri_key PRIMARY KEY(user_id,forum_id))";
        $q->query($DB, $SQL);
        $SQL="CREATE INDEX forum_id_".$table_name."_key ON ".$table_name."_moderators (forum_id)";
        $q->query($DB, $SQL);
        $ret=$q->error();
        return $ret;
        break;
      case "attachments":
        $SQL="CREATE TABLE ".$table_name." (
                  id int4 DEFAULT '0' NOT NULL,
                  message_id int4 DEFAULT '0' NOT NULL,
                  filename varchar(50) DEFAULT '' NOT NULL,
                  CONSTRAINT ".$table_name."pri_key PRIMARY KEY(id, message_id))";
        $q->query($DB, $SQL);
        $SQL="CREATE INDEX message_id_".$table_name."_key ON ".$table_name."_moderators (message_id)";
        $q->query($DB, $SQL);
        break;
    }
  }

?>
