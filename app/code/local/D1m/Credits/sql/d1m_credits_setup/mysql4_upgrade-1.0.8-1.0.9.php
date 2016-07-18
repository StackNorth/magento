<?php
/**
 * Created by PhpStorm.
 * User: d1m
 * Date: 2016/7/15
 * Time: 16:25
 */
$installer = $this;
$installer->run("
DROP TABLE IF EXISTS `aca_credits_test`;
CREATE TABLE `aca_credits_test` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`status`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`content`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`title`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`time`  datetime NULL DEFAULT NULL ,
`email`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=53
ROW_FORMAT=COMPACT
;");