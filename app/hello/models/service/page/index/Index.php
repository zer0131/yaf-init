<?php
/**
 * @author ryan
 * @desc page index
 */

class Service_Page_Index_Index  extends Fx_Base {
    public function testPage() {
        return Service_Data_Index_Index::instance()->testData();
    }
}