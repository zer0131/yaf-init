<?php
/**
 * @author ryan
 * @desc 默认控制器
 */

class Controller_Index extends Yaf_Controller_Abstract {

    public $actions = array(
        'index' => 'actions/Index.php',
        'test' => 'actions/api/Test.php',
    );

}