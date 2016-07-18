<?php
/**
 * Created by Victor Guo
 * Date: 13-8-15
 * Time: 上午9:59
 */

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('d1m_slide')};
CREATE TABLE {$this->getTable('d1m_slide')} (
  `slide_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` DATETIME NULL,
  `update_time` DATETIME NULL,
  PRIMARY KEY (`slide_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('d1m_slides_group')};
CREATE TABLE {$this->getTable('d1m_slides_group')} (
 `group_id` int(11) unsigned NOT NULL auto_increment,
 `group_name` varchar(255) NOT NULL default '',
 `slides_width` SMALLINT( 4 ) NOT NULL DEFAULT '0',
 `slides_height` SMALLINT( 4 ) NOT NULL DEFAULT '0',
 `status` smallint(6) NOT NULL default '0',
 `created_time` DATETIME NULL,
 `update_time` DATETIME NULL,
 PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('d1m_slide_group_mapping')};
CREATE TABLE {$this->getTable('d1m_slide_group_mapping')} (
 `mapping_id` int(11) unsigned NOT NULL auto_increment,
 `group_id` int(11) unsigned NOT NULL,
 `slide_id` int(11) unsigned NOT NULL,
 `sort_order` int(11) NOT NULL default '0',
 PRIMARY KEY (`mapping_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();