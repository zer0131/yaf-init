<?php
/**
 * @author ryan
 * @desc pcntl示例
 */

require_once __DIR__ . "/../../../lib/fx/Init.php";
$app = Fx_Init::init('hello');
$app->execute('main');

function main() {
    echo '主进程pid：'.posix_getpid().PHP_EOL;
    $max = 6;
    $runNum = 0;
    $childArr = array();//子进程数组
    while (true) {
        if ($runNum < $max) {
            $runNum++;
            $pid = pcntl_fork();
            if ($pid == 0) {
                echo '子进程逻辑['.posix_getpid().']'.PHP_EOL;
                sleep(15);
                exit(0);//这里一定要exit掉
            } elseif ($pid > 0) {
                $childArr[$pid] = $pid;
                echo '主进程逻辑,子进程pid：'.$pid.PHP_EOL;
            } else {
                //创建子进程失败
            }
        }
        if ($runNum > 0) {
            $pids = array_keys($childArr);
            foreach ($pids as $pid) {
                //获取子进程的状态
                $ret = pcntl_waitpid($pid, $status, WNOHANG);
                if ($ret == -1) {
                    echo '错误'.PHP_EOL;
                    unset($childArr[$pid]);
                    $runNum--;
                } elseif ($ret == 0) {
                    echo "子进程正在执行[$pid]...".PHP_EOL;
                } else {
                    //exit
                    if (pcntl_wtermsig($status) == SIGKILL) {
                        echo "子进程执行错误[$status]".PHP_EOL;
                    }
                    elseif (pcntl_wexitstatus($status) != 0) {
                        echo "子进程执行错误[$status]".PHP_EOL;
                    }
                    else {
                        echo "子进程执行成功[$pid]".PHP_EOL;
                    }
                    unset($childArr[$pid]);
                    $runNum--;
                }
            }
        }
        sleep(2);
    }
}
