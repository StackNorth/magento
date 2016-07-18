<?php
/**
 * Created by Victor Guo
 * Date: 13-8-16
 * Time: 上午10:51
 */

class D1m_Slides_Block_Adminhtml_Groups_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function _construct()
    {
        parent::_construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'd1m_slides';
        $this->_controller = 'adminhtml_groups';

        $this->_updateButton('save', 'label', $this->__('Save Group'));
        $this->_updateButton('delete', 'label', $this->__('Delete Group'));

        $this->_addButton('saveandcontinue', array(
            'label' => $this->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            function checkSelectedValues(){
                var selValue = $('selected_slide_ids').value;
                var selectedArray = selValue.split(',');
                var checkboxes = $$('#groupSlidesGrid_table tbody input');
                for(var i=0;i<checkboxes.length;i++)
                {
                    if(selectedArray.indexOf(checkboxes[i].value)!==-1)
                    {
                        checkboxes[i].checked = 'checked';
                    }
                }
            }
            document.observe('dom:loaded', function(){
                checkSelectedValues();
                groupSlidesGridJsObject.initRowCallback = checkSelectedValues;
            });
            document.observe('click', function(e, el){
                if(el = e.findElement('#groupSlidesGrid_table tbody input')){
                    var selValue = $('selected_slide_ids').value;
                    var valueArray = [];
                    if(selValue)
                    {
                        valueArray = selValue.split(',');
                    }
                    if(el.checked)
                    {
                        if(valueArray.indexOf(el.value)==-1)
                        {
                            valueArray.push(el.value);
                        }
                    }
                    else
                    {
                        if(valueArray.indexOf(el.value)!=-1)
                        {
                            var valueIndex = valueArray.indexOf(el.value);
                            valueArray.splice(valueIndex, 1);
                        }
                    }
                    $('selected_slide_ids').value = valueArray.join(',');
                }
            });
        ";
    }

    public function getHeaderText()
    {
        return $this->__('Add/Edit Groups');
    }
}