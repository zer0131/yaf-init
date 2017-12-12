<?php
/**
 * @author ryan
 * @desc cli controller
 */

class Controller_Cli extends \Yaf\Controller_Abstract {

    public $actions = array(
        'script' => 'actions/cli/Script.php',
    );

    public function init() {
        if (!IS_CLI) {
            exit(1);
        }
    }
}