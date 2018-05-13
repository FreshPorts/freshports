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

$items = explode('/', $script);
if ($Debug) {
echo '<pre>';
var_dump($items);
echo '</pre>';

echo "script = $script";
}

# change this entire file so it uses php-rest-service.  In the meantime, do this:
if (strpos($script, SCRIPT_API) === 0) $script = SCRIPT_API;

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
