<?
//////////////////////////////////////////////////////////////////////
//
// phpPolls - A voting booth for PHP3
//
// This file is "phpPollUI.php3" and is responsible for
// generating all HTML user-interfaces for a poll.
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

<?
//////////////////////////////////////////////////////////////////////
//
// Function poll_generateUI()
//
//////////////////////////////////////////////////////////////////////
//
// This function generates the HTML code allowing a user to vote
// for a certain poll. The ID of this poll has to be given as function
// parameter, as well as a forwarder.
//
//////////////////////////////////////////////////////////////////////
//
// Parameters:
//	poll_id -
//		ID number of the poll to be used (MUST be valid)
//	poll_forwarder -
//		URL that will be used as a forwarder in the resulting
//		page to forward the user to an application-defined
//		target page.
//
//////////////////////////////////////////////////////////////////////
//
// Returns:
//	0 - error (prints error to output)
//	1 - ok
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
//	$poll_usePersistentConnects
//	$poll_dbName, $poll_dataTableName, $poll_maxOptions
//	$poll_baseURL
//		(from phpPollConfig.php3)
//
//////////////////////////////////////////////////////////////////////
//
// Author: tig
// Last change: 99/06/02
//
//////////////////////////////////////////////////////////////////////

function poll_generateUI($poll_id, $poll_forwarder)
{
	global $poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd;
	global $poll_dbName, $poll_dataTableName, $poll_maxOptions;
	global $poll_usePersistentConnects, $poll_baseURL;

	// connect to database
	if($poll_usePersistentConnects == 0)
		$poll_mySQL_ID = mysql_connect($poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd);
	else
		$poll_mySQL_ID = mysql_pconnect($poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd);

	// setup a form
	echo "<form action=\"$poll_baseURL/phpPollCollector.php3\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"poll_id\" value=\"".$poll_id."\">";
	echo "<input type=\"hidden\" name=\"poll_forwarder\" value=\"".$poll_forwarder."\">";

	// no default selected yet
	$poll_default = 0;

	// cycle through all options
	for($i = 1; $i <= $poll_maxOptions; $i++)
	{
		// select next vote option
		$poll_result = mysql_db_query($poll_dbName, "SELECT * FROM $poll_dataTableName WHERE (pollID=$poll_id) AND (voteID=$i)");
		if(!$poll_result)
		{
			echo mysql_errno(). ": ".mysql_error(). "<br>";
			return(0);
		}

		// fetch field
		$poll_object = mysql_fetch_object($poll_result);

		if(is_object($poll_object))
		{
			$poll_optionText = $poll_object->optionText;

			if($poll_optionText != "")
			{
				echo "<input type=\"radio\" name=\"poll_voteNr\" value=\"".$i."\" ";

				// set the first button as default
				if($poll_default == 0)
				{
					$poll_default = 1;
					echo "checked ";
				}

				echo "> $poll_optionText <br>";
			}
		}
	}

	// show submit button
	echo "<input type=\"submit\" value=\"Vote\">";

	// close form
	echo "</form>";

	// close link to database
	if($poll_usePersistentConnects == 0)
		mysql_close($poll_mySQL_ID);

	return(1);

}

//////////////////////////////////////////////////////////////////////
//
// Function poll_viewResults()
//
//////////////////////////////////////////////////////////////////////
//
// This function generates HTML code showing a poll's results.
// It displays them in a table for which parameters can be adjusted
// in phpPollConfig.php3
//
//////////////////////////////////////////////////////////////////////
//
// Parameters:
//	$poll_id -
//		ID of poll to show results for (MUST be valid)
//	$poll_tableHeader, $poll_tableFooter -
//		Tags surrounding the output table (<table></table>)
//	$poll_rowHeader, $poll_rowFooter -
//		Tags surrounding each row (<tr></tr>
//	$poll_dataHeader, $poll_dataFooter -
//		Tags surrounding each data entry (<td></td>)
//
//////////////////////////////////////////////////////////////////////
//
// Returns:
//	0 - error (prints error to output)
//	1 - ok
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
//	$poll_usePersistentConnects
//	$poll_dbName, $poll_descTableName, $poll_dataTableName
//	$poll_maxOptions, $poll_resultBarScale, $poll_resultBarHeight
//	$poll_resultTableBgColor, $poll_resultBarFile, $poll_baseURL
//		(from phpPollConfig.php3)
//
//////////////////////////////////////////////////////////////////////
//
// Author: tig
// Last change: 99/09/21
//
//////////////////////////////////////////////////////////////////////

