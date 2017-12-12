<?php
/**
 * @author ryan
 * @desc data index
 */

class Service_Data_Index_Index {
    use \Fx\Traits\Instance;
    public function testData() {
        return Dao_Index_Index::getInstance()->getData();
    }
}