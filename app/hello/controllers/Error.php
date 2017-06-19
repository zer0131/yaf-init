<?php

/**
 * @author ryan
 * @desc 错误处理controller
 */
class Controller_Error extends Yaf_Controller_Abstract {

    public function errorAction($exception) {
        Fx_Log::warning($exception->getFile() . $exception->getLine() . $exception->getMessage());
        switch ($exception->getCode()) {
            case Yaf_ERR_LOADFAILD:
            case Yaf_ERR_LOADFAILD_MODULE:
            case Yaf_ERR_LOADFAILD_CONTROLLER:
            case Yaf_ERR_LOADFAILD_ACTION:
                //404
                header('HTTP/1.1 404 Not Found');
                header("status: 404 Not Found");
                break;
            case Yaf_ERR_NOTFOUND_ACTION:
                header('HTTP/1.1 404 Not Found');
                header("status: 404 Not Found");
                break;
            case CUSTOM_ERROR_CODE:
                //自定义的异常
                break;
        }
    }
}