<?php


require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/url-parser.php');

#require_once($_SERVER['DOCUMENT_ROOT'] . '/../php-rest-service/RestService/Server.php');
#require_once($_SERVER['DOCUMENT_ROOT'] . '/../php-rest-service/RestService/Client.php');
#use RestService\Server;

#echo phpinfo();


# for example: https://www.freshports.org/sysutils/anvil/?branch=quarterly
$url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

# $url_query will be everything after the question mark
$url_parts = array();
$url_parts = parse_url($url, -1);

/*
For the url example above, we'd get:

array(4) {
  ["scheme"]=>
  string(5) "https"
  ["host"]=>
  string(18) "www.freshports.org"
  ["path"]=>
  string(16) "/sysutils/anvil/"
  ["query"]=>
  string(16) "branch=quarterly"
}
*/

$Debug = isset($url_parts['Debug']);
$Debug = 0;
if ($Debug) echo 'The URL is ' .  $url;

$url_query = $url_parts['query'] ?? ''; # branch=quarterly
$url_path  = $url_parts['path']  ?? ''; # /sysutils/anvil/

# the query string as key => value pairs, e.g. port=sysutils/anvil for a badge
$query_parts = array();
parse_str($url_query, $query_parts);

if ($Debug) {
#    phpinfo();
    echo '<pre>';

    echo 'script = ';
    var_dump($url_path);

    echo 'url_query = ';
    var_dump($url_query);

    echo 'url parts = ';
    var_dump($url_parts);
    echo '</pre>';
}

define('SCRIPT_BADGES', '/--/badges/');
define('SCRIPT_API',    '/--/api/1/search/');
define('SCRIPT_STATUS', '/--/status/');

$items = explode('/', $url_path);
if ($Debug) {
    echo 'The parts from $url_path are: <pre>';
    var_dump($items);
    echo '</pre>';
    echo "url_path = $url_path";
}

# Someday, change this entire file so it uses php-rest-service.  In the meantime, do this:
$script = '';
if (strpos($url_path, SCRIPT_API)    === 0) $script = SCRIPT_API;
if (strpos($url_path, SCRIPT_BADGES) === 0) $script = SCRIPT_BADGES;
if (strpos($url_path, SCRIPT_STATUS) === 0) $script = SCRIPT_STATUS;

switch($script) {
    case SCRIPT_BADGES:
        require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/badges.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
        
        $category_port = pg_escape_string($db, $query_parts['port'] ?? '');
        
        list($category, $port) = array_pad(explode('/', $category_port, 2), 2, '');
        $myPort = new Port($db);
        $result = $myPort->Fetch($category, $port);
        $port_badge = new port_badge($db, $myPort);
        if (!empty($result)) {
            $badge = $port_badge->url();
            header("Location: $badge", true, 303);
            exit;
        } else {
            $img = $port_badge->not_found();
            header("Location: $img", true, 303);
            exit;
        }

        break;
 
    case SCRIPT_STATUS:
        require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/system-status-page.php');

        freshports_SystemStatusPage($db);
        break;

    case SCRIPT_API:
        require_once($_SERVER['DOCUMENT_ROOT'] . '/../api/1/api-search.php');
        echo '<br>api invoked</br>';

		/*
		Server::create('/--/api')
			->addGetRoute('test', function(){
		#    logger('test');
		syslog(LOG_WARNING, 'testing');
				return 'Yay!';
			})
			->addGetRoute('foo/(.*)', function($bar){
				return $bar;
			})
		->run();
		*/

		echo '  oh oh';

		#var_dump($db);
		Server::create('/--/api/1/search', 'freshportsAPISearch\Search')
			->setDebugMode(DEBUG)
			->collectRoutes()
		->run();

		echo 'done';
        break;

    default:
		# we should never return from this function.
		freshports_Parse404URI($url, $db);
}
