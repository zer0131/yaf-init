<?php
/**
 * @author ryan
 * @desc dao index 这里直接与数据库连接
 */

class Dao_Index_Index {
    use \Fx\Traits\Instance;
    public function getData() {
        return 'index';
}
}