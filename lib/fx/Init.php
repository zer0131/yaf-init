<?php

/**
 * @author ryan
 * @desc 项目是初始化类
 */
class Fx_Init {
    private static $_isInit = false;

    /**
     * 统一初始化入口
     * @param null $appName
     * @return object|bool
     */
    public static function init($appName = null) {

        !extension_loaded('yaf') && exit(1);

        if (self::$_isInit) {
            return false;
        }
        self::$_isInit = true;

        //设置时区
        date_default_timezone_set('PRC');

        //初始化基础环境，设置常量
        self::_initBasicEnv();

        //设置app环境
        self::_initAppEnv($appName);

        //初始化框架
        self::_initYaf();

        //引入公共函数
        file_exists(LIB_PATH . '/fx/Functions.php') && require_once LIB_PATH . '/fx/Functions.php';

        //执行产品线的auto_prepend
        file_exists(APP_PATH . "/AutoPrepend.php") && include_once APP_PATH . "/AutoPrepend.php";

        //引入composer机制
        file_exists(__DIR__ . '/../vendor/autoload.php') && require_once __DIR__ . '/../vendor/autoload.php';

        return Yaf_Application::app();
    }

    private static function _initBasicEnv() {
        define('REQUEST_TIME_US', intval(microtime(true) * 1000000));
        define('REQUEST_ID', uniqid());
        define('ROOT_PATH', realpath(dirname(__FILE__) . '/../../'));
        define('DATA_PATH', ROOT_PATH . '/data');
        define('LOG_PATH', ROOT_PATH . '/log');
        define('APP_PATH', ROOT_PATH . '/app');
        define('LIB_PATH', ROOT_PATH . '/lib');
        define('WEB_ROOT', ROOT_PATH . '/public');
        define('CONF_FILE', 'application.ini');
    }

    private static function _initAppEnv($appName) {
        // 检测当前App
        if ($appName != null || ($appName = self::_getAppName()) != null) {
            define('IS_FOX', true);//在yaf框架中有效
            define('MAIN_APP', $appName);
            define('MAIN_APP_CONF', MAIN_APP . '_conf');
        } else {
            exit(1);
        }

        //系统信息
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            ini_set('magic_quotes_runtime', 0);
            define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc() ? true : false);
        } else {
            define('MAGIC_QUOTES_GPC', false);
        }

        //设置当前App
        require_once LIB_PATH . '/fx/AppEnv.php';
        Fx_AppEnv::setCurrApp(MAIN_APP);

        return true;
    }

    private static function _getAppName() {
        $appName = null;
        if (PHP_SAPI != 'cli') {
            $script = explode('/', rtrim($_SERVER['SCRIPT_NAME'], '/'));
            if ($script[2] == 'index.php') {
                $appName = $script[1];
            }
        } else {
            //使用cli执行需在APP_PATH目录下执行对应脚本
            $file = $_SERVER['argv'][0];
            if ($file[0] != '/') {
                $fullPath = realpath($file);
            } else {
                $fullPath = $file;
            }
            if (strpos($fullPath, APP_PATH . '/') === 0) {
                $s = substr($fullPath, strlen(APP_PATH) + 1);
                if (($pos = strpos($s, '/')) > 0) {
                    $appName = substr($s, 0, $pos);
                }
            }
        }
        return $appName;
    }

    private static function _initYaf() {
        $confPath = Fx_AppEnv::getEnv('conf');

        !file_exists($confPath . '/' . CONF_FILE) && exit(1);

        // 生成Yaf实例
        $app = new Yaf_Application($confPath . '/' . CONF_FILE);

        define('CLIENT_IP', Fx_Ip::getClientIp());
        define('USER_IP', Fx_Ip::getUserIp());
        define('FRONTEND_IP', Fx_Ip::getFrontendIp());
        define("MODULE", MAIN_APP);

        return true;
    }

}