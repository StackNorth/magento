<?php
/**
 * Created by Victor Guo
 * Date: 13-8-16
 * Time: 下午1:51
 */

class D1m_Slides_Block_Adminhtml_Groups_Edit_Tab_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('groupSlidesGrid');
        $this->setDefaultSort('slide_id');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('d1m_slides/slide')->getCollection();
        $collection->getSelect()->order('slide_id');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column)
    {
        if($this->getCollection())
        {
           parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('slide_select', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'value' => 'slide_id',
            'align' => 'center',
            'index' => 'slide_id'
        ));
        $this->addColumn('slide_id', array(
            'header' => $this->__('ID'),
            'sortable' => true,
            'width' => '50px',
            'align' => 'center',
            'index' => 'slide_id'
        ));
        $this->addColumn('title', array(
            'header' => $this->__('Title'),
            'index' => 'title',
            'align' => 'left',
        ));
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/slidesgrid', array('_current' => true));
    }

    private function _getGroupSlidesData()
    {
        return Mage::registry('groupslides_data');
    }
}