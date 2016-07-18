<?php

class D1m_Customapi_Model_Orderservice extends Mage_Core_Model_Abstract
{

    public function addClassOrder($order, $debug = 0)
    {
        try {
            $configUrl = 'http://192.168.100.22:8090/aCRM_Fissler/webService/memberService?wsdl';
            //   $configUrl = 'http://192.168.100.22:8090/aCRM_Fissler_UAT/webService/classOrderService?wsdl';
            if ($debug) echo 'wsdl:' . $configUrl . ' method: addClassOrder<br/>';
            if(!$order || $order->getId() <= 0 ){return '';}
          //  $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            $items = $order->getAllItems();
            $totalPrice = intval($order->getGrandTotal());
            foreach ($items as $item) {
                $product = $this->getProduct($item->getSku());
                $quantity = intval($item->getQtyOrdered());
                //   $className= $item->getName() ;
                //echo $product->getCoursetype();
                $startDate = substr($product->getClassDate(), 0, 11) . $product->getNClasstime1() . ':00';
                $overDate = substr($product->getClassDate(), 0, 11) . $product->getNClasstime2() . ':00';
                $price = intval($product->getPrice());

            }
            $customerModel = Mage::getModel('customer/customer');
            $memberInfo = $customerModel->load($order->getCustomerId());
            $addOrder = array(
                'classOrder' => array(
                    'memberID' => $memberInfo->getIncrementId(),
                    'mobile' => $memberInfo->getPhone(),
                    'purchaseDate' => $order->getCreatedAt(),//购买日期
                    'quantity' => $quantity,//课程数量
                    'price' => $price,//课程单价
                    'points' => $price,//课程单价
                    'totalPrice' => $totalPrice,//1 购买总金额
                    'startDate' => $startDate,//上课时间
                    'expireDate' => $overDate,//结束时间
                    'channel' => '4',
                    'createBy' => $order->getCreatedAt(),
                    'createDate' => $order->getCreatedAt(),
                    'modifyBy' => $order->getCreatedAt(),
                    'modifyDate' => $order->getCreatedAt(),
                )
            );

            $client = new SoapClient($configUrl, array('trace' => true));
            $resultData = $client->addClassOrder($addOrder);
            if ($debug) var_dump($resultData);
            if ($debug) print_r($addOrder);
            if ($resultData && is_object($resultData)) {
                if ($resultData->addClassOrder != 1) {
                    $data = array('order' => $addOrder, 'result' => $resultData);
                    Mage::helper('msgnotice')->saveFailedActions('addClassOrder', serialize($data), serialize($resultData));
                    Mage::log($data, null, 'crm.log');
                } else {
                    return true;
                }
            }


        } catch (SoapFault $fault) {
            $errorString = "Error: " . $fault->faultcode . ". string: " . $fault->faultstring;
            if ($debug) echo '$errorString:' . $errorString . '<br/>';
            $data = array('order' => $order);
            Mage::helper('msgnotice')->saveFailedActions('addClassOrder', serialize($data), $errorString);
        } catch (Exception $e) {
            // echo '$errorString:'.$e->getMessage().'<br/>';

            $data = array('order' => $order);
            Mage::helper('msgnotice')->saveFailedActions('addClassOrder', serialize($data), $e->getMessage());
            Mage::logException($e);
        }

        return false;
    }
    public function getProduct($sku)
    {
        $id = Mage::getModel('catalog/product')->getIdBySku($sku);

        if ($id)
        {
            return Mage::getModel('catalog/product')->load($id);
        }

        return ;
    }
    public function addReserveClass($order, $debug = 0)
    {
        try {
            $configUrl = 'http://112.65.137.219:8008/aCRM_Fissler/webService/reserveClassService?wsdl';

            if ($debug) echo 'wsdl:' . $configUrl . ' method: addReserveClass<br/>';

            $client = new SoapClient($configUrl, array('trace' => true));

            $resultData = $client->addReserveClass($order);

            if ($debug) var_dump($resultData);

            if ($resultData && is_object($resultData)) {
                if ($resultData->addClassOrder != 1) {
                    $data = array('order' => $order);
                    Mage::helper('msgnotice')->saveFailedActions('addClassOrder', serialize($data), serialize($resultData));
                } else {
                    return true;
                }
            }


        } catch (SoapFault $fault) {
            $errorString = "Error: " . $fault->faultcode . ". string: " . $fault->faultstring;

            if ($debug) echo '$errorString:' . $errorString . '<br/>';

            $data = array('order' => $order);
            Mage::helper('msgnotice')->saveFailedActions('addClassOrder', serialize($data), $errorString);
        } catch (Exception $e) {
            if ($debug) echo '$errorString:' . $e->getMessage() . '<br/>';
            $data = array('order' => $order);
            Mage::helper('msgnotice')->saveFailedActions('addClassOrder', serialize($data), $e->getMessage());
            Mage::logException($e);
        }

        return false;
    }


}