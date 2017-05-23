<?php
/**
 * @author zhangenrui
 * @desc 项目入口文件
 */

require_once __DIR__."/../../lib/fx/Init.php";
$app = Fx_Init::init();
$app->bootstrap()->run();