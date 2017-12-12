<?php

/**
 * @author ryan
 * @desc 日志操作类
 */
namespace Fx;
class Log {
    const LOG_LEVEL_FATAL = 0x01;
    const LOG_LEVEL_WARNING = 0x02;
    const LOG_LEVEL_NOTICE = 0x04;
    const LOG_LEVEL_TRACE = 0x08;
    const LOG_LEVEL_DEBUG = 0x10;

    const DEFAULT_FORMAT = '%L: %t [%f:%N] errno[%E] logId[%l] uri[%U] user[%u] refer[%{referer}i] cookie[%{cookie}i] %S %M';
    //const DEFAULT_FORMAT_PB = '%L: %t [%f:%N] errno[%E] logId[%l] uri[%U] user[%u] refer[%{referer}i] cookie[%{cookie}i] idc[%{x_fx_idc}i] cookie[%{cookie}x] method[%{http_method}x] version[%{http_version}x] httpStatus[%{http_status}x] sendBytes[%{send_bytes}x] %S %M';
    const DEFAULT_FORMAT_STD = '%L: %{%m-%d %H:%M:%S}t %{app}x * %{pid}x [logid=%l filename=%f lineno=%N errno=%{err_no}x %{encoded_str_array}x errmsg=%{u_err_msg}x]';

    public static $arrLogLevels = array(
        self::LOG_LEVEL_FATAL => 'FATAL',
        self::LOG_LEVEL_WARNING => 'WARNING',
        self::LOG_LEVEL_NOTICE => 'NOTICE',
        self::LOG_LEVEL_TRACE => 'TRACE',
        self::LOG_LEVEL_DEBUG => 'DEBUG',
    );

    private static $_arrInstance = array();
    private static $_strLogPath = null;
    private static $_strDataPath = null;
    public static $currentInstance;

    public $currentLogLevel;
    public $currentArgs;
    public $currentErrNo;
    public $currentErrMsg;
    public $currentFile;
    public $currentLine;
    public $currentFunction;
    public $currentClass;
    public $currentFunctionParam;

    protected $intLevel;
    protected $strLogFile;
    protected $bolAutoRotate;
    protected $strFormat;
    protected $strFormatWF;
    //protected $strFormatPB;
    protected $addNotice = array();
    protected $pbAddNotice = array();
    protected $objWriter = null;

    //    protected $confPblog = array();

    private function __construct($arrLogConfig) {
        $this->intLevel = $arrLogConfig['level'];
        $this->bolAutoRotate = $arrLogConfig['auto_rotate'];
        $this->strLogFile = $arrLogConfig['log_file'];
        $this->strFormat = $arrLogConfig['format'];
        $this->strFormatWF = $arrLogConfig['format_wf'];
        //$this->strFormatPB = $arrLogConfig['format_pb'];
    }

    /**
     * getInstance
     * 获取指定App的log对象，默认为当前App
     * @param mixed $app
     * @param mixed $logType
     * @return object
     */
    public static function getInstance($app = null, $logType = null) {

        empty($app) && $app = self::getLogPrefix();

        if (empty(self::$_arrInstance[$app])) {
            $logConf = Conf::getConf('log');

            // 生成路径
            $logPath = self::getLogPath();
            if ($logConf['use_sub_dir'] == '1') {
                if (!is_dir($logPath . "/$app")) {
                    @mkdir($logPath . "/$app");
                }
                $logFile = $logPath . "/$app/$app.log";
            } else {
                $logFile = $logPath . "/$app.log";
            }

            // 用于ut测试打印日志
            if ($logType == "stf") {
                $logDir = dirname($logFile) . "/" . $logType . "/";
                if (!file_exists($logDir)) {
                    @mkdir($logDir);
                }
                $logFile = $logDir . $app . "_" . $logType . ".log";
            }

            //get log format
            if (isset($logConf['format'])) {
                $format = $logConf['format'];
            } else {
                $format = self::DEFAULT_FORMAT;
            }

            if (isset($logConf['format_wf'])) {
                $formatWf = $logConf['format_wf'];
            } else {
                $formatWf = $format;
            }

            //pb_format
            /*if (isset($logConf['format_pb'])) {
                $formatPb = $logConf['format_pb'];
            } else {
                $formatPb = self::DEFAULT_FORMAT_PB;
            }*/

            // get conf
            $conf = array(
                'level' => intval($logConf['level']),
                'auto_rotate' => ($logConf['auto_rotate'] == '1'),
                'log_file' => $logFile,
                'format' => $format,
                'format_wf' => $formatWf,
                //'format_pb' => $formatPb,
            );

            self::$_arrInstance[$app] = new Log($conf);
        }

        return self::$_arrInstance[$app];
    }

