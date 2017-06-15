<?php
/**
 * @author ryan
 * @desc 默认action，执行方法是固定的
 */

class Action_Index extends Yaf_Action_Abstract {

    public function execute() {
        dumper('index');
    }
}