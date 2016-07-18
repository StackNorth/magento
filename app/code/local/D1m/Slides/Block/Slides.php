<?php
/**
 * Created by Victor Guo
 * Date: 13-8-19
 * Time: 下午3:10
 */
class D1m_Slides_Block_Slides extends Mage_Core_Block_Template
{
    public function getSlides()
    {
        $id = $this->getGroupId();
        if($id)
        {
            $slidesTable = Mage::getSingleton('core/resource')
                        ->getTableName('d1m_slide');
            $slidesCollection = Mage::getModel('d1m_slides/mapping')->getCollection();
            $slidesCollection->addFieldToFilter('group_id', $id)
                ->getSelect()
                ->join(array('t2' => $slidesTable),
                    't2.slide_id=main_table.slide_id',
                    array('title', 'filename', 'link'));
            return $slidesCollection;
        }
    }

    public function getResizedImage($slidePath, $width, $height)
    {
        $mediaDir = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        if(!empty($width) && !empty($height))
        {
            $resizeFolder = 'custom'.DS.'slides'.DS.'resized'.DS.$width.'x'.$height.DS;

            $fileName = substr($slidePath, (strrpos($slidePath, DS) + 1));

            $basePath = $mediaDir.DS.$slidePath;
            $newPath = $mediaDir.DS.$resizeFolder.$fileName;

            if(file_exists($basePath) && is_file($basePath) && !file_exists($newPath))
            {
                $imageObj = new Varien_Image($basePath);
                $imageObj->constrainOnly(true);
                $imageObj->keepAspectRatio(true);
                $imageObj->keepFrame(false);
                $imageObj->resize($width, $height);
                $imageObj->save($newPath);
            }

            $resizedUrl = $mediaUrl.$resizeFolder.$fileName;
            return $resizedUrl;
        }
        else
        {
            throw new Exception('Missing width or height');
        }
    }
}