<?php
/**
 * User: ahsw@qq.com
 * caeate Time: 2016/7/714:42
*/

/**
 * 添加 access token 的表字段。意义在于
 */

$installer = $this;
$installer->run("ALTER TABLE  `{$this->getTable('sales/order')}`  ADD  `sand_card_number` varchar(30)     NULL DEFAULT  '';");
$installer->run("ALTER TABLE  `{$this->getTable('d1m_credits/order')}` ADD `sand_card_number` varchar(30)     NULL DEFAULT  '';");
$installer->endSetup();
