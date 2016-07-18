<?php
class D1m_Course_Block_Choose extends Mage_Core_Block_Template
{

    protected function getCity()
    {
        if ( !$this->hasData('d1m_course_city') )
        {
            if ( Mage::registry('d1m_course_city') ) 
            {
                $this->setData('d1m_course_city', Mage::registry('d1m_course_city'));
            }
            else
            {

                $model=Mage::getResourceModel('d1m_course/course_collection');
                $attr=$model->getAttribute('province');
                $options = $attr->getSource()->getAllOptions();
                $collections1 = new Varien_Data_Collection();
                foreach ($options as $option)
                {
                    if ($option['value']=="") continue;
                    $obj=new Varien_Object();
                    $obj->setData('label',$option['label']);
                    $obj->setData('value',$option['value']);
                    $obj->setData('n_shops',implode('|', $this->getNshopByProvince($option['value'])));
                    $collections1->addItem($obj);
                }
                $this->setData('d1m_course_city', $collections1);

            }
        }
        return $this->getData('d1m_course_city');
    }
    
    public function getNshopByProvince($province){
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $model = Mage::getModel('catalog/product');
        $collection = $model->getCollection();
        $collection->addAttributeToFilter('province', $province);
        $collection->groupByAttribute('n_shop');
        $nShop = array();
        foreach ($collection as $item){
            $nShop[] = $item->getData('n_shop');
        }
        return $nShop;
    }

    public function getNShop()
    {
        if ( !$this->hasData('d1m_course_nshop') )
        {
            if ( Mage::registry('d1m_course_nshop') )
            {
                $this->setData('d1m_course_nshop', Mage::registry('d1m_course_nshop'));
            }
            else
            {
                $model=Mage::getResourceModel('d1m_course/course_collection');
                $attr=$model->getAttribute('n_shop');
                $options = $attr->getSource()->getAllOptions();
                $collections1 = new Varien_Data_Collection();
                foreach ($options as $option)
                {
                    if ($option['value']=="") continue;
                    $obj=new Varien_Object();
                    $obj->setData('label',$option['label']);
                    $obj->setData('value',$option['value']);
                    $collections1->addItem($obj);
                }
                $this->setData('d1m_course_nshop', $collections1);
    
            }
        }
        return $this->getData('d1m_course_nshop');
    }

    protected function getYear()
    {
        if ( !$this->hasData('d1m_course_year') )
        {
            if ( Mage::registry('d1m_course_year') )
            {
                $this->setData('d1m_course_year', Mage::registry('d1m_course_year'));
            }
            else
            {

                $collections1 = new Varien_Data_Collection();


                //后2个月，前2个月，共5个月
                $m=date("n");


                $y=date("Y");
                $obj=new Varien_Object();
                $obj->setData('label',$y);
                $obj->setData('value',$y);
                $collections1->addItem($obj);

                if ($m>10)
                {
                    $obj=new Varien_Object();
                    $obj->setData('label',$y+1);
                    $obj->setData('value',$y+1);
                    $collections1->addItem($obj);
                }

                if ($m<2)
                {
                    $obj=new Varien_Object();
                    $obj->setData('label',$y-1);
                    $obj->setData('value',$y-1);
                    $collections1->addItem($obj);

                }



                $this->setData('d1m_course_year', $collections1);
            }
        }
        return $this->getData('d1m_course_year');
    }
    
    protected function getMonth()
    {
        if ( !$this->hasData('d1m_course_month') )
        {
            if ( Mage::registry('d1m_course_month') )
            {
                $this->setData('d1m_course_month', Mage::registry('d1m_course_month'));
            }
            else
            {

                $collections1 = new Varien_Data_Collection();
                $m=date("n");



                for ($i=$m;$i<=$m+2;$i++)
                {

                    $j=$i;
                    if ($j>12) $j=$j-12;
                    if ($j<10) $m2='0'.$j;else $m2=$j;

                    $obj=new Varien_Object();
                    $obj->setData('label',$m2);
                    $obj->setData('value',$j);
                    $collections1->addItem($obj);
                }

                for ($i=$m-2;$i<$m;$i++)
                {
                    $j=$i;
                    if ($j<1) $j=$j+12;
                    if ($j<10) $m2='0'.$j;else $m2=$j;

                    $obj=new Varien_Object();
                    $obj->setData('label',$m2);
                    $obj->setData('value',$j);
                    $collections1->addItem($obj);
                }



                $this->setData('d1m_course_month', $collections1);

            }
        }
        return $this->getData('d1m_course_month');
    }
    protected function getCate()
    {
        if ( !$this->hasData('d1m_course_cate') )
        {
            if ( Mage::registry('d1m_course_cate') )
            {
                $this->setData('d1m_course_cate', Mage::registry('d1m_course_cate'));
            }
            else
            {


                $model=Mage::getResourceModel('d1m_course/course_collection');
                $attr=$model->getAttribute('coursetype');
                $options = $attr->getSource()->getAllOptions();
                $collections1 = new Varien_Data_Collection();
                $no=1;
                $arrmap=array();
                foreach ($options as $option)
                {
                    if ($option['value']=="") continue;
                    $obj=new Varien_Object();
                    $obj->setData('no',$no);
                    $obj->setData('label',$option['label']);
                    $obj->setData('value',$option['value']);
                    $collections1->addItem($obj);
                    $value=$option['value']; //int
                    $arrmap[$value]=$no;
                    $no++;
                }
                $this->setData('d1m_course_cate', $collections1);

            }
        }
        return $this->getData('d1m_course_cate');
    }
    




}