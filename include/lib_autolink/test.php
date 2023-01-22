<?php

include('lib_autolink.php');

$html = "http://this.is.link/x.php?email=somebody@host.com&anotheremail=other@other.org&x=1";

$html = autolink($html,0);

echo $html . "\n";

$html = autolink_email($html);

echo $html . "\n";
