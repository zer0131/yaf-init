<?php
/**
 * @author zhangenrui
 * @desc 项目入口文件
 */
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 'On');
require_once __DIR__."/../../lib/fx/Init.php";
$app = Fx_Init::init('hello');
$app->bootstrap()->run();