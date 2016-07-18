<?php
/**
 * 添加 access token 的表字段。意义在于
 */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('weChat/access_token')};
CREATE TABLE {$this->getTable('weChat/access_token')}
(
   `id` int(10) unsigned not null,
    `access_token` varchar (200) NOT NULL default 'Access Token',
   `created_time` datetime DEFAULT NULL COMMENT 'create Time',
   `update_time` datetime DEFAULT NULL COMMENT 'update Time',
    PRIMARY KEY (`id`)
) ENGINE = MEMORY DEFAULT CHARSET = utf8;
");

$installer->endSetup();
