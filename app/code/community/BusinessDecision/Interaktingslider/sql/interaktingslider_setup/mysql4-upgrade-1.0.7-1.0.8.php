<?php
/* @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$i = 0;
foreach (Mage::getModel('interaktingslider/slide')->getCollection() as $slide) {
    $slide->load(NULL);
    $slide->setContent('<div><a class="slider-sample" href="javascript:void(0);"><img src="{{skin_url=\'images/slider_sample.jpg\'}}" alt="banner" width="960" height="445" /></a>
<div class="caption">
<p>Slide ' . (++$i) . '</p>
</div>
</div>')->save();
}

$installer->endSetup();
