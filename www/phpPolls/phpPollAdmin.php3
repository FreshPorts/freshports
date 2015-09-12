<?
//////////////////////////////////////////////////////////////////////
//
// phpPolls - A voting booth for PHP3 (administration module)
//
// This file is "phpPollAdmin.php3" and handles all administrative
// tasks. This module is meant to be run stand-alone and not from
// another script.
//
// See phpPollConfig.php3 for configuration options.
//
// Copyright (c) 1999 Till Gerken (tig@skv.org)
//
// This software is released under the GNU Public License.
// Please see the accompanying file gpl.txt for licensing details!
//
//////////////////////////////////////////////////////////////////////
?>

<html>

<head>
<title>
phpPolls Administration Module
</title>

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

<body bgcolor="#ffffff" text="#000000" link="#0000FF" vlink="#000080">

<?

require "phpPollConfig.php3";
require "phpPollUI.php3";

echo "<h1>phpPolls $poll_scriptVersion Administration</h1>";

echo "<p><a href=\"$poll_baseURL/phpPollAdmin.php3\">Main Menu</a></p>";

//////////////////////////////////////////////////////////////////////
//
// Function poll_mainMenu()
//
//////////////////////////////////////////////////////////////////////
//
// Displays a simple menu with the (for now) two possible choices:
//	- adding a new poll
//	- removing an existing poll
//
//////////////////////////////////////////////////////////////////////
//
// Calls to:
//	Issues indirect callbacks using the $poll_action variable to
//	poll_createPoll() and poll_removePoll()
//
//////////////////////////////////////////////////////////////////////
//
// Global references:
//	$poll_baseURL
//		(from phpPollConfig.php3)
//
//////////////////////////////////////////////////////////////////////
//
// Author: tig
// Last change: 99/05/10
//
//////////////////////////////////////////////////////////////////////

function poll_mainMenu()
{
	global $poll_baseURL;

	// display main menu
	echo "<p><a href=\"$poll_baseURL/phpPollAdmin.php3?poll_action=create\">Create new poll</a><br>";
	echo "<a href=\"$poll_baseURL/phpPollAdmin.php3?poll_action=remove\">Remove existing poll</a></p>";
	echo "<p><a href=\"$poll_baseURL/phpPollAdmin.php3?poll_action=view\">View poll results</a></p>";
}

//////////////////////////////////////////////////////////////////////
//
// Function poll_createPoll()
//
//////////////////////////////////////////////////////////////////////
//
// Generates a HTML formular allowing an administrator to enter
// necessary data to create a new poll.
//
//////////////////////////////////////////////////////////////////////
//
// Calls to:
//	Issues indirect callback using the $poll_action variable to
//	poll_createPosted()
//
//////////////////////////////////////////////////////////////////////
//
// Global references:
//	$poll_maxOptions, $poll_baseURL
//		(from phpPollConfig.php3)
//
//////////////////////////////////////////////////////////////////////
//
// Author: tig
// Last change: 99/05/10
//
//////////////////////////////////////////////////////////////////////

function poll_createPoll()
{
	global $poll_maxOptions;
	global $poll_baseURL;

	// create a new poll
	echo "<h2>Create new poll</h2>";

	// setup the poll description entry thing
	echo "<form action=\"$poll_baseURL/phpPollAdmin.php3\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"poll_action\" value=\"createPosted\">";

	echo "<p>Polltitle: <input type=text name=\"poll_pollTitle\" size=50 maxlength=100></p>";

	echo "<p>Please enter each available option into a single field</p>";

	// create table for poll options
	echo "<table>";

	for($i = 1; $i <= $poll_maxOptions; $i++)
	{
		echo "<tr>";
		echo "<td>Option $i:</td><td><input type=text name=\"poll_optionText[$i]\" size=50 maxlength=50></td>";
		echo "</tr>";
	}

	// close table
	echo "</tr>";
	echo "</table>";
	// show submit button
	echo "<input type=\"submit\" value=\"Create\">";

	// close form
	echo "</form>";

}

