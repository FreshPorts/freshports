<?php

if ( defined( "_DB_LAYER" ) ) return;

define("_DB_LAYER", 1 );

if(!defined("PHORUM_ADMIN") && !function_exists("mysql_connect")){
  echo "<b>Error: You have configured Phorum to use MySQL.  MySQL support is not available to PHP on this server.</b>";
  exit();
}

class db {

  var $connect_id;
  var $type;
  var $database;

  function db($database_type="mysql") {
    $this->type=$database_type;
  }

  function open($database, $host, $user, $password) {
    if(empty($user)){
      $this->connect_id=@mysql_connect();
    }
    else{
      $this->connect_id=@mysql_connect($host, $user, $password);
    }

    if ($this->connect_id) {
      $this->database=$database;
      if(@mysql_select_db($this->database, $this->connect_id)){
        return $this->connect_id;
      }
      else{
        return 0;
      }
    }
    else{
      return 0;
    }
  }

  function drop_sequence($sequence){
    // This function no longer used for MySQL
    return 0;
  }

  function reset_sequence($sequence, $newval){
    // This function no longer used for MySQL
    return 0;
  }

  function lastid(){
    // This function is only used for MySQL
    return mysql_insert_id($this->connect_id);
  }

  function nextid($sequence) {
    // This function no longer used for MySQL
    return 0;
  }

  function close() {
  // Closes the database connection and frees any query results left.

    if ($this->query_id && is_array($this->query_id)) {
      while (list($key,$val)=each($this->query_id)) {
        mysql_free_result($val);
      }
    }
    $result=@mysql_close($this->connect_id);
    return $result;
  }

};

/************************************** QUERY ***************************/

class query {

  var $result;
  var $row;

  function query(&$db, $query="") {
  // Constructor of the query object.
  // executes the query

    if(!empty($query) && !empty($db->connect_id)){
      // mysql_select_db($db->database, $db->connect_id);  // If you are having trouble with other apps uncomment this line.
      $this->result=@mysql_query($query, $db->connect_id);
      return $this->result;
    }
  }

  function getrow() {
    $row=0;
    $row=@mysql_fetch_array($this->result, MYSQL_ASSOC);
    $this->row=$row;
    return $row;
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

  function seek($row){
    mysql_data_seek($this->result, $row);
  }

  function free() {
  // free the mysql result tables

    return mysql_free_result($this->result);
  }

}; // End class

// Custom Create Table Section

