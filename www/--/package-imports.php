<?php

# Provide a listing of the latest package imports

define('PACKAGE_FILE', "/var/db/freshports/cache/html/package-imports.txt");


if (file_exists(PACKAGE_FILE)) {
    $myfile = fopen(PACKAGE_FILE, 'r');

    $HTML = fread($myfile, filesize(PACKAGE_FILE));
    fclose($myfile);

    header("HTTP/1.1 200 OK");
    $modified = date("F d Y H:i:s.", filemtime(PACKAGE_FILE));
    header("Last-Modified: " . $modified);
} else {
    header("HTTP/1.1 500 Internal Server Error");

    $HTML = 'The required file was not found: ' . PACKAGE_FILE;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta http-equiv="Content-Type" content="text/plain charset=UTF-8">
</head>
<body>

<pre>

<?php

echo $HTML;

?>

</pre>
</body>
</html>
