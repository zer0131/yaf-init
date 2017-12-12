<?php

/**
 * @author ryan
 * @desc 错误处理controller
 */
class Controller_Error extends \Yaf\Controller_Abstract {

    public function errorAction($exception) {
        \Fx\Log::warning(__METHOD__. sprintf(' errFile:%s, errLine:%s, errCode:%s, errMsg:%s', $exception->getFile(), $exception->getLine(), $exception->getCode(), $exception->getMessage()));
        header('HTTP/1.1 404 Not Found');
        header("status: 404 Not Found");
    }
}