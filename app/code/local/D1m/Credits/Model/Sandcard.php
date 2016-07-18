<?php
/**
 * User: ahsw@qq.com
 * caeate Time: 2016/6/2715:02
 */

class D1m_Credits_Model_Sandcard extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {

        $this->_init('d1m_credits/sandcard');
    }
    public function  import($file='fissler_sale20160627190001.txt'){

      //  $fileUrl='ftp://fissler_online:Sand@123@61.129.71.106/'.$file;//正式
        $fileUrl='ftp://lcc:redhat@61.129.71.106/'.$file;//UAT
        return  file_get_contents($fileUrl);

    }
    public function saveRow($row){
         if(strlen($row[1])>5){
                   if(! $this->checkCardNum($row[1])){
                       $now = Mage::getModel('core/date')->date('Y-m-d H:i:s');
                       $data=array(
                           'card_num'=>$row[1],
                           'discount'=>$row[0],
                           'card_name'=>$row[4],
                           'sale_price'=>$row[5],
                           'created'=>$now
                       );

                       $sandCardModel=Mage::getModel('d1m_credits/sandcard');
                       $sandCardModel->setData($data);
                    return    $sandCardModel->save();
                   }
            }
        return '';
    }
    public function checkCardNum($cardNum){
        if($cardNum==''){
           return 'error';
        }
       return  $this->_getResource()->checkCardNum($cardNum);


    }

}
