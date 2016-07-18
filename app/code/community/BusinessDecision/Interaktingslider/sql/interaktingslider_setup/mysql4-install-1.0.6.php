<?php
/**
* Interakting Slider
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@magentocommerce.com and you will be sent a copy immediately.
*
* @category   BusinessDecision
* @package    BusinessDecision_Interaktingslider
* @author     Business & Decision Picardie - contactmagento@interakting.com
* @copyright  Copyright (c) 2009 Business & Decision (http://www.businessdecision.com)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/ 

$installer = $this;

$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('interaktingslider_slide')}` (
  `slide_id` smallint(6) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `content` text,
  `from_time` datetime default NULL,
  `to_time` datetime default NULL,
  `is_active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`slide_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Interaktingslider Slides';
CREATE TABLE IF NOT EXISTS `{$this->getTable('interaktingslider_slide_position')}` (
  `slide_id` smallint(6) NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  `position` smallint(6) default NULL,
  PRIMARY KEY  (`slide_id`,`store_id`),
  KEY `FK_CAROUSEL_SLIDE_POSITION_STORE` (`store_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Interaktingslider Slides Position';
CREATE TABLE IF NOT EXISTS `{$this->getTable('interaktingslider_slide_store')}` (
  `slide_id` smallint(6) NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`slide_id`,`store_id`),
  KEY `FK_CAROUSEL_SLIDE_STORE_STORE` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Interaktingslider Slides to Stores';
ALTER TABLE `{$this->getTable('interaktingslider_slide_position')}`
ADD FOREIGN KEY (`slide_id`) REFERENCES `{$this->getTable('interaktingslider_slide')}` (`slide_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `{$this->getTable('interaktingslider_slide_store')}`
ADD FOREIGN KEY (`slide_id`) REFERENCES `{$this->getTable('interaktingslider_slide')}` (`slide_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;
INSERT INTO `{$this->getTable('interaktingslider_slide')}` (`slide_id`, `name`, `content`, `from_time`, `to_time`, `is_active`) VALUES(1, 'Diapo 1', 'Slide 1', '2009-11-06 04:46:36', NULL, 1),(2, 'Diapo 2', 'Slide 2', '2009-11-06 10:46:36', NULL, 1),
(4, 'Diapo 4', 'Slide 3', '2009-11-06 10:46:36', NULL, 1);
INSERT INTO `{$this->getTable('interaktingslider_slide_store')}` (`slide_id`, `store_id`) VALUES
(1, 0),
(2, 0),
(3, 0),
(4, 0);
");

$installer->endSetup();
?>