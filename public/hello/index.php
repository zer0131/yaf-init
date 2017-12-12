<?php

require_once __DIR__."/../../lib/fx/Init.php";
$app = \Fx\Init::init('hello');
$app->bootstrap()->run();
