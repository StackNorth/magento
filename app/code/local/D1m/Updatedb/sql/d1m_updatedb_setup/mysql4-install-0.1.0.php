<?php
$installer = $this;
$installer->startSetup();
try
{
    $installer->run("
DROP TABLE IF EXISTS {$this->getTable('d1m_sms_count')};
CREATE TABLE {$this->getTable('d1m_sms_count')}
(
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sphone` varchar(30) DEFAULT NULL,
  `sdate` date DEFAULT NULL,
  `scount` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone_date` (`sphone`,`sdate`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;

");
}
catch (Exception $e)
{
    Mage::logException($e);
}

$installer->endSetup();
?>
