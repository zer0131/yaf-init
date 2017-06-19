<?php
/**
 * @author ryan
 * @desc script action
 */

class Action_Script extends Yaf_Action_Abstract {
    public function execute() {
        dumper(IS_CLI);
    }
}