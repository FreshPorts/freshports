<?php

#
# Provide a listing of the latest package imports
# this page usually lags behing the database by about an hour.
# imports are run at the top of the hour, and so is this page.
# imports take longer and are usually completed after this page is built.
#

$Debug = 0;

define('PACKAGE_FILE_DATE', "/var/db/freshports/cache/html/package-imports-date.txt");
define('PACKAGE_FILE_NAME', "/var/db/freshports/cache/html/package-imports-name.txt");

# optional sort by
#if (IsSet($_REQUEST['sort']) && defined($_REQUEST['sort'])) {
if (IsSet($_REQUEST['sort'])) {
    $sort = $_REQUEST['sort'];
    if ($Debug) echo "incoming sort is specified<br>";
} else {
    $sort = '';
    if ($Debug) echo "incoming sort is not specified<br>";
}

switch ($sort) {
    case 'name':
        $cache_file = PACKAGE_FILE_NAME;
        if ($Debug) echo "sort specified is name<br>";
        break;

    case 'date':
        $cache_file = PACKAGE_FILE_DATE;
        if ($Debug) echo "sort specified is date<br>";
        break;

    default:
        # if no valid parameter supplied, redirect to sort by name
        header("HTTP/1.1 301 Moved Permanently");
        header('Location: ' . $_SERVER['SCRIPT_NAME'] . '?sort=name');
        exit;
}


if (file_exists($cache_file)) {
    $myfile = fopen($cache_file, 'r');

    $HTML = fread($myfile, filesize($cache_file));
    fclose($myfile);

    header("HTTP/1.1 200 OK");
    $modified = date("F d Y H:i:s", filemtime($cache_file));
    $modified = gmdate("D, d M Y H:i:s \G\M\T", filemtime($cache_file));

    header("Last-Modified: " . $modified);
} else {
    header("HTTP/1.1 500 Internal Server Error");

    $HTML = 'The required file was not found: ' . $cache_file;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta http-equiv="Content-Type" content="text/plain charset=UTF-8">
</head>
<body>

<pre>

This page can sort by name or date.

<?php

#echo phpinfo();

echo "sorted by: $sort\n";

echo "cache file: $cache_file\n";

echo "Last-Modified: " . $modified . "\n";

echo $HTML;

?>

</pre>
</body>
</html>
