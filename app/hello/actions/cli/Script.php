<?php
/**
 * @author ryan
 * @desc script action
 */

class Action_Script extends \Yaf\Action_Abstract {
    public function execute() {
        dumper(IS_CLI);
    }
}