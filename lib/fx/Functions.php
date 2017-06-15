<?php
/**
 * @author ryan
 * @desc 公共函数
 */

/**
 * 改进后的变量输出
 * @param mixed $var 变量
 * @param bool $echo 是否返回输出
 * @return string|null
 */
if (!function_exists('dumper')) {
    function dumper($var, $echo = true) {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
        if ('cli' == PHP_SAPI) {
            $output = PHP_EOL . $output . PHP_EOL;
        } else {
            $output = '<pre style="margin:10px;padding:30px;font-size: 16px;background: #f2f2f2;border-radius:10px 10px;overflow: auto">' . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
        if (!$echo) {
            return $output;
        }
        echo $output;
        return null;
    }
}