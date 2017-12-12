<?php
/**
 * @author ryan
 * @desc 测试接口
 */

class Action_Test extends \Yaf\Action_Abstract {
    public function execute() {
        $ret = array(
            'errno' => 0,
            'errmsg' => 'success',
            'data' => array(
                'test' => 'fox'
            ),
        );
        header('content-type: application/json');
        echo json_encode($ret);
    }
}