    /**
     * log前缀
     * @return  string
     **/
    public static function getLogPrefix() {
        if (defined('IS_FOX') && IS_FOX == true) {
            return AppEnv::getCurrApp();
        }
        if (defined('MODULE')) {
            return MODULE;
        }
        return 'unknow';
    }

    /**
     * 日志打印的根目录
     * @return  string
     **/
    public static function getLogPath() {
        if (defined('IS_FOX') && IS_FOX == true) {
            return LOG_PATH;
        }
        if (self::$_strLogPath === null) {
            $ret = Conf::getConf('log.log_path');
            if ($ret) {
                self::$_strLogPath = $ret;
            } else {
                self::$_strLogPath = './';
            }
        }
        return self::$_strLogPath;

    }

    /**
     * 日志库依赖的数据文件根目录
     * @return mixed|null|string
     */
    public static function getDataPath() {
        if (defined('IS_FOX') && IS_FOX == true) {
            return DATA_PATH;
        }
        if (self::$_strDataPath === null) {
            $ret = Conf::getConf('log.data_path');
            if ($ret) {
                self::$_strDataPath = $ret;
            } else {
                self::$_strDataPath = "./";
            }
        }
        return self::$_strDataPath;
    }

    public static function genLogID() {
        return REQUEST_ID;
    }

    public function getLogString($format) {
        $md5val = md5($format);
        $func = "fx_log_$md5val";
        if (function_exists($func)) {
            return $func();
        }
        $dataPath = self::getDataPath();
        $fileName = $dataPath . '/log/' . $md5val . '.php';
        if (!file_exists($fileName)) {
            $tmpFilename = $fileName . '.' . posix_getpid() . '.' . rand();
            if (!is_dir($dataPath . '/log')) {
                @mkdir($dataPath . '/log');
            }
            file_put_contents($tmpFilename, $this->parseFormat($format));
            rename($tmpFilename, $fileName);
        }
        include_once $fileName;
        $str = $func();

        return $str;
    }

    // 获取客户端ip
    public static function getClientIp() {
        return Ip::getClientIp();
    }

    public static function flattenArgs($args) {
        if (!is_array($args)) {
            return '';
        }
        $str = array();
        foreach ($args as $a) {
            $str[] = preg_replace('/[ \n\t]+/', " ", $a);
        }
        return implode(', ', $str);
    }

    public static function addNotice($key, $value) {
        $log = self::getInstance();

        if (!isset($value)) {
            $value = $key;
            $key = '@';
        }

        $info = is_array($value) ? strtr(strtr(var_export($value, true), array(
                "  array (\n" => '{',
                "array (\n" => '{',
                ' => ' => ':',
                ",\n" => ',',
            )), array(
                '{  ' => '{',
                ":\n{" => ':{',
                '  ),  ' => '},',
                '),' => '},',
                ',)' => '}',
                ',  ' => ',',
            )) : $value;
        $log->addNotice[$key] = $info;
    }

    public function getStrArgs() {
        $strArgs = '';
        foreach ($this->currentArgs as $k => &$v) {
            if (is_array($v) || is_object($v)) {
                $v = serialize($v);
            }
            $strArgs .= ' ' . $k . '[' . $v . ']';
        }
        return $strArgs;
    }

    public function getStrArgsStd() {
        $args = array();
        foreach ($this->currentArgs as $k => &$v) {
            if (is_array($v) || is_object($v)) {
                $v = serialize($v);
            }
            $args[] = rawurlencode(json_encode($k)) . '=' . rawurlencode(json_encode($v));
        }
        return implode(' ', $args);
    }

