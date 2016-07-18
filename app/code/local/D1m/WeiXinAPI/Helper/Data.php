<?php
class D1m_WeiXinAPI_Helper_Data extends Mage_Core_Helper_Abstract{


    /***
     *  save shipping
     * @param $data
     * @return array
     */
    public function validateAddress($data)
    {

        $data=$this->initDefaultAddress($data);
        /* @var $address Mage_Customer_Model_Address */
        $address =  Mage::getModel('customer/address');

        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm    = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
            ->setEntityType('customer_address')
            ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());

        $addressForm->setEntity($address);
        // emulate request object
        $addressData    = $addressForm->extractData($addressForm->prepareRequest($data));

        $addressErrors  = $addressForm->validateData($addressData);

        if ($addressErrors !== true)
        {
            return array('code' => 0, 'message' => $addressErrors);
        }

        $addressForm->compactData($addressData);
        // unset shipping address attributes which were not shown in form
        foreach ($addressForm->getAttributes() as $attribute) {
            if (!isset($data[$attribute->getAttributeCode()])) {
                $address->setData($attribute->getAttributeCode(), NULL);
            }
        }

        if (($validateRes = $address->validate())!==true)
        {
            return array('code' => 0, 'message' => $validateRes);
        }

        return $address->getData();

    }
    protected function initDefaultAddress($data){
        $data['save_in_address_book']	= 0;
        $data['city']		= 'unknown';
        $data['district'] 	= 'unknown';
        $data['country_id'] = 'CN';
        $data['customer_address_id'] = '';
        $data['firstname'] 	= 'unknown';
        $data['lastname'] 	= 'unknown';
        $data['postcode']	= '201103';
        $data['telephone']	= 'unknown';
        $data['region']	 	= 'unknown';
        $data['region_id']	= '487';
        $data['street']	= array('unknown');
        $data['email']		= 'unknown@unknown.com';
        return $data;
    }
}