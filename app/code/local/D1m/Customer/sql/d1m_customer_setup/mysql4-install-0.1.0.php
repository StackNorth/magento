<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->updateAttribute('customer', 'firstname', 'is_required', '0');
$installer->updateAttribute('customer', 'lastname', 'is_required', '0');


$installer->addAttribute('customer', 'username', array(
    'label'    => 'Username',
    'is_visible'        => 1,
    'is_user_defined'   => 0,
    'required'     => 1,
    'sort_order'     => 50,
));


$installer->addAttribute('customer', 'phone', array(
    'label'    => 'Phone',
    'is_visible'        => 1,
    'is_user_defined'   => 0,
    'required'     => 1,
    'sort_order'     => 51,
));


/* @var $eavConfig Mage_Eav_Model_Config */
$eavConfig = Mage::getSingleton('eav/config');

// update customer system attributes used_in_forms data
$attributes = array(
    'username'      => array(),
    'phone'      => array()
);

$defaultUsedInForms = array(
    'customer_account_create',
    'customer_account_edit',
    'checkout_register'
);

foreach ($attributes as $attributeCode => $data) {
    $attribute = $eavConfig->getAttribute('customer', $attributeCode);
    if (!$attribute) {
        continue;
    }
    if (false === ($attribute->getData('is_system') == 1 && $attribute->getData('is_visible') == 0)) {
        $usedInForms = $defaultUsedInForms;
        if (!empty($data['adminhtml_only'])) {
            $usedInForms = array('adminhtml_customer');
        } else {
            $usedInForms[] = 'adminhtml_customer';
        }
       
        $attribute->setData('used_in_forms', $usedInForms);
    }
    $attribute->save();
}


$installer->endSetup();
