<?php

/*
 * @author     D1M
 * @package    D1M
 * @copyright  Copyright (c)  D1M
 * @copyright  Copyright (c) D1M
 */

class D1m_CouponRule_Block_Adminhtml_Promo_Quote_Grid extends Mage_Adminhtml_Block_Promo_Quote_Grid
{

    protected function _prepareColumns ()
    {
        $this->addColumn('action',
                array(
                    'header' => Mage::helper('sales')->__('Action'),
                    'width' => '120px',
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => array(
                        array(
                            'caption' => Mage::helper('couponRule/data')->__('Activate'),
                            'url' => array('base' => '*/promo_quote/activate'),
                            'field' => 'rule_id'
                        ),
                        array(
                            'caption' => Mage::helper('couponRule/data')->__('Deactivate'),
                            'url' => array('base' => '*/promo_quote/deactivate'),
                            'field' => 'rule_id'
                        ),
                        array(
                            'caption' => Mage::helper('couponRule/data')->__('Duplicate'),
                            'url' => array('base' => '*/promo_quote/duplicate'),
                            'field' => 'rule_id'
                        ),
                        array(
                            'caption' => Mage::helper('couponRule/data')->__('Delete'),
                            'url' => array('base' => '*/promo_quote/massdelete'),
                            'field' => 'rule_id'
                        )
                    ),
                    'filter' => false,
                    'sortable' => false,
                    'index' => 'stores',
                    'is_system' => true,
        ));

        $this->addColumn('coupon_type', array(
            'header'    => Mage::helper('salesrule')->__('Coupon type'),
            'align'     => 'left',
            'width'     => '150px',
            'index'     => 'coupon_type',
            'type'      => 'options',
            'options'    => Mage::getModel('salesrule/rule')->getCouponTypes()
        ));
        return parent::_prepareColumns();
    }

}