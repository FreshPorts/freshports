<?php


require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/common.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/freshports.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/databaselogin.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../include/getvalues.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/functions.php');

#require_once($_SERVER['DOCUMENT_ROOT'] . '/../php-rest-service/RestService/Server.php');
#require_once($_SERVER['DOCUMENT_ROOT'] . '/../php-rest-service/RestService/Client.php');
#use RestService\Server;

#phpinfo();



$script = $_SERVER['REQUEST_URI'];
$query  = $_SERVER['QUERY_STRING'];

$url = parse_url($query);

parse_str($query, $url_parts);

$Debug = isset($url_parts['Debug']);
$Debug = 0;
if ($Debug) {
#    phpinfo();
    echo '<pre>';

    echo 'script = ';
    var_dump($script);

    echo 'query = ';
    var_dump($query);

    echo 'url = ';
    var_dump($url);

    echo 'url parts = ';
    var_dump($url_parts);
    echo '</pre>';
}

define('SCRIPT_BADGES', '/--/badges/');
define('SCRIPT_API',    '/--/api/1/search/');
define('SCRIPT_STATUS', '/--/status/');
define('SCRIPT_JSON', '/--/json/');

$items = explode('/', $script);
if ($Debug) {
echo '<pre>';
var_dump($items);
echo '</pre>';

echo "script = $script";
}

# change this entire file so it uses php-rest-service.  In the meantime, do this:
if (strpos($script, SCRIPT_API)    === 0) $script = SCRIPT_API;
if (strpos($script, SCRIPT_BADGES) === 0) $script = SCRIPT_BADGES;
if (strpos($script, SCRIPT_STATUS) === 0) $script = SCRIPT_STATUS;
if (strpos($script, SCRIPT_JSON) === 0) $script = SCRIPT_JSON;

switch($script) {
    case SCRIPT_BADGES:
        require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/badges.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
        
        $category_port = pg_escape_string($url_parts['port']);
        
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
    case SCRIPT_JSON:
        require_once($_SERVER['DOCUMENT_ROOT'] . '/../classes/ports.php');
        
        $category_port = pg_escape_string($url_parts['port']);
        
        list($category, $port) = explode('/', $category_port);
        $myPort = new Port($db);
        $result = $myPort->Fetch($category, $port);
        header('Content-Type: application/json');		
        if (!empty($result)) {
	    $response = get_object_vars($myPort)
	    echo json_encode($response);	
            exit;
        } else {
            $error = ["error" => "Port not found"];
            echo json_encode($error);
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
            echo 'We are in maintenance mode<br>';
        } else {
            echo 'We are in not in maintenance mode';
        }

        echo '</p><p>';

        if ($status->LoginsAreAllowed()) {
            echo 'Logins are enabled<br>';
        } else {
            echo 'Nobody is allowed to login right now.';
        }
        echo '</p>';

        require_once("/var/db/freshports/cache/html/backend-status.html");
        echo "</body>\n";
        exit;
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
        $result = freshports_Parse404URI($_SERVER['REQUEST_URI'], $db);

        # if you get here, we could not find anything in the database, so let's run 
        # the 404 code.
        #
        # XXX move missing.php out of DOCUMENT_ROOT
        #echo "\$result='$result'";

        if ($result != '') {
	    require_once($_SERVER['DOCUMENT_ROOT'] . '/../rewrite/missing.php');
        }
}
