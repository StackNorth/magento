<?php
class D1m_Customapi_Model_Memberservice extends Mage_Core_Model_Abstract
{

	public function getSoapClient($debug=0)
	{

	 	$configUrl  = 'http://192.168.100.22:8090/aCRM_Fissler/webService/memberService?wsdl';// Produce

		//  $configUrl ='http://192.168.100.22:8090/aCRM_Fissler_UAT/webService/memberService?wsdl';//UAT
		if ($debug) echo 'wsdl: '.$configUrl.'<br/>';
		return new SoapClient($configUrl,array('trace' => true));
	}



	public function addMemberInfo($customerCrm,$debug=0)
	{

		try
       	{
			$configUrl  = 'http://192.168.100.22:8090/aCRM_Fissler/webService/memberService?wsdl';// Produce
			$client =new SoapClient($configUrl,array('trace' => true));
            $customerCrm['channel']=3;//用户来源!
            $param=array('member'=>$customerCrm);
			$resultData = $client->addMemberInfo($param);

			$data = array('addCustomer'=>$param,'result'=>$resultData,'memberID'=>$resultData->addMemberInfo->memberID);
			Mage::log($data,null, 'crm.log');
			if($resultData && is_object($resultData))
			{

				if($resultData->addMemberInfo->returnValue == '-1')
				{
                    return '';
				}
                return $resultData->addMemberInfo->memberID;
			}


		} catch (SoapFault $fault)
        {
			$errorString = "Error: ".$fault->faultcode.". string: ".$fault->faultstring;
			if ($debug) echo '$errorString:'.$errorString.'<br/>';
			$data = array('customer'=>$param);
            Mage::helper('msgnotice')->saveFailedActions('addMember', serialize($data), $errorString);
            Mage::logException($fault);
		}
		catch(Exception $e)
       	{
            if ($debug) echo '$errorString:'.$e->getMessage().'<br/>';
       		$data = array('customer'=>$param);
            Mage::helper('msgnotice')->saveFailedActions('addMember', serialize($data), $e->getMessage() );
       		Mage::logException($e);
       	}
		return false;
	}

	public function searchMembers($param,$debug=0)
	{
      // searchMembers(String memberID, String name,String mobile, String tel, String registerDate1, String registerDate2)
      /* @var $param array */
		try
       	{
			$client = $this->getSoapClient($debug);
            if ($debug) echo 'method: searchMembers<br/>';
            //$param=array();
            //$param=array('mobile',$mobile);
            /* @var $obj stdClass */

            // var_dump($param);

			$obj = $client->searchMembers($param);
            if ($debug) var_dump($obj);

            //如果条件为空，可以返回多条记录
            // $obj instanceof  stdClass
            if (!isset($obj->searchMembers)) return null; //nothing

            $data=$obj->searchMembers;
            //可能是数组或stdClass
            //如果是数组仅返回第一条记录
            if (is_array($data)) return $data[0];
            return $data; //数组或一条记录 元素 stdclass
		}
        catch (SoapFault $fault)
        {
            $errorString = "Error: ".$fault->faultcode.". string: ".$fault->faultstring;
            if ($debug) echo '$errorString:'.$errorString.'<br/>';
			mage::logException($fault);
		}
		catch(Exception $e)
       	{
       		if ($debug) echo '$errorString:'.$e->getMessage().'<br/>';
       		Mage::logException($e);
       	}
		return null;
	}

	public function updateMember($customer,$debug=0)
	{
		$resultData = false;
		try
       	{
			$client = $this->getSoapClient($debug);
            if ($debug) echo 'method: updateMember<br/>';
			$resultData = $client->updateMember($customer);

			if ($debug) var_dump($resultData);

			if($resultData && is_object($resultData))
			{
				if($resultData->addMember != 1)
				{
					$data = array('customer'=>$customer);
					Mage::helper('msgnotice')->saveFailedActions('updateMember', serialize($data), serialize($resultData));
				}
				else
				{
					return true;
				}
			}

		} catch (SoapFault $fault)
        {
			 $errorString = "Error: ".$fault->faultcode.". string: ".$fault->faultstring;
            if ($debug)  echo '$errorString:'.$errorString.'<br/>';
			$data = array('customer'=>$customer);
			Mage::helper('msgnotice')->saveFailedActions('updateMember', serialize($data), $errorString);
		}
		catch(Exception $e)
       	{
            if ($debug)  echo '$errorString:'.$e->getMessage().'<br/>';
       		$data = array('customer'=>$customer);
			Mage::helper('msgnotice')->saveFailedActions('updateMember', serialize($data), $e->getMessage());
       		Mage::logException($e);
       	}
		return false;
	}



}