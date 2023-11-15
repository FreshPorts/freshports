<?php

include('lib_autolink.php');

$html = "http://mail.example.org?email=foo@example.org";

$html = htmlspecialchars($html);

$html = autolink($html,0);

echo $html . "\n";

$html = autolink_email($html);

echo $html . "\n";
