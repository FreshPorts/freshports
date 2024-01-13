<?php
	#
	# $Id: commit_record.php,v 1.4 2012-12-21 18:20:53 dan Exp $
	#
	# Copyright (c) 1998-2006 DVL Software Limited
	#


// base class for a single commit
class CommitRecord {

	var $commit_log_id;
	var $commit_date_raw;
	var $message_subject;
	var $message_id;
	var $commit_hash_short;
	var $committer;
	var $committer_name;
	var $committer_email;
	var $author_name;
	var $author_email;
	var $commit_description;
	var $commit_date;
	var $commit_time;
	var $encoding_losses;
	var $port_id;
	var $needs_refresh;
	var $forbidden;
	var $broken;
	var $deprecated;
	var $ignore;
	var $element_id;
	var $version;
	var $revision;
	var $epoch;
	var $date_added;
	var $short_description;
	var $category_id;
	var $port;
	var $element_pathname;
	var $status;
	var $category;
	var $watch;
	var $vulnerable_current;
	var $vulnerable_past;
	var $restricted;
	var $no_cdrom;
	var $expiration_date;
	var $is_interactive;
	var $only_for_archs;
	var $not_for_archs;

	var $stf_message;
	var $svn_revision;
	var $repo_name;
	var $repository;
	var $repo_hostname;
	var $path_to_repo;

	function __construct() {
	}

	function PopulateValues($myrow) {
		$this->commit_log_id		= $myrow['commit_log_id'];
		$this->commit_date_raw		= $myrow['commit_date_raw'];
		$this->message_subject		= $myrow['message_subject'];
		$this->message_id		= $myrow['message_id'];
		$this->commit_hash_short	= $myrow['commit_hash_short'];
		$this->committer		= $myrow['committer'];
		$this->committer_name           = $myrow["committer_name"];
		$this->committer_email          = $myrow["committer_email"];
		$this->author_name              = $myrow["author_name"];
		$this->author_email             = $myrow["author_email"];
		$this->commit_description	= $myrow['commit_description'];
		$this->commit_date		= $myrow['commit_date'];
		$this->commit_time		= $myrow['commit_time'];
		$this->encoding_losses		= $myrow['encoding_losses'];
		$this->port_id			= $myrow['port_id'];
		$this->needs_refresh		= $myrow['needs_refresh'];
		$this->forbidden		= $myrow['forbidden'];
		$this->broken			= $myrow['broken'];
		$this->deprecated		= $myrow['deprecated'];
		$this->ignore			= $myrow['ignore'];
		$this->element_id		= $myrow['element_id'];
		$this->version			= $myrow['version'];
		$this->revision			= $myrow['revision'];
		$this->epoch			= $myrow['epoch'];
		$this->date_added		= $myrow['date_added'];
		$this->short_description	= $myrow['short_description'];
		$this->category_id		= $myrow['category_id'];
		$this->port			= $myrow['port'];
		$this->element_pathname		= $myrow['element_pathname'];
		$this->status			= $myrow['status'];
		$this->category			= $myrow['category'];
		$this->watch			= $myrow['watch'];
		$this->vulnerable_current	= $myrow['vulnerable_current'];
		$this->vulnerable_past		= $myrow['vulnerable_past'];
		$this->restricted		= $myrow['restricted'];
		$this->no_cdrom			= $myrow['no_cdrom'];
		$this->expiration_date		= $myrow['expiration_date'];
		$this->is_interactive		= $myrow['is_interactive'];
		$this->only_for_archs		= $myrow['only_for_archs'];
		$this->not_for_archs		= $myrow['not_for_archs'];
		$this->stf_message		= $myrow['stf_message'];
		$this->svn_revision             = $myrow['svn_revision'];
		$this->repo_name                = $myrow['repo_name'];
		$this->repo_hostname            = $myrow['repo_hostname'];
		$this->repository               = $myrow['repository'];
		$this->path_to_repo             = $myrow['path_to_repo'];
	}

	function EncodingLosses() {
		return $this->encoding_losses == 't';
	}

}
