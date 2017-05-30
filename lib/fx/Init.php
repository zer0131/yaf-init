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

        //执行产品线的auto_prepend
        self::_doProductPrepend();

        return Yaf_Application::app();
    }

    private static function _initBasicEnv() {
        define('REQUEST_TIME_US', intval(microtime(true) * 1000000));
        define('ROOT_PATH', realpath(dirname(__FILE__) . '/../../'));
        define('CONF_PATH', ROOT_PATH . '/conf');
        define('DATA_PATH', ROOT_PATH . '/data');
        define('LOG_PATH', ROOT_PATH . '/log');
        define('APP_PATH', ROOT_PATH . '/app');
        define('LIB_PATH', ROOT_PATH . '/lib');
        define('WEB_ROOT', ROOT_PATH . '/public');
    }

    private static function _initAppEnv($appName) {
        // 检测当前App
        if ($appName != null || ($appName = self::_getAppName()) != null) {
            define('IS_FOX', true);
            define('MAIN_APP', $appName);
        } else {
            define('IS_FOX', false);
            define('MAIN_APP', 'unknown-app');
        }

        if (!IS_FOX) {
            exit('Init App Error');
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
        // 读取App的yaf框架配置
        //require_once LIB_PATH.'/fx/Conf.php';
        //$conf = Fx_Conf::getAppConf('yf');

        // 设置代码目录，其他使用默认或配置值
        $confPath = Fx_AppEnv::getEnv('conf');

        // 生成Yaf实例
        $app = new Yaf_Application($confPath . '/application.ini');

        define('CLIENT_IP', Fx_Ip::getClientIp());
        define('USER_IP', Fx_Ip::getUserIp());
        define('FRONTEND_IP', Fx_Ip::getFrontendIp());
        define("MODULE", MAIN_APP);

        return true;
    }

    private static function _doProductPrepend() {
        if (file_exists(APP_PATH . "/AutoPrepend.php")) {
            include_once APP_PATH . "/AutoPrepend.php";
        }
    }

}