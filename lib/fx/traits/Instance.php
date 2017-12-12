<?php

/**
 * @author zhangenrui
 * @desc 快速实例化实现
 */
namespace Fx\Traits;
trait Instance {

    private static $_obj;

    /**
     * 获取实例
     * @return static
     */
    public static function getInstance() {
        if (self::$_obj) {
            return self::$_obj;
        }
        $args = func_get_args();//参数
        $className = get_called_class();//运行时调用的类名
        $ref = new \ReflectionClass($className);//反射实例化
        self::$_obj = $ref->newInstanceArgs($args);
        return self::$_obj;
    }
}