//////////////////////////////////////////////////////////////////////
//
// Function poll_createPosted()
//
//////////////////////////////////////////////////////////////////////
//
// Creates a new poll by writing all submitted data to the MySQL
// database.
//
//////////////////////////////////////////////////////////////////////
//
// Calls to:
//	none
//
//////////////////////////////////////////////////////////////////////
//
// Global references:
//	$poll_dbName, $poll_descTableName, $poll_dataTableName,
//	$poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd,
//	$poll_usePersistentConnects, $poll_maxOptions,
//		(from phpPollConfig.php3)
//	$poll_pollTitle, $poll_optionText
//		(from previously submitted form)
//
//////////////////////////////////////////////////////////////////////
//
// Author: tig
// Last change: 99/07/16
//
//////////////////////////////////////////////////////////////////////

function poll_createPosted()
{
	global $poll_dbName, $poll_descTableName, $poll_dataTableName;
	global $poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd;
	global $poll_usePersistentConnects, $poll_maxOptions;
	global $poll_pollTitle, $poll_optionText;

	// show message
	echo "Your poll has been submitted successfully, updating tables...<br>";

	// connect to database
	if($poll_usePersistentConnects == 0)
		$poll_mySQL_ID = mysql_connect($poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd);
	else
		$poll_mySQL_ID = mysql_pconnect($poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd);

	$poll_timeStamp = time();

	// add slashes to title so quotes can appear
	$poll_pollTitle = addslashes($poll_pollTitle);

	// insert values into polldescription
	if(!mysql_db_query($poll_dbName, "INSERT INTO $poll_descTableName (pollID, pollTitle, timeStamp) VALUES (NULL, '$poll_pollTitle', '$poll_timeStamp')", $poll_mySQL_ID))
	{
		echo mysql_errno(). ": ".mysql_error(). "<br>";
		return;
	}

	// get this poll's ID
	$poll_result = mysql_db_query($poll_dbName, "SELECT pollID FROM $poll_descTableName WHERE pollTitle='$poll_pollTitle'", $poll_mySQL_ID);
	if(!$poll_result)
	{
		echo mysql_errno(). ": ".mysql_error(). "<br>";
		return;
	}

	// fetch ID field
	$poll_object = mysql_fetch_object($poll_result);
	$poll_id = $poll_object->pollID;

	echo "Created a new poll with ID $poll_id, now creating data entries...<br>";

	// create option records in data table
	for($i = 1; $i <= $poll_maxOptions; $i++)
	{
		if($poll_optionText[$i] != "")
		{
			// add slashes to the option texts as well
			$poll_optionText[$i] = addslashes($poll_optionText[$i]);
			
			if(!mysql_db_query($poll_dbName, "INSERT INTO $poll_dataTableName (pollID, optionText, optionCount, voteID) VALUES ($poll_id, '$poll_optionText[$i]', 0, $i)", $poll_mySQL_ID))
			{
				echo mysql_errno(). ": ".mysql_error(). "<br>";
				return;
			}
		}
	}

	// close link to database
	if($poll_usePersistentConnects == 0)
		mysql_close($poll_mySQL_ID);

	echo "<p>Snippet for voting:<br>";
	echo stripslashes($poll_pollTitle)."<br>";
	echo "&lt;? require('phpPollConfig.php3');<br>";
	echo "require('phpPollUI.php3');<br>";
	echo "poll_generateUI($poll_id, \"http://my.site.net/forwarder.html\");<br>";
	echo "?&gt;</p>";

	echo "<p>Snippet for viewing:<br>";
	echo "&lt;? require('phpPollConfig.php3');<br>";
	echo "require('phpPollUI.php3');<br>";
	echo "poll_viewResults($poll_id);<br>";
	echo "?&gt;</p>";

}

//////////////////////////////////////////////////////////////////////
//
// Function poll_removePoll()
//
//////////////////////////////////////////////////////////////////////
//
// Generates a HTML formular listing all polls. Choosing one will
// result in removal of that poll.
//
//////////////////////////////////////////////////////////////////////
//
// Calls to:
//	Issues indirect callback using the $poll_action variable to
//	poll_removePosted()
//
//////////////////////////////////////////////////////////////////////
//
// Global references:
//	$poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd
//	$poll_dbName, $poll_descTableName, $poll_baseURL
//	$poll_usePersistentConnects
//		(from phpPollConfig.php3)
//
//////////////////////////////////////////////////////////////////////
//
// Author: tig
// Last change: 99/08/25
//
//////////////////////////////////////////////////////////////////////

