<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @搜索词库匹配表
 * @author 周进
 */
class Model_Directory extends ORM {

    /**
     * 表名称
     */
    protected $_table_name = "directory";

    /**
     * 主键名称
     */
    protected $_primary_key = 'id';

    /**
     * 数据库连接配置
     */
    protected $_db_group = 'default';

} //End Industry Model
