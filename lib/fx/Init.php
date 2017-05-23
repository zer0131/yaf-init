<?php

/**
 * @author ryan
 * @desc 项目是初始化类
 */
class Fx_Init {
    private static $_isInit = false;

    public static function init($appName = null) {
        if (self::$_isInit) {
            return false;
        }
        self::$_isInit = true;

        //设置时区
        date_default_timezone_set('PRC');

        //初始化基础环境，设置常量
        self::_initBasicEnv();

        return true;//Yaf_Application::app();
    }

    private static function _initBasicEnv() {
        define('REQUEST_TIME_US', intval(microtime(true)*1000000));
        define('ROOT_PATH', realpath(dirname(__FILE__) . '/../../'));
        define('CONF_PATH', ROOT_PATH.'/conf');
        define('DATA_PATH', ROOT_PATH.'/data');
        define('LOG_PATH', ROOT_PATH.'/log');
        define('APP_PATH', ROOT_PATH.'/app');
        define('LIB_PATH', ROOT_PATH.'/lib');
        define('WEB_ROOT', ROOT_PATH.'/public');
    }
}