function poll_viewResults($poll_id, $poll_tableHeader="<table border=1>", $poll_rowHeader="<tr>", $poll_dataHeader="<td>", $poll_dataFooter="</td>", $poll_rowFooter="</tr>", $poll_tableFooter="</table>")
{
	global $poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd, $poll_usePersistentConnects;
	global $poll_dbName, $poll_descTableName, $poll_dataTableName, $poll_maxOptions;
	global $poll_resultBarScale, $poll_resultBarHeight, $poll_resultTableBgColor;
	global $poll_resultBarFile, $poll_baseURL;

	// connect to database
	if($poll_usePersistentConnects == 0)
		$poll_mySQL_ID = mysql_connect($poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd);
	else
		$poll_mySQL_ID = mysql_pconnect($poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd);

	$poll_result = mysql_db_query($poll_dbName, "SELECT SUM(optionCount) AS SUM FROM $poll_dataTableName WHERE pollID=$poll_id");
	if(!$poll_result)
	{
		echo mysql_errno(). ": ".mysql_error(). "<br>";
		return(0);
	}

	$poll_sum = (int)mysql_result($poll_result, 0, "SUM"); 
	mysql_free_result($poll_result); 

	echo $poll_tableHeader;

	// cycle through all options
	for($i = 1; $i <= $poll_maxOptions; $i++)
	{
		// select next vote option
		$poll_result = mysql_db_query($poll_dbName, "SELECT * FROM $poll_dataTableName WHERE (pollID=$poll_id) AND (voteID=$i)");
		if(!$poll_result)
		{
			echo mysql_errno(). ": ".mysql_error(). "<br>";
			return(0);
		}

		// fetch field
		$poll_object = mysql_fetch_object($poll_result);

		if(is_object($poll_object))
		{
			$poll_optionText = $poll_object->optionText;
			$poll_optionCount = $poll_object->optionCount;

			echo $poll_rowHeader;

			if($poll_optionText != "")
			{
				echo $poll_dataHeader;
				echo "$poll_optionText";
				echo $poll_dataFooter;

				if($poll_sum)
					$poll_percent = 100 * $poll_optionCount / $poll_sum;
				else
					$poll_percent = 0;

				echo $poll_dataHeader;

				if ($poll_percent > 0)
				{
					$poll_percentScale = (int)($poll_percent * $poll_resultBarScale);
					echo "<img src=\"$poll_baseURL/$poll_resultBarFile\" height=$poll_resultBarHeight width=$poll_percentScale>";
				}

				echo $poll_dataFooter;
				echo $poll_dataHeader;

                                printf(" %.2f %% (%d)", $poll_percent, $poll_optionCount);

				echo $poll_dataFooter;
		        }

			echo $poll_rowFooter;

		}

	}

	echo $poll_rowHeader;
	echo $poll_dataHeader;
	echo "Total votes: $poll_sum";
	echo $poll_dataFooter;
	echo $poll_rowFooter;

	echo $poll_tableFooter;

	// close link to database
	if($poll_usePersistentConnects == 0)
		mysql_close($poll_mySQL_ID);

	return(1);

}

//////////////////////////////////////////////////////////////////////
//
// Function poll_getResults()
//
//////////////////////////////////////////////////////////////////////
//
// This function gets a poll's result and returns it in an array.
//
//////////////////////////////////////////////////////////////////////
//
// Parameters:
//	$poll_id -
//		ID of poll to show results for (MUST be valid)
//
//////////////////////////////////////////////////////////////////////
//
// Returns:
//	0 - error (prints error to output)
//	array results - 
//		This is a multi-dimensional array containing various information
//		of the poll identified with $poll_id.
//		The first element of the array ($result[0]) contains the topic of
//		the poll and the number of total votes:
//			$result[0]["title"] - topic of the poll
//			$result[0]["votes"] - total number of votes for this poll
//		The next elemts contain information about the individual poll
//		options. If a poll has two options, it would contain two more elements -
//		$results[1] and $results[2] - both being an array again with the following
//		elements:
//			$result[n]["text"] - text of the option
//			$result[n]["votes"] - votes for this option
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
//	$poll_usePersistentConnects
//	$poll_dbName, $poll_descTableName, $poll_dataTableName
//		(from phpPollConfig.php3)
//
//////////////////////////////////////////////////////////////////////
//
// Author: tobias
// Last change: 99/06/04
//
//////////////////////////////////////////////////////////////////////

