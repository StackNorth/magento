<?php
class D1m_Producttool_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getdownloadinfo($productname)
    {
        /* @var $model D1m_Producttool_Model_Downloadfile */
        $model=Mage::getModel('d1m_producttool/downloadfile');
        /* @var $collection   D1m_Producttool_Model_Mysql4_Downloadfile_Collection  */
        $collection=$model->getCollection();
        $collection->addFilter('pname',$productname);
        $item=$collection->getFirstItem();
        $id=$item->getId();
        if ($id>0)
        {
            $fname=$item->getData('fname');
            $fn=Mage::getBaseDir('media').'/d1m/course_download/'.$id;
            return array($fname,$fn);
        }
        else
           return '';

    }
    
    public function getUniqueSku($sku,$i=0){
        $pid = Mage::getModel('catalog/product')->getIdBySku($sku);
        if($pid > 0){
            $i++;
            return $this->getUniqueSku($sku.'_'.$i);
        }else{
            return $sku;
        }
    }
    
    public function getDirFiles($newDir){
        $files = array();
        if(is_file($newDir)){
            $files[] = $newDir;
        }else{
            if ($handle = opendir("$newDir")) {
                while (false !== ($file = readdir($handle))) {
                    if(in_array($file, array('.','..'))){
                        continue;
                    }
                    $path = $newDir."/".$file;

                    if(is_dir($path)){
                        $files[$file] = $path;
                    }else if(is_file($path)){
                        $path_parts= pathinfo($path);
                        $filename=     $path_parts [ 'filename' ];
                        $files[$filename] = $path;

                    }
                }
            }
        }
        sort($files);
        return $files;
    }
    
    public function getProductsByName($name){
        $product = Mage::getModel('catalog/product');
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = $product->getCollection();
        $collection->addAttributeToFilter('name', $name);
        return $collection;
    }
    
    public function cleanProductImages($productid){
        /* @var $loadpro Mage_Catalog_Model_Product */
        /* @var $mediaApi Mage_Catalog_Model_Product_Attribute_Media_Api */
        $mediaApi = Mage::getModel("catalog/product_attribute_media_api");
        $mediaApiItems = $mediaApi->items($productid);
        foreach($mediaApiItems as $item) {
            $datatemp = $mediaApi->remove($productid, $item['file']);
        }
        /* @var $mediaApi D1m_Producttool_Model_Product_Attribute_Media_Api */
        $mediaApi = Mage::getModel("d1m_producttool/product_attribute_media_api");
        $mediaApiItems = $mediaApi->items($productid, 2);
        foreach($mediaApiItems as $item) {
            $datatemp = $mediaApi->remove($productid, $item['file']);
        }
    }
}
