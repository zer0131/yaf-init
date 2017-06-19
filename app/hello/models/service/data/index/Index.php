<?php
/**
 * @author ryan
 * @desc data index
 */

class Service_Data_Index_Index extends Fx_Base {
    public function testData() {
        return Dao_Index_Index::instance()->getData();
    }
}