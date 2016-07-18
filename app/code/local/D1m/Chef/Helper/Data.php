<?php
class D1m_Chef_Helper_Data extends Mage_Core_Helper_Abstract {

    
    const CHEF_IMAGE_FILE_PATH   =   'chef/chef';

    
    //get chef thumb dir
    public function getChefChefThumbDirectory()
    {
       return  Mage::getBaseDir('media') . DS . self::CHEF_IMAGE_FILE_PATH;
    }

    //upload files
    public function uploadFile(array $files,$object){
        if(!is_array($files) || empty($files['csmallpic']['name']))
            return ;

        try
        {
            $suffix = time() . rand();
            $thumbFileName = $suffix.str_replace('\\', '/', str_replace(' ', '', $files['csmallpic']['name']));
            $upload = new Varien_File_Uploader('csmallpic');
            $upload->setAllowedExtensions(array('jpg', 'jpeg', 'png', 'gif'));
            $upload->setAllowRenameFiles(false);
            $upload->setFilesDispersion(false);

            if ($object instanceof D1m_Chef_Model_Chef)
            {
                $result = $upload->save($this->getChefChefThumbDirectory(),$thumbFileName);
                if (!empty($result['file']))
                    return  str_replace('\\','/',self::CHEF_IMAGE_FILE_PATH .DS.$result['file']);
            }
        }
        catch (Exception $e)
        {
            Mage::logException($e);
        }
        return ;
    }

    /**
     * Chef main content image.
     */
    public function uploadFileForThumbnailMain(array $files,$object)
    {
        if(!is_array($files) || empty($files['cbigpic']['name']))
            return ;

        try {
            $suffix = time() . rand();
            $thumbFileName = $suffix.str_replace('\\', '/', str_replace(' ', '', $files['cbigpic']['name']));
            $upload = new Varien_File_Uploader('cbigpic');
            $upload->setAllowedExtensions(array('jpg', 'jpeg', 'png', 'gif'));
            $upload->setAllowRenameFiles(false);
            $upload->setFilesDispersion(false);

            if ($object instanceof D1m_Chef_Model_Chef)
            {
                $result = $upload->save($this->getChefChefThumbDirectory(),$thumbFileName);
                if (!empty($result['file']))
                    return  str_replace('\\','/',self::CHEF_IMAGE_FILE_PATH .DS.$result['file']);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return ;
    }






    /**
     * get chef detail url
     */
    public function getChefDetailUrl($id)
    {
        return $this->_getUrl('chef/index/detail', array( 'id' => $id ));
    }

    /**
     * get chef chef or region image url
     */
    public function getImageUrl($imgPath)
    {
        return Mage::getBaseUrl('media'). $imgPath;
    }


}