function poll_getResults($poll_id)
{
	global $poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd, $poll_usePersistentConnects;
	global $poll_dbName, $poll_descTableName, $poll_dataTableName, $poll_maxOptions;

	$ret = array();
    
	// connect to database
	if($poll_usePersistentConnects == 0)
		$poll_mySQL_ID = mysql_connect($poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd);
	else
		$poll_mySQL_ID = mysql_pconnect($poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd);

	$poll_result = mysql_db_query($poll_dbName, "SELECT SUM(optionCount) AS SUM FROM $poll_dataTableName WHERE pollID=$poll_id");
	if(!$poll_result)
	{
		echo mysql_errno(). ": ".mysql_error(). "<br>";
		return(0);
	}

	$poll_sum = mysql_result($poll_result, 0, "SUM"); 
    
	$poll_result = mysql_db_query($poll_dbName, "SELECT pollTitle FROM $poll_descTableName WHERE pollID=$poll_id");
	if(!$poll_result)
	{
		echo mysql_errno(). ": ".mysql_error(). "<br>";
		return(0);
	}
    
	$poll_title = mysql_result($poll_result, 0, "pollTitle"); 
       
	$ret[0] = array("title"=>$poll_title, "votes"=>$poll_sum);
    
	// select next vote option
	$poll_result = mysql_db_query($poll_dbName, "SELECT * FROM $poll_dataTableName WHERE pollID=$poll_id");
	if(!$poll_result)
	{
		echo mysql_errno(). ": ".mysql_error(). "<br>";
		return(0);
	}

	while ($row = mysql_fetch_array($poll_result))
	{
		$ret[] = array("text"=>$row["optionText"], "votes"=>$row["optionCount"]);
	}

	// close link to database
	if($poll_usePersistentConnects == 0)
		mysql_close($poll_mySQL_ID);

	return($ret);

}

//////////////////////////////////////////////////////////////////////
//
// Function poll_listPolls()
//
//////////////////////////////////////////////////////////////////////
//
// This function returns all available polls in a two-dimensional
// array, structured as [pollDescription, pollID]
//
//////////////////////////////////////////////////////////////////////
//
// Parameters:
//	none
//
//////////////////////////////////////////////////////////////////////
//
// Returns:
//	Array listing all polls along with ID [pollDescription, pollID]
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
//	$poll_usePersistentConnects
//	$poll_dbName, $poll_descTableName
//		(from phpPollConfig.php3)
//
//////////////////////////////////////////////////////////////////////
//
// Author: tig
// Last change: 99/06/02
//
//////////////////////////////////////////////////////////////////////

function poll_listPolls()
{
	global $poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd;
	global $poll_usePersistentConnects, $poll_dbName, $poll_descTableName;

	// connect to database
	if($poll_usePersistentConnects == 0)
		$poll_mySQL_ID = mysql_connect($poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd);
	else
		$poll_mySQL_ID = mysql_pconnect($poll_mySQL_host, $poll_mySQL_user, $poll_mySQL_pwd);

	// select all descriptions
	$poll_result = mysql_db_query($poll_dbName, "SELECT * FROM $poll_descTableName ORDER BY timeStamp"); 
	if(!$poll_result)
	{
		echo mysql_errno(). ": ".mysql_error(). "<br>";
		return;
	}

	$counter = 0;

	// cycle through the descriptions until everyone has been fetched
	while($poll_object = mysql_fetch_object($poll_result))
	{
		$resultArray[$counter] = array($poll_object->pollID, $poll_object->pollTitle);
		$counter++;
	}

	if($poll_usePersistentConnects == 0)
		mysql_close($poll_mySQL_ID);

	return($resultArray);

}

//////////////////////////////////////////////////////////////////////

?>
