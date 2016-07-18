<?php
/* @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$i = 0;
$slide = Mage::getModel('interaktingslider/slide');
$data[] = array(
	'name' => 'Shop By Look1',
	'content' => '<div><a class="shopbylook-slider" href="javascript:void(0);"><img src="{{skin_url=\'images/look-slider1.jpg\'}}" alt="banner" width="960" height="350" /></a>
<div class="caption">
<p>Look Slide 1</p>
</div>
</div>',
	'from-time' => '2012-10-02 09:59:50',
	'to-time' => null,
	'is-active' => 1,
	'stores' => array(0,1,2),
	'group' => 'shopbylook',
	);
$data[] = array(
	'name' => 'Shop By Look2',
	'content' => '<div><a class="shopbylook-slider" href="javascript:void(0);"><img src="{{skin_url=\'images/look-slider2.jpg\'}}" alt="banner" width="960" height="268" /></a>
	<div class="caption">
	<p>Look Slide 2</p>
	</div>
	</div>',
	'from-time' => '2012-10-02 09:59:50',
	'to-time' => null,
	'is-active' => 1,
	'stores' => array(0,1,2),
	'group' => 'shopbylook',

	);

foreach ($data as $v) {
	$slide = Mage::getModel('interaktingslider/slide');
	foreach($v as $k=>$subv) {
		$slide->setData($k,$subv);
	}
	$slide->save();
}
$installer->endSetup();
