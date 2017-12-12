<?php
/**
 * @author ryan
 * @desc page index
 */

class Service_Page_Index_Index {
    use \Fx\Traits\Instance;
    public function testPage() {
        return Service_Data_Index_Index::getInstance()->testData();
    }
}