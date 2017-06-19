<?php
/**
 * @author ryan
 * @desc CLI请求入口。请求方式示例：php ./cli.php request_uri=/cli/action_name/arg1/value1/arg2/value2
 */

ini_set('memory_limit','4096M');
require_once __DIR__ . "/../../../lib/fx/Init.php";
$app = Fx_Init::init('hello');
$app->bootstrap();
$app->getDispatcher()->dispatch(new Yaf_Request_Simple());
