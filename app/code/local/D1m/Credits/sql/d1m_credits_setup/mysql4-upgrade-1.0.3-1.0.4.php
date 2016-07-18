<?php
$installer = $this;

$installer->run("ALTER TABLE  `aca_credits_order` ADD  `order_from` varchar(20)   NULL DEFAULT  'web',
  ADD  `order_type` varchar(20)   NULL DEFAULT  'buy',
    ADD  `order_trench` varchar(100)   NULL DEFAULT  '' ;
");