  function create_table(&$DB, $table, $table_name){
    global $q;
    switch($table){
      case "main":
        $sSQL="CREATE TABLE $table_name (
                id int unsigned DEFAULT '0' NOT NULL,
                datestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                thread int unsigned DEFAULT '0' NOT NULL,
                parent int unsigned DEFAULT '0' NOT NULL,
                author char(37) DEFAULT '' NOT NULL,
                subject char(255) DEFAULT '' NOT NULL,
                email char(200) DEFAULT '' NOT NULL,
                host char(50) DEFAULT '' NOT NULL,
                email_reply char(1) NOT NULL DEFAULT 'N',
                approved char(1) NOT NULL DEFAULT 'N',
                msgid char(100) DEFAULT '' NOT NULL,
                modifystamp int unsigned DEFAULT '0' NOT NULL,
                userid int unsigned DEFAULT 0 NOT NULL,
                PRIMARY KEY (id),
                KEY author (author),
                KEY userid (userid),
                KEY datestamp (datestamp),
                KEY subject (subject),
                KEY thread (thread),
                KEY parent (parent),
                KEY approved (approved),
                KEY msgid (msgid),
                KEY modifystamp (modifystamp)
              )";
        $q->query($DB, $sSQL);
        if(!$q->error()){
          $sSQL="CREATE TABLE ".$table_name."_bodies (id int unsigned DEFAULT '0' NOT NULL AUTO_INCREMENT, body text DEFAULT '' NOT NULL, thread int unsigned DEFAULT '0' NOT NULL, PRIMARY KEY (id), KEY thread (thread))";
          $q->query($DB, $sSQL);
          if($q->error()){
            $errormsg = $q->error();
            $sSQL="DROP TABLE ".$table_name;
            $q->query($DB, $sSQL);
            return $errormsg;
            } else {
            return "";
          }
        } else {
          return $q->error();
        }
        break;
      case "forums":
        $sSQL="CREATE TABLE ".$table_name." (
          id int unsigned DEFAULT 0 NOT NULL AUTO_INCREMENT,
          name char(50) DEFAULT '' NOT NULL,
          active smallint DEFAULT 0 NOT NULL,
          description char(255) DEFAULT '' NOT NULL,
          config_suffix char(50) DEFAULT '' NOT NULL,
          folder char(1) DEFAULT '0' NOT NULL,
          parent int unsigned DEFAULT 0 NOT NULL,
          display int unsigned DEFAULT 0 NOT NULL,
          table_name char(50) DEFAULT '' NOT NULL,
          moderation char(1) DEFAULT 'n' NOT NULL,
          email_list char(50) DEFAULT '' NOT NULL,
          email_return char(50) DEFAULT '' NOT NULL,
          email_tag char(50) DEFAULT '' NOT NULL,
          check_dup smallint unsigned DEFAULT 0 NOT NULL,
          multi_level smallint unsigned DEFAULT 0 NOT NULL,
          collapse smallint unsigned DEFAULT 0 NOT NULL,
          flat smallint unsigned DEFAULT 0 NOT NULL,
          lang char(50) DEFAULT '' NOT NULL,
          html char(40) DEFAULT 'N' NOT NULL,
          table_width char(4) DEFAULT '' NOT NULL,
          table_header_color char(7) DEFAULT '' NOT NULL,
          table_header_font_color char(7) DEFAULT '' NOT NULL,
          table_body_color_1 char(7) DEFAULT '' NOT NULL,
          table_body_color_2 char(7) DEFAULT '' NOT NULL,
          table_body_font_color_1 char(7) DEFAULT '' NOT NULL,
          table_body_font_color_2 char(7) DEFAULT '' NOT NULL,
          nav_color char(7) DEFAULT '' NOT NULL,
          nav_font_color char(7) DEFAULT '' NOT NULL,
          allow_uploads char(1) DEFAULT 'N' NOT NULL,
          upload_types char(100) DEFAULT '' NOT NULL,
          upload_size int unsigned DEFAULT '0' NOT NULL,
          max_uploads int unsigned DEFAULT '0' NOT NULL,
          security int unsigned DEFAULT '0' NOT NULL,
          showip smallint unsigned DEFAULT 1 NOT NULL,
          emailnotification smallint unsigned DEFAULT 1 NOT NULL,
          body_color char(7) DEFAULT '' NOT NULL,
          body_link_color char(7) DEFAULT '' NOT NULL,
          body_alink_color char(7) DEFAULT '' NOT NULL,
          body_vlink_color char(7) DEFAULT '' NOT NULL,
          PRIMARY KEY (id),
          KEY (name),
          KEY (active),
          KEY (parent),
          key (security) )";
        $q->query($DB, $sSQL);
        $ret=$q->error();
        return $ret;
        break;

      case "auth":
        // auth table
        $sSQL="CREATE TABLE ".$table_name."_auth (
          id int unsigned DEFAULT 0 NOT NULL AUTO_INCREMENT,
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
          PRIMARY KEY (id),
          KEY (name),
          KEY (username),
          key (sess_id),
          key (password)
        )";
        $q->query($DB, $sSQL);
        $ret=$q->error();
        return $ret;
        break;

      case "moderators":
        // moderator xref
        $sSQL="CREATE TABLE ".$table_name."_moderators (
          user_id int unsigned DEFAULT 0 NOT NULL,
          forum_id int unsigned DEFAULT 0 NOT NULL,
          PRIMARY KEY (user_id,forum_id),
          key (forum_id)
        )";
        $q->query($DB, $sSQL);
        $ret=$q->error();
        return $ret;
        break;

      case "attachments":
        $sSQL="CREATE TABLE IF NOT EXISTS ".$table_name." (
          id int unsigned DEFAULT 0 NOT NULL AUTO_INCREMENT,
          message_id int unsigned DEFAULT '0' NOT NULL,
          filename char(50) DEFAULT '' NOT NULL,
          PRIMARY KEY (id, message_id),
          KEY lookup (message_id)
        )";
        $q->query($DB, $sSQL);
        return $q->error();
        break;
    }
  }

?>