function poll_removePoll()
{
	global $poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd;
	global $poll_usePersistentConnects, $poll_dbName, $poll_descTableName;
	global $poll_baseURL;

	// remove an existing poll
	echo "<h2>Remove an existing poll</h2>";

	echo "<h3>WARNING: The chosen poll will be removed IMMEDIATELY from the database!<br>This includes all IP locks and logs!</h3>";

	echo "<p>Please choose a poll from the list below.</p>";

	// connect to database
	if($poll_usePersistentConnects == 0)
		$poll_mySQL_ID = mysql_connect($poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd);
	else
		$poll_mySQL_ID = mysql_pconnect($poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd);

	// start paragraph listing all polls
	echo "<p>";

	// the poll will be chosen using a radiobutton form
	echo "<form action=\"$poll_baseURL/phpPollAdmin.php3\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"poll_action\" value=\"removePosted\">";
	echo "<table>";

	// select all descriptions
	$poll_result = mysql_db_query($poll_dbName, "SELECT * FROM $poll_descTableName ORDER BY timeStamp"); 
	if(!$poll_result)
	{
		echo mysql_errno(). ": ".mysql_error(). "<br>";
		return;
	}

	$polls_available = 0;

	// cycle through the descriptions until everyone has been fetched
	while($poll_object = mysql_fetch_object($poll_result))
	{
		$polls_available = 1;
		echo "<tr><td><input type=\"radio\" name=\"poll_id\" value=\"".$poll_object->pollID."\">".$poll_object->pollTitle."</td></tr>";
	}

	// close table and form
	echo "</table>";
	if($polls_available)
		echo "<input type=\"submit\" value=\"Remove\">";
	else
		echo "There are no polls in the database.";
	echo "</form>";

	// close link to database
	if($poll_usePersistentConnects == 0)
		mysql_close($poll_mySQL_ID);

}

//////////////////////////////////////////////////////////////////////
//
// Function poll_removePosted()
//
//////////////////////////////////////////////////////////////////////
//
// Uses the data from the previously submitted form to actually remove
// a poll from the database.
//
//////////////////////////////////////////////////////////////////////
//
// Calls to:
//	none
//
//////////////////////////////////////////////////////////////////////
//
// Global references:
//	$poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd
//	$poll_dbName, $poll_descTableName, $poll_dataTableName
//	$poll_IPTableName, $poll_logTableName
//	$poll_usePersistentConnects
//		(from phpPollConfig.php3)
//	$poll_id
//		(submitted)
//
//////////////////////////////////////////////////////////////////////
//
// Author: tig
// Last change: 99/08/25
//
//////////////////////////////////////////////////////////////////////

function poll_removePosted()
{
	global $poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd;
	global $poll_dbName, $poll_descTableName, $poll_dataTableName;
	global $poll_IPTableName, $poll_logTableName;
	global $poll_usePersistentConnects;
	global $poll_id;

	echo "Removing poll...<br>";

	// connect to database
	if($poll_usePersistentConnects == 0)
		$poll_mySQL_ID = mysql_connect($poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd);
	else
		$poll_mySQL_ID = mysql_pconnect($poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd);

	$poll_result = mysql_db_query($poll_dbName, "DELETE FROM $poll_descTableName WHERE pollID=$poll_id");
	if(!$poll_result)
	{
		echo mysql_errno(). ": ".mysql_error(). "<br>";
		return;
	}

	$poll_result = mysql_db_query($poll_dbName, "DELETE FROM $poll_dataTableName WHERE pollID=$poll_id");
	if(!$poll_result)
	{
		echo mysql_errno(). ": ".mysql_error(). "<br>";
		return;
	}

	$poll_result = mysql_db_query($poll_dbName, "DELETE FROM $poll_IPTableName WHERE pollID=$poll_id");
	if(!$poll_result)
	{
		echo mysql_errno(). ": ".mysql_error(). "<br>";
		return;
	}

	$poll_result = mysql_db_query($poll_dbName, "DELETE FROM $poll_logTableName WHERE pollID=$poll_id");
	if(!$poll_result)
	{
		echo mysql_errno(). ": ".mysql_error(). "<br>";
		return;
	}

	// close link to database
	if($poll_usePersistentConnects == 0)
		mysql_close($poll_mySQL_ID);

	echo "Done.";

}

