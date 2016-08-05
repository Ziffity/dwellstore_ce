<?php

class VINDESIGN_Updatepdp_Block_Adminhtml_Updatepdp_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'updatepdp';
        $this->_controller = 'adminhtml_updatepdp';
        
        $this->_updateButton('save', 'label', Mage::helper('updatepdp')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('updatepdp')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('updatepdp_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'updatepdp_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'updatepdp_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('updatepdp_data') && Mage::registry('updatepdp_data')->getId() ) {
            return Mage::helper('updatepdp')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('updatepdp_data')->getTitle()));
        } else {
            return Mage::helper('updatepdp')->__('Add Item');
        }
    }
}