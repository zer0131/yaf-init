<?php

/**
 * @author ryan
 * @desc 配置读取
 */
class Fx_Conf {

    /**
     * 经过封装的获取配置方法
     * @param string $item 配置项，如：application.baseUri
     * @param string $default 默认值
     * @param string $section 配置节点，如：debug
     * @param string $app 获取的app名称
     * @return mixed
     */
    public static function getConf($item, $default = null, $section = null, $app = null) {
        $item = rtrim($item, '.');
        $item = ltrim($item, '.');
        if (!$item) {
            return $default ? $default : null;
        }
        !$section && $section = Yaf_Application::app()->environ();
        $confPath = Fx_AppEnv::getEnv('conf', $app) . '/' . CONF_FILE;
        if (!file_exists($confPath)) {
            return $default ? $default : null;
        }
        $confObj = new Yaf_Config_Ini($confPath, $section);
        if (!self::_check(explode('.', $item), $confObj)) {
            return $default ? $default : null;
        }
        $conf = $confObj->get($item);
        if (is_object($conf)) {
            return $conf->toArray();
        }
        return $conf;
    }

    /**
     * 直接获取yaf中的配置对象
     * @param string $section
     * @param string $app
     * @return Yaf_Config_Ini
     */
    public static function getOriginConf($section = null, $app = null) {
        !$section && $section = Yaf_Application::app()->environ();
        $confPath = Fx_AppEnv::getEnv('conf', $app) . '/' . CONF_FILE;
        return new Yaf_Config_Ini($confPath, $section);
    }

    //校验配置项
    private static function _check($itemArr, $confObj) {
        $flag = false;
        foreach ($itemArr as $item) {
            if (isset($confObj->$item)) {
                array_shift($itemArr);
                if (count($itemArr) === 0) {
                    $flag = true;
                    break;
                }
                return self::check($itemArr, $confObj->get($item));
            }
            break;
        }
        return $flag;
    }

}