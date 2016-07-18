<?php
class D1m_Chef_Block_Adminhtml_Chef_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    public function initForm() {
        $model = Mage::registry('chef_chef_data');
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldSet = $form->addFieldset('chef_chef_basic_form', array(
            'legend' => $this->__('厨师基本信息'),
        ));

        //文章标题
        $fieldSet->addField('cname', 'text', array(
            'label' => $this->__('姓名'),
            'title' => $this->__('姓名'),
            'name' => 'cname',
            'required' => true,
        ));

        $fieldSet->addField('coneword', 'text', array(
            'label' => $this->__('一句话介绍'),
            'title' => $this->__('一句话介绍'),
            'name' => 'coneword',
            'required' => true,
        ));
        $fieldSet->addField('cshort', 'textarea', array(
            'label' => $this->__('从业经历'),
            'title' => $this->__('从业经历'),
            'name' => 'cshort',
            'required' => true,
        ));

        $fieldSet->addField('clong', 'textarea', array(
            'label' => $this->__('详细描述'),
            'title' => $this->__('详细描述'),
            'name' => 'clong',
            'required' => true,
        ));


        $fieldSet->addField('cregion', 'text', array( //select
            'label' => $this->__('城市'),
            'title' => $this->__('城市'),
            'name' => 'cregion',
            'required' => true,
            'value'=>'上海',
            //'values'=>array(''=>'请选择','上海'=>'上海','北京'=>'北京'),
        ));


        $fieldSet->addField('csmallpic', 'image', array(
            'label' => $this->__('小图'),
            'title' => $this->__('小图'),
            'name' => 'csmallpic',
        ));

        $fieldSet->addField('cbigpic', 'image', array(
            'label' => $this->__('大图'),
            'title' => $this->__('大图'),
            'name' => 'cbigpic',
        ));

        $fieldSet->addField('corder', 'text', array(
            'label' => $this->__('显示顺序'),
            'title' => $this->__('显示顺序'),
            'after_element_html' => '<span class="hint"><p class="note">' . $this->__('默认是0, 数值小的排前面，数值大的排后面') . '</p></span>',
            'name' => 'corder',
            'required' => true,
        ));





        //作品作者的状态
        $fieldSet->addField('cstatus', 'select', array(
            'label' => $this->__('Status'),
            'title' => $this->__('Status'),
            'name' => 'cstatus',
            'values' => array(
                array(
                    'value' => D1m_Chef_Model_Status::STATUS_ENABLED,
                    'label' => $this->__('Enabled'),
                ),
                array(
                    'value' => D1m_Chef_Model_Status::STATUS_DISABLED,
                    'label' => $this->__('Disabled'),
                ),
            ),
        ));

        //添加实体ID
        if ($this->getRequest()->getParam('id', 0))
        {
            $fieldSet->addField('chef_id', 'hidden', array(
                'name' => 'chef_id',
            ));
        }

        if (Mage::getSingleton('adminhtml/session')->getChefData())
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getChefData());
            Mage::getSingleton('adminhtml/session')->setChefData(null);
        } elseif ($model)
        {
           $form->setValues($model->getData());
        }

        return $this;
    }

}

?>
