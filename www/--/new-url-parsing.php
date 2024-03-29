<?php


require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/new-url-parsing.php');

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
        
        $category_port = pg_escape_string($db, $url_parts['port'] ?? '');
        
        list($category, $port) = explode('/', $category_port);
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
        require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/system_status.php');

        $status = new SystemStatus($db);

        echo "<body>\n";
        echo "<head>\n";
        echo '<meta http-equiv="refresh" content="180"> <!-- Refresh every 3 minutes -->' . "\n";
        echo "</head>\n";

        echo '<p>';
        if ($status->InMaintenanceMode()) {
            echo 'We are in maintenance mode.';
        } else {
            echo 'We are in not in maintenance mode.';
        }

        echo '</p><p>';

        if ($status->LoginsAreAllowed()) {
            echo 'Logins are enabled.';
        } else {
            echo 'Nobody is allowed to login right now.';
        }
        echo '</p>';

        define('STATUS_FILE', "/var/db/freshports/cache/html/backend-status.html");

        require_once(STATUS_FILE);

        passthru("/usr/sbin/service fp_listen status", $fp_listen_result);

        echo '<hr>';

        echo "<p>This status was last updated at " . gmdate('Y-m-d H:i:sO', filemtime(STATUS_FILE)) . '</p>';
        echo "<p>It should never be more than 4 minutes old.</p>";
        echo '<p>This page automatically reloads every 3 minutes.</p>';
        echo '<p>The contents are generated every 3 minutes by the backend server.</p>';

        echo "</body>\n";
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