    public function parseFormat($format) {
        $matches = array();
        $regex = '/%(?:{([^}]*)})?(.)/';
        preg_match_all($regex, $format, $matches);
        $prelim = array();
        $action = array();
        $prelimDone = array();

        $len = count($matches[0]);
        for ($i = 0; $i < $len; $i++) {
            $code = $matches[2][$i];
            $param = $matches[1][$i];
            switch ($code) {
                case 'h':
                    $action[] = "(defined('CLIENT_IP')? CLIENT_IP : \Fx\Log::getClientIp())";
                    break;
                case 't':
                    $action[] = ($param == '') ? "strftime('%y-%m-%d %H:%M:%S')" : "strftime(" . var_export($param, true) . ")";
                    break;
                case 'i':
                    $key = 'HTTP_' . str_replace('-', '_', strtoupper($param));
                    $key = var_export($key, true);
                    $action[] = "(isset(\$_SERVER[$key])? \$_SERVER[$key] : '')";
                    break;
                case 'a':
                    $action[] = "(defined('CLIENT_IP')? CLIENT_IP : \Fx\Log::getClientIp())";
                    break;
                case 'A':
                    $action[] = "(isset(\$_SERVER['SERVER_ADDR'])? \$_SERVER['SERVER_ADDR'] : '')";
                    break;
                case 'C':
                    if ($param == '') {
                        $action[] = "(isset(\$_SERVER['HTTP_COOKIE'])? \$_SERVER['HTTP_COOKIE'] : '')";
                    } else {
                        $param = var_export($param, true);
                        $action[] = "(isset(\$_COOKIE[$param])? \$_COOKIE[$param] : '')";
                    }
                    break;
                case 'D':
                    $action[] = "(defined('REQUEST_TIME_US')? (microtime(true) * 1000 - REQUEST_TIME_US/1000) : '')";
                    break;
                case 'e':
                    $param = var_export($param, true);
                    $action[] = "((getenv($param) !== false)? getenv($param) : '')";
                    break;
                case 'f':
                    $action[] = '\Fx\Log::$currentInstance->currentFile';
                    break;
                case 'H':
                    $action[] = "(isset(\$_SERVER['SERVER_PROTOCOL'])? \$_SERVER['SERVER_PROTOCOL'] : '')";
                    break;
                case 'm':
                    $action[] = "(isset(\$_SERVER['REQUEST_METHOD'])? \$_SERVER['REQUEST_METHOD'] : '')";
                    break;
                case 'p':
                    $action[] = "(isset(\$_SERVER['SERVER_PORT'])? \$_SERVER['SERVER_PORT'] : '')";
                    break;
                case 'q':
                    $action[] = "(isset(\$_SERVER['QUERY_STRING'])? \$_SERVER['QUERY_STRING'] : '')";
                    break;
                case 'T':
                    switch ($param) {
                        case 'ms':
                            $action[] = "(defined('REQUEST_TIME_US')? (microtime(true) * 1000 - REQUEST_TIME_US/1000) : '')";
                            break;
                        case 'us':
                            $action[] = "(defined('REQUEST_TIME_US')? (microtime(true) * 1000000 - REQUEST_TIME_US) : '')";
                            break;
                        default:
                            $action[] = "(defined('REQUEST_TIME_US')? (microtime(true) - REQUEST_TIME_US/1000000) : '')";
                    }
                    break;
                case 'U':
                    $action[] = "(isset(\$_SERVER['REQUEST_URI'])? \$_SERVER['REQUEST_URI'] : '')";
                    break;
                case 'v':
                    $action[] = "(isset(\$_SERVER['HOSTNAME'])? \$_SERVER['HOSTNAME'] : '')";
                    break;
                case 'V':
                    $action[] = "(isset(\$_SERVER['HTTP_HOST'])? \$_SERVER['HTTP_HOST'] : '')";
                    break;
                case 'L':
                    $action[] = '\Fx\Log::$currentInstance->currentLogLevel';
                    break;
                case 'N':
                    $action[] = '\Fx\Log::$currentInstance->currentLine';
                    break;
                case 'E':
                    $action[] = '\Fx\Log::$currentInstance->currentErrNo';
                    break;
                case 'l':
                    $action[] = "\Fx\Log::genLogID()";
                    break;
                case 'u':
                    /*if (!isset($prelimDone['user'])) {
                        $prelim[] = '$____user____ = Sf_Passport::getUserInfoFromCookie();';
                        $prelimDone['user'] = true;
                    }
                    $action[] = "((defined('CLIENT_IP') ? CLIENT_IP: Sf_Log::getClientIp()) . ' ' . \$____user____['uid'] . ' ' . \$____user____['uname'])";*/
                    $action[] = "defined('CLIENT_IP') ? CLIENT_IP: \Fx\Log::getClientIp()";
                    break;
                case 'S':
                    if ($param == '') {
                        $action[] = '\Fx\Log::$currentInstance->getStrArgs()';
                    } else {
                        $paramName = var_export($param, true);
                        if (!isset($prelimDone['S_' . $paramName])) {
                            $prelim[] = "if (isset(\Fx\Log::\$currentInstance->currentArgs[$paramName])) {
                                    \$____curargs____[$paramName] = \Fx\Log::\$currentInstance->currentArgs[$paramName];
                                    unset(\Fx\Log::\$currentInstance->currentArgs[$paramName]);
                                } else \$____curargs____[$paramName] = '';";
                            $prelimDone['S_' . $paramName] = true;
                        }
                        $action[] = "\$____curargs____[$paramName]";
                    }
                    break;
                case 'M':
                    $action[] = '\Fx\Log::$currentInstance->currentErrMsg';
                    break;
                case 'x':
                    $needUrlencode = false;
                    if (substr($param, 0, 2) == 'u_') {
                        $needUrlencode = true;
                        $param = substr($param, 2);
                    }
                    switch ($param) {
                        case 'log_level':
                            $action[] = '\Fx\Log::$currentInstance->currentLogLevel';
                            break;
                        case 'line':
                            $action[] = '\Fx\Log::$currentInstance->currentLine';
                            break;
                        case 'class':
                            $action[] = '\Fx\Log::$currentInstance->currentClass';
                            break;
                        case 'function':
                            $action[] = '\Fx\Log::$currentInstance->currentFunction';
                            break;
                        case 'err_no':
                            $action[] = '\Fx\Log::$currentInstance->currentErrNo';
                            break;
                        case 'err_msg':
                            $action[] = '\Fx\Log::$currentInstance->currentErrMsg';
                            break;
                        case 'log_id':
                            $action[] = "\Fx\Log::genLogID()";
                            break;
                        case 'app':
                            $action[] = "\Fx\Log::getLogPrefix()";
                            break;
                        case 'function_param':
                            $action[] = '\Fx\Log::flattenArgs(\Fx\Log::$currentInstance->currentFunctionParam)';
                            break;
                        case 'argv':
                            $action[] = '(isset($GLOBALS["argv"])? \Fx\Log::flattenArgs($GLOBALS["argv"]) : \'\')';
                            break;
                        case 'pid':
                            $action[] = 'posix_getpid()';
                            break;
                        case 'encoded_str_array':
                            $action[] = '\Fx\Log::$currentInstance->getStrArgsStd()';
                            break;
                        case 'cookie':
                            $action[] = "(isset(\$_SERVER['HTTP_COOKIE'])? \$_SERVER['HTTP_COOKIE'] : '')";
                            break;
                        case 'http_method':
                            $action[] = isset($_SERVER['REQUEST_METHOD'])? $_SERVER['REQUEST_METHOD'] : '';
                            break;
                        case 'http_version':
                            $action[] = isset($_SERVER['SERVER_PROTOCOL'])? $_SERVER['SERVER_PROTOCOL'] : '';
                            break;
                        case 'http_status':
                            $action[] = http_response_code();
                            break;
                        case 'send_bytes':
                            $action[] = ob_get_length();
                            break;
                        default:
                            $action[] = "''";
                    }
                    if ($needUrlencode) {
                        $actionLen = count($action);
                        $action[$actionLen - 1] = 'rawurlencode(' . $action[$actionLen - 1] . ')';
                    }
                    break;
                case '%':
                    $action[] = "'%'";
                    break;
                default:
                    $action[] = "''";
            }
        }