//////////////////////////////////////////////////////////////////////
//
// Function poll_viewPoll()
//
//////////////////////////////////////////////////////////////////////
//
// Lists all available polls allowing the user to choose one for
// viewing its results.
//
//////////////////////////////////////////////////////////////////////
//
// Calls to:
//	Issues indirect callback using the $poll_action variable to
//	poll_viewPosted()
//
//////////////////////////////////////////////////////////////////////
//
// Global references:
//	$poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd
//	$poll_dbName, $poll_descTableName
//	$poll_usePersistentConnects
//		(from phpPollConfig.php3)
//
//////////////////////////////////////////////////////////////////////
//
// Author: tig
// Last change: 99/06/02
//
//////////////////////////////////////////////////////////////////////

function poll_viewPoll()
{
	global $poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd;
	global $poll_usePersistentConnects, $poll_dbName, $poll_descTableName;

	// view all poll results
	echo "<h2>View poll results</h2>";

	echo "<p>Please choose a poll from the list below.</p>";

	// connect to database
	if($poll_usePersistentConnects == 0)
		$poll_mySQL_ID = mysql_connect($poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd);
	else
		$poll_mySQL_ID = mysql_pconnect($poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd);

	// start paragraph listing all polls
	echo "<p>";

	// select all descriptions
	$poll_result = mysql_db_query($poll_dbName, "SELECT * FROM $poll_descTableName ORDER BY timeStamp"); 
	if(!$poll_result)
	{
		echo mysql_errno(). ": ".mysql_error(). "<br>";
		return;
	}

	// start form listing all polls
	echo "<form action=\"".basename($GLOBALS[PHP_SELF])."\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"poll_action\" value=\"viewPosted\">";

	echo "<table>";

	// cycle through the descriptions until everyone has been fetched
	while($poll_object = mysql_fetch_object($poll_result))
	{
		echo "<tr><td><input type=\"radio\" name=\"poll_id\" value=\"".$poll_object->pollID."\">".$poll_object->pollTitle."</td></tr>";
	}

	echo "</table>";

	echo "<input type=\"submit\" value=\"View\">";
	echo "</form>";

	// close link to database
	if($poll_usePersistentConnects == 0)
		mysql_close($poll_mySQL_ID);

}

//////////////////////////////////////////////////////////////////////
//
// Function poll_view()
//
//////////////////////////////////////////////////////////////////////
//
// Display a poll's results
//
//////////////////////////////////////////////////////////////////////
//
// Calls to:
//	poll_viewResults()
//
//////////////////////////////////////////////////////////////////////
//
// Global references:
//	$poll_id
//		(submitted)
//
//////////////////////////////////////////////////////////////////////
//
// Author: tig
// Last change: 99/05/10
//
//////////////////////////////////////////////////////////////////////

function poll_viewPosted()
{
	global $poll_id;

	// view this poll's results
	echo "<h2>View poll results</h2>";

	poll_viewResults($poll_id);

}

//////////////////////////////////////////////////////////////////////
//
// Main program part, evaluates the state variables and calls
// the subroutines accordingly.
//
//////////////////////////////////////////////////////////////////////

// decide what state we are in
switch($poll_action)
{
	case "create":
		poll_createPoll();
		break;

	case "createPosted":
		poll_createPosted();
		break;

	case "remove":
		poll_removePoll();
		break;

	case "removePosted":
		poll_removePosted();
		break;
    
	case "view": 
		poll_viewPoll();
		break;

	case "viewPosted":
		poll_viewPosted();
		break;

	default:
		poll_mainMenu();
		break;
}

//////////////////////////////////////////////////////////////////////

?>


</body>
</html>
