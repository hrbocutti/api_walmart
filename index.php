<?php

use App\Route\Routes;

require "bootstrap.php";

/*$handle = fopen("log/routeLog.txt", "a+");
$content = getallheaders();
fwrite($handle, date("Y-m-d H:i:s") . PHP_EOL);
fwrite($handle, " ################  ENTRADA ################ " . PHP_EOL);
fwrite($handle, json_encode($content) . PHP_EOL);
fwrite($handle, file_get_contents('php://input') . PHP_EOL);
fwrite($handle, " ################ FIM ENTRADA ################ " . PHP_EOL);
fwrite($handle, PHP_EOL);
fclose($handle);*/

$route = new Routes();
$route->routes();