        $strformat = preg_split($regex, $format);
        $code = var_export($strformat[0], true);
        for ($i = 1; $i < count($strformat); $i++) {
            $code = $code . ' . ' . $action[$i - 1] . ' . ' . var_export($strformat[$i], true);
        }
        $code .= ' . "\n"';
        $pre = implode("\n", $prelim);

        $cmt = "Used for app " . self::getLogPrefix() . "\n";
        $cmt .= "Original format string: " . str_replace('*/', '* /', $format);

        $md5val = md5($format);
        $func = "fx_log_$md5val";
        $str = "<?php \n/*\n$cmt\n*/\nfunction $func() {\n$pre\nreturn $code;\n}";
        return $str;
    }

    /**
     * debug
     *
     * @param mixed $str
     * @param int $errno
     * @param mixed $arrArgs
     * @param int $depth
     * @return mixed
     */
    public static function debug($str, $errno = 0, $arrArgs = null, $depth = 0) {
        return self::getInstance()->_writeLog(self::LOG_LEVEL_DEBUG, $str, $errno, $arrArgs, $depth + 1);
    }

    /**
     * trace
     * @param $str
     * @param int $errno
     * @param null $arrArgs
     * @param int $depth
     * @return mixed
     */
    public static function trace($str, $errno = 0, $arrArgs = null, $depth = 0) {
        return self::getInstance()->_writeLog(self::LOG_LEVEL_TRACE, $str, $errno, $arrArgs, $depth + 1);
    }

    /**
     * notice
     * @param $str
     * @param int $errno
     * @param null $arrArgs
     * @param int $depth
     */
    public static function notice($str, $errno = 0, $arrArgs = null, $depth = 0) {
        self::getInstance()->_writeLog(self::LOG_LEVEL_NOTICE, $str, $errno, $arrArgs, $depth + 1);
    }

    /**
     * warning
     * @param $str
     * @param int $errno
     * @param null $arrArgs
     * @param int $depth
     * @return mixed
     */
    public static function warning($str, $errno = 0, $arrArgs = null, $depth = 0) {
        return self::getInstance()->_writeLog(self::LOG_LEVEL_WARNING, $str, $errno, $arrArgs, $depth + 1);
    }

    /**
     * fatal
     * @param $str
     * @param int $errno
     * @param null $arrArgs
     * @param int $depth
     * @return mixed
     */
    public static function fatal($str, $errno = 0, $arrArgs = null, $depth = 0) {
        return self::getInstance()->_writeLog(self::LOG_LEVEL_FATAL, $str, $errno, $arrArgs, $depth + 1);
    }

    public function _writeLog($intLevel, $str, $errno = 0, $arrArgs = null, $depth = 0, $filenameSuffix = '', $logFormat = null) {
        if ($intLevel > $this->intLevel || !isset(self::$arrLogLevels[$intLevel])) {
            return false;
        }

        //log file name
        $strLogFile = $this->strLogFile;
        if (($intLevel & self::LOG_LEVEL_WARNING) || ($intLevel & self::LOG_LEVEL_FATAL)) {
            $strLogFile .= '.wf';
        } else if (($intLevel & self::LOG_LEVEL_DEBUG) || ($intLevel & self::LOG_LEVEL_TRACE)) {
            $strLogFile .= '.dt';
        }

        $strLogFile .= $filenameSuffix;

        $this->_setCurLog($intLevel, $str, $errno, $arrArgs, $depth, $filenameSuffix, $logFormat);

        //get the format
        if ($logFormat === null) {
            $format = $this->_getFormat($intLevel);
        } else {
            $format = $logFormat;
        }
        $str = $this->getLogString($format);

        // 日志文件加上年月日配置
        if ($this->bolAutoRotate) {
            $strLogFile .= '.' . date('YmdH');
        }

        return file_put_contents($strLogFile, $str, FILE_APPEND);
    }

    /**
     * 设置某些log信息
     * @param $intLevel
     * @param $str
     * @param int $errno
     * @param null $arrArgs
     * @param int $depth
     * @param string $filenameSuffix
     * @param null $logFormat
     */
    private function _setCurLog($intLevel, $str, $errno = 0, $arrArgs = null, $depth = 0, $filenameSuffix = '', $logFormat = null) {
        //assign data required
        $this->currentLogLevel = self::$arrLogLevels[$intLevel];

        //build array for use as strargs
        $_arrArgs = false;
        $_addNotice = false;
        if (is_array($arrArgs) && count($arrArgs) > 0) {
            $_arrArgs = true;
        }

        if (($intLevel & self::LOG_LEVEL_NOTICE) && !empty($this->addNotice)) {
            $_addNotice = true;
        }

        if ($_arrArgs && $_addNotice) { //both are defined, merge
            $this->currentArgs = $arrArgs + $this->addNotice;
        } else if (!$_arrArgs && $_addNotice) { //only add notice
            $this->currentArgs = $this->addNotice;
        } else if ($_arrArgs && !$_addNotice) { //only arr args
            $this->currentArgs = $arrArgs;
        } else { //empty
            $this->currentArgs = array();
        }

        $this->currentErrNo = $errno;
        $this->currentErrMsg = $str;

        // 不调用 args，减少内存消耗
        if (defined('DEBUG_BACKTRACE_IGNORE_ARGS')) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $depth + 2);
        } else {
            $trace = debug_backtrace();
        }

        $depth2 = $depth + 1;
        if ($depth >= count($trace)) {
            $depth = count($trace) - 1;
            $depth2 = $depth;
        }

        $this->currentFile = isset($trace[$depth2]['file']) ? $trace[$depth2]['file'] : "";
        $this->currentLine = isset($trace[$depth2]['line']) ? $trace[$depth2]['line'] : "";
        $this->currentFunction = isset($trace[$depth2]['function']) ? $trace[$depth2]['function'] : "";
        $this->currentClass = isset($trace[$depth2]['class']) ? $trace[$depth2]['class'] : "";
        $this->currentFunctionParam = isset($trace[$depth2]['args']) ? $trace[$depth2]['args'] : "";

        self::$currentInstance = $this;
    }

    // 获取日志输出格式
    private function _getFormat($level) {
        if ($level == self::LOG_LEVEL_FATAL || $level == self::LOG_LEVEL_WARNING) {
            $fmtstr = $this->strFormatWF;
        } else {
            $fmtstr = $this->strFormat;
        }
        return $fmtstr;
    }
}