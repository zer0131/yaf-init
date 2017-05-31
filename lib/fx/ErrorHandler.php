<?php

/**
 * @author ryan
 * @desc 错误处理
 */
class Fx_ErrorHandler {
    private $_errorCode = array(
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_COMPILE_ERROR
    );

    const MESSAGE_TYPE = 3;

    private $_logPath = '';

    public function __construct() {
        $this->_logPath = LOG_PATH . '/php-error.log';
        /*if (!is_file($this->_logPath)) {
            var_dump(file_put_contents($this->_logPath, ''));
            exit;
        }*/
    }

    public static function registerErrorHandler() {
        $handler = new static();
        $handler->registerNormalErrorHandler();
        $handler->registerFatalHandler();
        return $handler;
    }

    public function registerNormalErrorHandler() {
        set_error_handler(array(
            $this,
            'handleError'
        ));
    }

    public function registerFatalHandler() {
        register_shutdown_function(array(
            $this,
            'handleFatal'
        ));
    }

    public function handleError($code, $message, $file = '', $line = 0) {
        if (error_reporting() & $code) {
            $level = self::_codeToString($code);
            $logid = '';//Fx_Log::genLogID();
            $str = "PHP {$level}: logid: {$logid} {$message} in {$file} on {$line}";
            error_log($str, self::MESSAGE_TYPE, $this->_logPath);
        }
    }


    public function handleFatal() {
        $lastError = error_get_last();
        if ($lastError && in_array($lastError['type'], $this->_errorCode)) {
            $logid = '';//Fx_Log::genLogID();
            $str = "PHP Fatal: logid: {$logid} {$lastError['message']} in {$lastError['file']} on {$lastError['line']}";
            error_log($str, self::MESSAGE_TYPE, $this->_logPath);
        }
    }

    private static function _codeToString($code) {
        switch ($code) {
            case E_ERROR:
                return 'Error';
            case E_WARNING:
                return 'Warning';
            case E_NOTICE:
                return 'Notice';
            case E_STRICT:
                return 'Strict';
            case E_DEPRECATED:
                return 'Deprecated';
        }
        return 'Unknown PHP error';
    }
}