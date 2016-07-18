<?php
/* @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$i = 0;
$slide = Mage::getModel('interaktingslider/slide');
$data[] = array(
	'name' => 'Slide 1',
	'content' => '<div><a class="slider slide-eqiq" href="javascript:void(0);"><img src="{{skin_url=\'images/eqiq-slide.png\'}}" alt="banner" width="959px" height="318px" /></a>
</div>',
	'from-time' => null,
	'to-time' => null,
	'is-active' => 1,
	'stores' => array(0,1,2),
	'group' => 'eqiq',
	);
$data[] = array(
	'name' => 'Slide 2',
	'content' => '<div><a class="slider slide-eqiq" href="javascript:void(0);"><img src="{{skin_url=\'images/eqiq-slide.png\'}}" alt="banner" width="959px" height="318px" /></a>
</div>',
	'from-time' => null,
	'to-time' => null,
	'is-active' => 1,
	'stores' => array(0,1,2),
	'group' => 'eqiq',
	);
$data[] = array(
	'name' => 'Slide 3',
	'content' => '<div><a class="slider slide-eqiq" href="javascript:void(0);"><img src="{{skin_url=\'images/eqiq-slide.png\'}}" alt="banner" width="959px" height="318px" /></a>
</div>',
	'from-time' => null,
	'to-time' => null,
	'is-active' => 1,
	'stores' => array(0,1,2),
	'group' => 'eqiq',
	);
$data[] = array(
	'name' => 'Slide 4',
	'content' => '<div><a class="slider slide-eqiq" href="javascript:void(0);"><img src="{{skin_url=\'images/eqiq-slide.png\'}}" alt="banner" width="959px" height="318px" /></a>
</div>',
	'from-time' => null,
	'to-time' => null,
	'is-active' => 1,
	'stores' => array(0,1,2),
	'group' => 'eqiq',
	);
$data[] = array(
	'name' => 'Slide 1',
	'content' => '<div><a class="slider-sample" href="javascript:void(0);"><img src="{{skin_url=\'images/stevemadden_img.png\'}}" alt="stevemadden_img" width="959" height="553" /></a></div>',
	'from-time' => null,
	'to-time' => null,
	'is-active' => 1,
	'stores' => array(0,1,2),
	'group' => 'stevemadden',
	);
$data[] = array(
	'name' => 'Slide 2',
	'content' => '<div><a class="slider-sample" href="javascript:void(0);"><img src="{{skin_url=\'images/stevemadden_img.png\'}}" alt="stevemadden_img" width="959" height="553" /></a></div>',
	'from-time' => null,
	'to-time' => null,
	'is-active' => 1,
	'stores' => array(0,1,2),
	'group' => 'stevemadden',
	);
$data[] = array(
	'name' => 'Slide 3',
	'content' => '<div><a class="slider-sample" href="javascript:void(0);"><img src="{{skin_url=\'images/stevemadden_img.png\'}}" alt="stevemadden_img" width="959" height="553" /></a></div>',
	'from-time' => null,
	'to-time' => null,
	'is-active' => 1,
	'stores' => array(0,1,2),
	'group' => 'stevemadden',
	);

foreach ($data as $v) {
	$slide = Mage::getModel('interaktingslider/slide');
	foreach($v as $k=>$subv) {
		$slide->setData($k,$subv);
	}
	$slide->save();
}
$installer->endSetup();
