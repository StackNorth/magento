<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 * 
 * EasyBanner module for Magento - flexible banner management
 * 
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_EasyBanner_Model_Banner_Image extends Mage_Core_Model_Abstract
{
    protected $_width;
    protected $_height;

    protected $_keepAspectRatio  = true;
    protected $_keepFrame        = true;
    protected $_keepTransparency = true;
    protected $_constrainOnly    = false;
    protected $_backgroundColor  = array(255, 255, 255);

    protected $_baseFile;
    protected $_newFile;
    protected $_processor;
    protected $_destinationSubdir;
    protected $_angle;
    protected $_watermarkPosition;
    protected $_watermarkWidth;
    protected $_watermarkHeigth;

    /**
     * @return TM_EasyBanner_Model_Banner_Image
     */
    public function setWidth($width)
    {
        $this->_width = $width;
        return $this;
    }

    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * @return TM_EasyBanner_Model_Banner_Image
     */
    public function setHeight($height)
    {
        $this->_height = $height;
        return $this;
    }

    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * @return TM_EasyBanner_Model_Banner_Image
     */
    public function setKeepAspectRatio($keep)
    {
        $this->_keepAspectRatio = (bool)$keep;
        return $this;
    }

    /**
     * @return TM_EasyBanner_Model_Banner_Image
     */
    public function setKeepFrame($keep)
    {
        $this->_keepFrame = (bool)$keep;
        return $this;
    }

    /**
     * @return TM_EasyBanner_Model_Banner_Image
     */
    public function setKeepTransparency($keep)
    {
        $this->_keepTransparency = (bool)$keep;
        return $this;
    }

    /**
     * @return TM_EasyBanner_Model_Banner_Image
     */
    public function setConstrainOnly($flag)
    {
        $this->_constrainOnly = (bool)$flag;
        return $this;
    }

    /**
     * @return TM_EasyBanner_Model_Banner_Image
     */
    public function setBackgroundColor(array $rgbArray)
    {
        $this->_backgroundColor = $rgbArray;
        return $this;
    }

    /**
     * @return TM_EasyBanner_Model_Banner_Image
     */
    public function setSize($size)
    {
        // determine width and height from string
        list($width, $height) = explode('x', strtolower($size), 2);
        foreach (array('width', 'height') as $wh) {
            $$wh  = (int)$$wh;
            if (empty($$wh))
                $$wh = null;
        }

        // set sizes
        $this->setWidth($width)->setHeight($height);

        return $this;
    }

    protected function _checkMemory($file = null)
    {
//        print '$this->_getMemoryLimit() = '.$this->_getMemoryLimit();
//        print '$this->_getMemoryUsage() = '.$this->_getMemoryUsage();
//        print '$this->_getNeedMemoryForBaseFile() = '.$this->_getNeedMemoryForBaseFile();

        return $this->_getMemoryLimit() > ($this->_getMemoryUsage() + $this->_getNeedMemoryForFile($file));
    }

    protected function _getMemoryLimit()
    {
        $memoryLimit = ini_get('memory_limit');

        if (!isSet($memoryLimit[0])){
            $memoryLimit = "128M";
        }

        if (substr($memoryLimit, -1) == 'M') {
            return (int)$memoryLimit * 1024 * 1024;
        }
        return $memoryLimit;
    }

    protected function _getMemoryUsage()
    {
        if (function_exists('memory_get_usage')) {
            return memory_get_usage();
        }
        return 0;
    }

    protected function _getNeedMemoryForFile($file = null)
    {
        $file = is_null($file) ? $this->getBaseFile() : $file;
        if (!$file) {
            return 0;
        }

        if (!file_exists($file) || !is_file($file)) {
            return 0;
        }

        $imageInfo = getimagesize($file);

        if (!isset($imageInfo[0]) || !isset($imageInfo[1])) {
            return 0;
        }
        if (!isset($imageInfo['channels'])) {
            // if there is no info about this parameter lets set it for maximum
            $imageInfo['channels'] = 4;
        }
        if (!isset($imageInfo['bits'])) {
            // if there is no info about this parameter lets set it for maximum
            $imageInfo['bits'] = 8;
        }
        return round(($imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels'] / 8 + Pow(2, 16)) * 1.65);
    }

    /**
     * Convert array of 3 items (decimal r, g, b) to string of their hex values
     *
     * @param array $rgbArray
     * @return string
     */
    private function _rgbToString($rgbArray)
    {
        $result = array();
        foreach ($rgbArray as $value) {
            if (null === $value) {
                $result[] = 'null';
            }
            else {
                $result[] = sprintf('%02s', dechex($value));
            }
        }
        return implode($result);
    }

    /**
     * Set filenames for base file and new file
     *
     * @param string $file
     * @return TM_EasyBanner_Model_Banner_Image
     */
    public function setBaseFile($file)
    {
        if (($file) && (0 !== strpos($file, '/', 0))) {
            $file = '/' . $file;
        }
        $baseDir = Mage::getBaseDir('media') . DS . trim(Mage::getStoreConfig('easybanner/general/image_folder'), ' /\\');
        
        if ('/no_selection' == $file) {
            $file = null;
        }
        if ($file) {
            if ((!file_exists($baseDir . $file)) || !$this->_checkMemory($baseDir . $file)) {
                $file = null;
            }
        }
        if (!$file) {
            $configPlaceholder = '/placeholder/' . Mage::getStoreConfig('easybanner/general/placeholder');
            $file = $configPlaceholder;
        }
        
        $baseFile = $baseDir . $file;
        
        if ((!$file) || (!file_exists($baseFile))) {
            throw new Exception(Mage::helper('catalog')->__('Image file not found'));
        }
        $this->_baseFile = $baseFile;
        
        $path = array(
            $baseDir,
            'cache'
        );
        if((!empty($this->_width)) || (!empty($this->_height))) {
            $path[] = "{$this->_width}x{$this->_height}";
        }
        $path[] = md5(
            implode('_', array(
                ($this->_keepAspectRatio  ? '' : 'non') . 'proportional',
                ($this->_keepFrame        ? '' : 'no')  . 'frame',
                ($this->_keepTransparency ? '' : 'no')  . 'transparency',
                ($this->_constrainOnly ? 'do' : 'not')  . 'constrainonly',
                $this->_rgbToString($this->_backgroundColor),
                'angle' . $this->_angle
            ))
        );
        $this->_newFile = implode('/', $path) . $file;
        
        return $this;
    }

    public function getBaseFile()
    {
        return $this->_baseFile;
    }

    public function getNewFile()
    {
        return $this->_newFile;
    }

    /**
     * @return TM_EasyBanner_Model_Banner_Image
     */
    public function setImageProcessor($processor)
    {
        $this->_processor = $processor;
        return $this;
    }

    /**
     * @return Varien_Image
     */
    public function getImageProcessor()
    {
        if( !$this->_processor ) {
//            var_dump($this->_checkMemory());
//            if (!$this->_checkMemory()) {
//                $this->_baseFile = null;
//            }
            $this->_processor = new Varien_Image($this->getBaseFile());
        }
        $this->_processor->keepAspectRatio($this->_keepAspectRatio);
        $this->_processor->keepFrame($this->_keepFrame);
        $this->_processor->keepTransparency($this->_keepTransparency);
        $this->_processor->constrainOnly($this->_constrainOnly);
        $this->_processor->backgroundColor($this->_backgroundColor);
        return $this->_processor;
    }

    /**
     * @see Varien_Image_Adapter_Abstract
     * @return TM_EasyBanner_Model_Banner_Image
     */
    public function resize()
    {
        if (is_null($this->getWidth()) && is_null($this->getHeight())) {
            return $this;
        }
        $this->getImageProcessor()->resize($this->_width, $this->_height);
        return $this;
    }
    
    /**
     * @return TM_EasyBanner_Model_Banner_Image
     */
    public function saveFile()
    {
        $this->getImageProcessor()->save($this->getNewFile());
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $baseDir = Mage::getBaseDir('media');
        $path = str_replace($baseDir . DS, "", $this->_newFile);
        return Mage::getBaseUrl('media') . str_replace(DS, '/', $path);
    }

    public function push()
    {
        $this->getImageProcessor()->display();
    }

    /**
     * @return TM_EasyBanner_Model_Banner_Image
     */
    public function setDestinationSubdir($dir)
    {
        $this->_destinationSubdir = $dir;
        return $this;
    }

    /**
     * @return string
     */
    public function getDestinationSubdir()
    {
        return $this->_destinationSubdir;
    }

    public function isCached()
    {
        return file_exists($this->_newFile);
    }

    public function clearCache()
    {
        $directory = Mage::getBaseDir('media') . DS.'easybanner'.DS.'cache'.DS;
        $io = new Varien_Io_File();
        $io->rmdir($directory, true);
    }
}