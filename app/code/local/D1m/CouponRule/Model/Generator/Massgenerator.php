<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * SalesRule Mass Coupon Generator
 *
 * @method Mage_SalesRule_Model_Resource_Coupon getResource()
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @author      D1M
 */

class D1m_CouponRule_Model_Generator_Massgenerator extends Mage_Core_Model_Abstract
    implements D1m_CouponRule_Model_Generator_CodegeneratorInterface
{
    /**
     * Maximum probability of guessing the coupon on the first attempt
     */
    const MAX_PROBABILITY_OF_GUESSING = 9;
    const MAX_GENERATE_ATTEMPTS = 10;

    /**
     * Count of generated Coupons
     * @var int
     */
    protected $_generatedCount = 0;
    
    protected $_coupon_collections = array();

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('salesrule/coupon');
    }

    /**
     * Generate coupon code
     *
     * @return string
     */
    public function generateCode()
    {
        $format  = $this->getFormat();
        if (!$format) {
            $format = Mage::getStoreConfig('couponRule/coupon/format');
        }
        $length  = max(1, (int) $this->getLength());
        $split   = max(0, (int) $this->getDash());
        $suffix  = $this->getSuffix();
        $prefix  = $this->getPrefix();

        $splitChar = $this->getDelimiter();
        $charset = Mage::helper('salesrule/coupon')->getCharset($format);
        
        $code = '';
        $charsetSize = count($charset);
        for ($i=0; $i<$length; $i++) {
            $char = $charset[mt_rand(0, $charsetSize - 1)];
            if ($split > 0 && ($i % $split) == 0 && $i != 0) {
                $char = $splitChar . $char;
            }
            $code .= $char;
        }

        $code = $prefix . $code . $suffix;
        return $code;
    }

    /**
     * Retrieve delimiter
     *
     * @return string
     */
    public function getDelimiter()
    {
        if ($this->getData('delimiter')) {
            return $this->getData('delimiter');
        } else {
            return Mage::helper('salesrule/coupon')->getCodeSeparator();
        }
    }

    /**
     * Generate Coupons Pool
     *
     * @return Mage_SalesRule_Model_Coupon_Massgenerator
     */
    public function generatePool()
    {
// Varien_Debug::backtrace();

        $this->_generatedCount = 0;
        $size = $this->getQty();

        $maxProbability = $this->getMaxProbability() ? $this->getMaxProbability() : self::MAX_PROBABILITY_OF_GUESSING;
        $maxAttempts = $this->getMaxAttempts() ? $this->getMaxAttempts() : self::MAX_GENERATE_ATTEMPTS;

        /** @var $coupon Mage_SalesRule_Model_Coupon */
        $coupon = Mage::getModel('salesrule/coupon');

        $chars = count(Mage::helper('salesrule/coupon')->getCharset($this->getFormat()));
        $length = (int) $this->getLength();
        $maxCodes = pow($chars, $length);
        $probability = $size / $maxCodes;

        //increase the length of Code if probability is low

        if ($probability > $maxProbability) {
 
            $this->setLength($length);
        }

        $now = $this->getResource()->formatDate(
            Mage::getSingleton('core/date')->gmtTimestamp()
        );

        for ($i = 0; $i < $size; $i++) {
            $attempt = 0;
            do {
                if ($attempt >= $maxAttempts) {
                    Mage::throwException(Mage::helper('salesrule')->__('Unable to create requested Coupon Qty. Please check settings and try again.'));
                }
                $code = $this->generateCode();
                $attempt++;
            } while (Mage::getModel('couponRule/coupon')->checkCouponExist($code));

            $expirationDate = $this->getToDate();
            if ($expirationDate instanceof Zend_Date) {
                $expirationDate = $expirationDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            }

            $coupon->setId(null)
                ->setRuleId($this->getRuleId())
                ->setUsageLimit($this->getUsesPerCoupon())
                ->setUsagePerCustomer($this->getUsesPerCustomer())
                ->setExpirationDate($expirationDate)
                ->setCreatedAt($now)
                ->setType(D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT)
                ->setCode($code)
                ->save();
            
            //保存对应关系
            $this->_coupon_collections[$i] = $code;
            $this->_generatedCount++;
        }
        return $this;
    }

    /**
     * Validate input
     *
     * @param array $data
     * @return bool
     */
    public function validateData($data)
    {
        return !empty($data) && !empty($data['qty']) && !empty($data['rule_id'])
            && !empty($data['length']) && !empty($data['format'])
            && (int)$data['qty'] > 0 && (int) $data['rule_id'] > 0
            && (int) $data['length'] > 0;
    }

    /**
     * Retrieve count of generated Coupons
     *
     * @return int
     */
    public function getGeneratedCount()
    {
        return $this->_generatedCount;
    }
    
    /**
     *  return all coupon code list
     */
    public function getCouponCollections(){
        return $this->_coupon_collections;
    }
}
