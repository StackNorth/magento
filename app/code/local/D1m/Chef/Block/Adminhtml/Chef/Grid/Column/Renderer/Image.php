<?php
class D1m_Chef_Block_Adminhtml_Chef_Grid_Column_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{

   public function render(Varien_Object $row)   {

		$html = '<img style="width:100px;" id="' . $this->getColumn()->getId() . '"
		src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$row->getData($this->getColumn()->getIndex()) . '"';
		$html .= '/>';
	   
		return $html;

	}
}
?>
