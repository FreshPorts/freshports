<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/functions.php');


#echo phpinfo();

$script = $_SERVER['SCRIPT_URI'];
$query  = $_SERVER['QUERY_STRING'];

$url = parse_url($query);

parse_str($query, $url_parts);

$Debug = 0;
if ($Debug) {
#	phpinfo();
    echo '<pre>';

    echo 'script = ';
    var_dump($script);

    echo 'query = ';
    var_dump($query);

    echo 'url = ';
    var_dump($url);

    echo 'url parts = ';
    var_dump($url_parts);
}

$result = freshports_Parse404URI($_SERVER['REQUEST_URI'], $db);

# if you get here, we could not find anything in the database, so let's run 
# the 404 code.
#
# XXX move missing.php out of DOCUMENT_ROOT
#echo "\$result='$result'";

if ($result != '') {
	require($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/missing.php');
}
