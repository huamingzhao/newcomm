<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * 企业诚信类型
 * @author 龚湧
 *
 */
class Model_IntegrityType extends ORM{
    /**
    * 表名称
    */
    protected $_table_name = "integrity_type";

    /**
    * 主键名称
    */
    protected $_primary_key = 'id';

    /**
    * 数据库连接配置
    */
    protected $_db_group = 'default';

}