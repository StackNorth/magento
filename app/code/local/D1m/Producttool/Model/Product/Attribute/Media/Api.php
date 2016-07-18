<?php
class D1m_Producttool_Model_Product_Attribute_Media_Api  extends Mage_Catalog_Model_Product_Attribute_Media_Api
{
    /**
     * Remove image from product
     *
     * @param int|string $productId
     * @param string $file
     * @return boolean
     */
    public function remove($productId, $file, $identifierType = null)
    {
        $product = $this->_initProduct($productId, 2, $identifierType);
    
        $gallery = $this->_getGalleryAttribute($product);
    
        if (!$gallery->getBackend()->getImage($product, $file)) {
            $this->_fault('not_exists');
        }
    
        $gallery->getBackend()->removeImage($product, $file);
    
        try {
            $product->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('not_removed', $e->getMessage());
        }
    
        return true;
    }
} // Class Mage_Catalog_Model_Product_Attribute_Media_Api End
