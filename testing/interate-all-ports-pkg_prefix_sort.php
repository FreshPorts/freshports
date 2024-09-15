<?php

$_SERVER['DOCUMENT_ROOT'] = "/usr/local/www/freshports/www";

require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
require_once('include/constants.php');
require_once('include/freshports.php');
require_once('include/databaselogin.php');
require_once('classes/port-display.php');
require_once('classes/ports.php');


$sql = "
select P.id, E.name as port, C.name as category
  FROM ports P join element E on P.element_id = E.id
  JOIN categories C on P.category_id = C.id
ORDER by category, port
";

$result = pg_query_params($db, "set client_encoding = 'ISO-8859-15'", array()) or die('query failed ' . pg_last_error($db));

$result = pg_query_params($db, $sql, array()) or die('query failed ' . pg_last_error($db));

$numrows = pg_num_rows($result);

echo "We have $numrows ports for testing\n";

$rows = pg_fetch_all($result);

foreach ($rows as $key => $row) {
  echo $key . ' ' . $row['category'] . '/' . $row['port'] . "\n";
  $port = new Port($db);
  $port->FetchByID($row['id']);
  $port_display = new port_display($db);
  $port_display->SetPort($port);
  $port_display->SetDetailsPackages();

  $HTMLPortPackages = $port_display->Display();
  
  unset($HTMLPortPackages);
  unset($port_display);
  unset($port);
}
