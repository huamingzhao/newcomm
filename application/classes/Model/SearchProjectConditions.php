<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * 搜索项目条件记录
 * @author 钟涛
 *
 */
class Model_SearchProjectConditions extends ORM{
    /**
    * 表名称
    */
    protected $_table_name = "searchproject_conditions";

    /**
    * 主键名称
    */
    protected $_primary_key = 'id';

    /**
    * 数据库连接配置
    */
    protected $_db_group = 'default';

}