<?php

/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.com)
 * @Copyright (C) 2018 mynukeviet. All rights reserved
 * @Createdate Mon, 12 Feb 2018 07:01:21 GMT
 */
if (!defined('NV_IS_FILE_MODULES')) die('Stop!!!');

$sql_drop_module = array();
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data;
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_salary";

$sql_create_module = $sql_drop_module;
$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "(
  id smallint(4) NOT NULL AUTO_INCREMENT,
  partid smallint(4) unsigned NOT NULL COMMENT 'Thuộc bộ phận',
  jobtitleid smallint(4) unsigned NOT NULL,
  userid mediumint(8) unsigned NOT NULL,
  first_name varchar(100) NOT NULL,
  last_name varchar(50) NOT NULL,
  gender tinyint(1) unsigned NOT NULL DEFAULT '1',
  birthday int(11) unsigned NOT NULL DEFAULT '0',
  main_phone varchar(20) NOT NULL,
  other_phone varchar(255) NOT NULL,
  main_email varchar(100) NOT NULL,
  other_email varchar(255) NOT NULL,
  address varchar(255) NOT NULL,
  knowledge text NOT NULL COMMENT 'Thông tin học vấn',
  image varchar(255) NOT NULL,
  jointime int(11) unsigned NOT NULL DEFAULT '0',
  salary double unsigned NOT NULL DEFAULT '0',
  allowance double unsigned NOT NULL DEFAULT '0',
  addtime int(11) unsigned NOT NULL,
  edittime int(11) unsigned NOT NULL DEFAULT '0',
  useradd mediumint(8) unsigned NOT NULL,
  status tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $lang . "_" . $module_data . "_salary(
  userid mediumint(8) unsigned NOT NULL,
  salary double unsigned NOT NULL,
  allowance double unsigned NOT NULL DEFAULT '0' COMMENT 'Phụ cấp',
  workday double unsigned NOT NULL COMMENT 'Số ngày công',
  overtime double unsigned NOT NULL COMMENT 'Số ngày làm thêm',
  advance double unsigned NOT NULL DEFAULT '0' COMMENT 'Tạm ứng',
  bonus double unsigned NOT NULL DEFAULT '0' COMMENT 'Thưởng',
  total double unsigned NOT NULL COMMENT 'Tổng',
  deduction double unsigned NOT NULL DEFAULT '0' COMMENT 'Tổng các khoản trừ',
  received double NOT NULL COMMENT 'Thực nhận',
  time varchar(10) NOT NULL,
  UNIQUE KEY userid (userid,time)
) ENGINE=MyISAM";

$data = array();
$array_config['groups_admin'] = '1';
$array_config['groups_use'] = '1';

foreach ($data as $config_name => $config_value) {
    $sql_create_module[] = "INSERT INTO " . NV_CONFIG_GLOBALTABLE . " (lang, module, config_name, config_value) VALUES ('" . $lang . "', " . $db->quote($module_name) . ", " . $db->quote($config_name) . ", " . $db->quote($config_value) . ")";
}