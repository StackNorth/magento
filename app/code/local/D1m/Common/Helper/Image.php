<?php
/**
 * Class D1m_Common_Helper_Image
 */
class D1m_Common_Helper_Image extends Mage_Core_Helper_Abstract
{
    /**
     *  default thumb directory name
     */
    const  DEFAULT_THUMB_DIRECTORY_NAME = 'thumb';

    /**
     * @var resize directory name
     */
    protected $_resizeDirectoryName;

    /***
     *  enable keep frame for image
     *
     * @var bool
     *
     */
    protected $_keepFrame = false;

    /**
     * @param boolean $keepFrame
     */
    public function setKeepFrame($keepFrame)
    {
        if (in_array($keepFrame,array(true,false)))
        {
            $this->_keepFrame = $keepFrame;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function getKeepFrame()
    {
        return $this->_keepFrame;
    }


    /**
     * @param mixed $resizeDirectoryName
     */
    public function setResizeDirectoryName($resizeDirectoryName)
    {
        $this->_resizeDirectoryName = $resizeDirectoryName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResizeDirectoryName()
    {
        if (is_null($this->_resizeDirectoryName))
        {
              return self::DEFAULT_THUMB_DIRECTORY_NAME;
        }

        return $this->_resizeDirectoryName;
    }

    /**
     *  get resize image url
     * @param $fileName
     * @param int $width
     * @param int $height
     * @return string
     */
    public function resizeImg($fileName, $width=100, $height = 100)
    {
        if (!strlen(trim($fileName)))
        {
            return ;
        }

        $baseFolderURL = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) ;

        $basePath = str_replace('\\','/',$baseFolderURL. DS . $fileName);

        $newPathFolder = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . "resized";
        $newFilePath   = $this->getResizeDirectoryName() .DS. $width.'x'.$height .DS. pathinfo($basePath,PATHINFO_BASENAME);
        $newPath = str_replace('\\','/',$newPathFolder .DS .$newFilePath);

        if(!is_dir($newPathFolder))
        {
          mkdir($newPathFolder,0777,true);
        }

        if (!is_dir(dirname($newPath)))
        {
            mkdir(dirname($newPath),0777,true);
        }

        //if image has already resized then just return URL
        if (file_exists($basePath) && is_file($basePath) && !file_exists($newPath))
        {
            $imageObj = new Varien_Image($basePath);
            $imageObj->constrainOnly(TRUE);
            $imageObj->keepAspectRatio(TRUE);
            $imageObj->backgroundColor(array(255,255,255));
            $imageObj->keepFrame($this->getKeepFrame());
            $imageObj->resize($width, $height);
            $imageObj->save($newPath);

            Mage::getSingleton('core/session')->setStatus('1');
            Mage::getSingleton('core/session')->setResizedimgname($fileName);
        }

        return str_replace('\\','/',Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA). "resized".DS.$newFilePath);

        return;
    }
}