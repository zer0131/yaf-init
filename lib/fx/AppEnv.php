<?php

/**
 * @author ryan
 * @desc app环境设置类
 */
class Fx_AppEnv {
    private static $_strCurrApp;
    private static $_arrEnv;

    /**
     * 设置当前App，返回前一个App，不传参数时，会设为主App
     * @param $app
     * @return string
     */
    public static function setCurrApp($app = null) {
        $strPrevApp = self::$_strCurrApp;
        self::$_strCurrApp = empty($app) ? MAIN_APP : $app;
        return $strPrevApp;
    }

    /**
     * 获取当前app
     * @return mixed
     */
    public static function getCurrApp() {
        return self::$_strCurrApp;
    }

    public static function getProduct() {
        //return PRODUCT;
    }

    public static function getSubSys() {
        //return SUBSYS;
    }

    /**
     * 获取当前或参数指定App的上下文环境值
     *
     * 预定义列表：conf - App的配置路径
     *             data - App的数据根目录
     *             code - App的代码根目录
     *
     * @param string $key
     * @param string $app
     * @return string
     */
    public static function getEnv($key, $app = null) {
        $curApp = empty($app) ? self::$_strCurrApp : $app;
        $env = '';
        switch ($key) {
            case 'conf':
                $env = CONF_PATH . "/app/$curApp";
                break;

            case 'data':
                $env = DATA_PATH . "/app/$curApp";
                break;

            case 'code':
                $env = APP_PATH . "/$curApp";
                break;

            case 'log':
                $env =  LOG_PATH . "/$curApp";
                break;

            default:
                $env = self::$_arrEnv[$curApp][$key];
        }
        return $env;
    }

    /**
     * 设置当前或参数指定App的上下文环境值
     *
     * note: 仅可设置非预定义的环境值
     *
     * @param string $key
     * @param string $value
     * @param string $app
     *
     * @return string
     */
    public static function setEnv($key, $value, $app = null) {
        $curApp = empty($app) ? self::$_strCurrApp : $app;
        self::$_arrEnv[$curApp][$key] = $value;
    }
}