<?php
class D1m_Producttool_IndexController extends  Mage_Core_Controller_Front_Action
{

    public function ajaxAction()
    {
        // index.php/producttool/index/ajax/pname/苹果派
        $arrkey=array('western_cuisine','coursetype','chef','province','seats','description','short_description','requirement','class_address');

        //get product name
        $result = array('status'=>false);
        $pname = $this->getRequest()->getParam('pname', '');
        $pid = $this->getRequest()->getParam('pid',0);
        if (($pname != '') or ($pid>0))
        {
            //获得产品信息
            /* @var $model Mage_Catalog_Model_Product */;
            /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
            $model=Mage::getModel('catalog/product');


            $collection=$model->getCollection();
            if ($pname!="")
            $collection->addAttributeToFilter('name',$pname);

            if ($pid>0)
                $collection->addAttributeToFilter('entity_id',$pid);

            $collection->addAttributeToSelect('name');
            for ($i=0;$i<count($arrkey);$i++)
            {
                $collection->addAttributeToSelect($arrkey[$i]);
            }
            $collection->setOrder('entity_id','desc');
            $obj=$collection->getFirstItem();
            if ($obj!=null)
            {
                // echo $obj->getId();                die();
                $result['id']=$obj->getId();
                $result['name']=$obj->getData('name');
                $result['status']=true;
                for ($i=0;$i<count($arrkey);$i++)
                {
                    $idx=$arrkey[$i];
                    $result[$idx]=$obj->getData($idx);
                }
            }

        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function updateNshopAction(){
        /* @var $choose D1m_Course_Block_Choose */
        $choose = Mage::getBlockSingleton('d1m_course/choose');
        $n_shops = $choose->getNShop();
        
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $model=Mage::getModel('catalog/product');
        $collection=$model->getCollection();
        $collection->addAttributeToSelect(array('n_shop', 'class_address'));
        foreach ($collection as $item){
            if(!$item->getData('n_shop') && $item->getData('class_address')){
                $product=Mage::getModel('catalog/product');
                $product->load($item->getId());
                $n_shop = '';
                foreach ($n_shops as $shop){
                    if(strstr($product->getData('class_address'), $shop->getData('label'))){
                        $n_shop = $shop->getData('value');
                    }
                }
                if($n_shop){
                    $product->setData('n_shop', $n_shop);
                    $product->save();
                }
            }
        }
        echo 'done';
    }

    public function updateImagesAction(){
        header("Content-type: text/html; charset=utf-8");
        set_time_limit(0);
        $newDir = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/catalog/product/new/';
        $files = Mage::helper('d1m_producttool')->getDirFiles($newDir);
        foreach ($files as $key=>$file){
            if(is_dir($file)){
                $productName = $key;
            }else if(is_file($file)){
                $productName = substr($key, 0, strpos($key, '.'));
            }
            $products = Mage::helper('d1m_producttool')->getProductsByName($productName);
            if(count($products) > 0){
                foreach ($products as $item){
                    Mage::helper('d1m_producttool')->cleanProductImages($item->getId());
                    /* @var $product Mage_Catalog_Model_Product */
                    $product = Mage::getModel('catalog/product');
                    $product->load($item->getId());
                    $images = Mage::helper('d1m_producttool')->getDirFiles($file);
                    foreach ($images as $imageFile){
                        $product->addImageToMediaGallery($imageFile,array('image','small_image','thumbnail'),false,false);
                    }
                    $product->save();
                    echo $item->getId().'-'.$item->getName().'<br />';
                }
            }else{
                echo $key.'--<br />';
            }
        }
        echo 'done';
    }
}