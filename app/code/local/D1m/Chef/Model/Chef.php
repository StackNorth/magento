<?php
class D1m_Chef_Model_Chef extends Mage_Core_Model_Abstract {

    //define xml node
    const BLOG_ARTICLE_SETTING_ENABLE_DELETE_IMAGE_FILE = 'chef/chefSetting/enable_delete_file';

    public function _construct() {
        parent::_construct();
        $this->_init('d1m_chef/chef');
    }

    protected function _beforeSave() {
        $nowDate  = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        if (!$this->getId()) {
            $this->setCreatedTime($nowDate)
                ->setUpdateTime($nowDate);
        }else{
            $this->setUpdateTime($nowDate);
        }
        parent::_beforeSave();
    }

    //get absolute path
    public function getAbsoluteThumbnailPath(){
        if (strlen($this->getThumbnail())){
            $thumbnailPath = str_replace('\\','/',Mage::getBaseDir('media') .DS.$this->getThumbnail());
        }

        if (!empty($thumbnailPath) && file_exists($thumbnailPath) && is_file($thumbnailPath)){
            return $thumbnailPath;
        }
        return ;
    }

    //delete image file
    public function deleteImageFile(){
        if($this->getAbsoluteThumbnailPath())
            @unlink($this->getAbsoluteThumbnailPath());
    }

    protected function _checkEnableDeleteFile(){
        return Mage::getStoreConfigFlag(self::BLOG_ARTICLE_SETTING_ENABLE_DELETE_IMAGE_FILE);
    }

    //删除时，确定是否删除真正的图片
    public function delete(){
        if ($this->_checkEnableDeleteFile() && $this->getAbsoluteThumbnailPath()){
            @unlink($this->getAbsoluteThumbnailPath());
        }
        parent::delete();
    }

    //validate entity
    public function validate(){

    }
}
