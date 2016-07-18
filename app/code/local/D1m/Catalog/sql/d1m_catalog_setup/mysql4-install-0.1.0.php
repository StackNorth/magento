<?php
$installer = $this;
 // echo get_class($installer);die();//Mage_Core_Model_Resource_Setup Mage_Catalog_Model_Resource_Setup
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer->startSetup();

//Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
//$installer->removeAttribute('catalog_category', 'category_attribute_hottest');

//增加属性
$arrname=array('class_address', 'n_classno','n_classtime1','n_classtime2','requirement');
$arrlabel=array('上课地址','课号','开始时间','结束时间','要求');
$arrdefault=array('','?','00:00','00:00','');

for ($i=0;$i<count($arrname);$i++)
{
    $obj = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $arrname[$i]);
    $objId=$obj->getId();

    if (($arrname[$i]=='class_address') or ($arrname[$i]=='requirement'))
      $global=Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE;
    else
      $global=Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL;

    if ( is_null($objId) )
        $installer->addAttribute('catalog_product', $arrname[$i],array(
                'default' => $arrdefault[$i],
                'group' => 'General',
                'label'	 => $arrlabel[$i],
                'type' => 'text',
                'input' => 'text',
                'backend' => '',
                'frontend' => '',
                'source' => 'eav/entity_attribute_source_table',
                'global' => $global,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'is_searchable' => false,
                'is_filterable' => 0,
                'is_filterable_in_search' => false,
                'used_in_product_listing' => false,
                'used_for_sort_by' => false,
                'visible_on_front' => true,
                'visible_in_advanced_search' => false,
                'unique' => false )
        );
    echo ("add $arrname[$i]<br/>");
}

$arrname=array('seats');
$arrlabel=array('座位数');
for ($i=0;$i<count($arrname);$i++)
{
    $obj = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $arrname[$i]);
    $objId=$obj->getId();
    if ( is_null($objId) )
        $installer->addAttribute('catalog_product', $arrname[$i],array(
                'default' => 8,
                'group' => 'General',
                'label'	 => $arrlabel[$i],
                'type' => 'int',
                'input' => 'text',
                'backend' => '',
                'frontend' => '',
                'source' => 'eav/entity_attribute_source_table',
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'is_searchable' => false,
                'is_filterable' => 0,
                'is_filterable_in_search' => false,
                'used_in_product_listing' => false,
                'used_for_sort_by' => false,
                'visible_on_front' => true,
                'visible_in_advanced_search' => false,
                'unique' => false )
        );

    echo ("add $arrname[$i]<br/>");
}


$arrname=array('class_date');
$arrlabel=array('上课日期');
for ($i=0;$i<count($arrname);$i++)
{
    $obj = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $arrname[$i]);
    $objId=$obj->getId();
    if ( is_null($objId) )
        $installer->addAttribute('catalog_product', $arrname[$i],array(
                'group' => 'General',
                'label'	 => $arrlabel[$i],
                'type' => 'date',
                'input' => 'text',
                'backend' => '',
                'frontend' => '',
                'source' => 'eav/entity_attribute_source_table',
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'is_searchable' => false,
                'is_filterable' => 0,
                'is_filterable_in_search' => false,
                'used_in_product_listing' => false,
                'used_for_sort_by' => false,
                'visible_on_front' => true,
                'visible_in_advanced_search' => false,
                'unique' => false )
        );

    echo ("add $arrname[$i]<br/>");
}

//厨师
$obj = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'chef');
$objId=$obj->getId();
if ( is_null($objId) )
    $installer->addAttribute('catalog_product', 'chef',array(
            'group' => 'General',
            'label'	 =>'厨师',
            'type' => 'text',
            'input' => 'select',
            'backend' => '',
            'frontend' => '',
            // 'source' => 'eav/entity_attribute_source_table',
            'source'=>'d1m_chef/system_config_source_chef',
            'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'is_searchable' => false,
            'is_filterable' => 0,
            'is_filterable_in_search' => false,
            'used_in_product_listing' => false,
            'used_for_sort_by' => false,
            'visible_on_front' => true,
            'visible_in_advanced_search' => false,
            'unique' => false )
    );

echo ("add chef <br/>");


$arrname=array('coursetype','n_shop','province','western_cuisine');
$arrlabel=array('课程类型','门店','省份','菜式',);


for ($i=0;$i<count($arrname);$i++)
{
    $name=$arrname[$i];
    $obj = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $name);
    $objId=$obj->getId();

    if ($name=='coursetype')
        $arroptions=array
        (
            'a' => array('世界美食'),
            'b' => array('甜品天地'),
            'c' => array('儿童厨艺'),
            'd' => array('当代生活'),
            'e' => array('健康养生'),
            'f' => array('海外精进'),
        );
    else if ($name=='n_shop')
        $arroptions=array
        (
            'a' => array('待定'),
        );
    else if ($name=='province')
        $arroptions=array
        (
            'a' => array('上海'),
            'b' => array('北京'),
        );
    else if ($name=='western_cuisine')
        $arroptions=array
        (
            'a' => array('甜品'),
            'b' => array('西式菜肴'),
            'c' => array('中式菜肴'),
        );



    if ( is_null($objId) )
        $installer->addAttribute('catalog_product', $name,
            array(
                'group' => 'General',
                'label'	 => $arrlabel[$i],
                'type' => 'text',
                'input' => 'select',
                'backend' => '',
                'frontend' => '',
                'source' => 'eav/entity_attribute_source_table',
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'is_searchable' => true,
                'is_filterable' => 0,
                'is_filterable_in_search' => true,
                'used_in_product_listing' => true,
                'used_for_sort_by' => true,
                'option' => array ('value' => $arroptions),
                'visible_on_front' => true,
                'visible_in_advanced_search' => false,
                'unique' => false )
        );
    echo ("add $name<br/>");
}

// die('done');
$installer->endSetup();