<?php
class MD_QuickView_Model_System_Config_Source_Selectmode
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'top', 'label'=>Mage::helper('adminhtml')->__('Top')),
            array('value' => 'left', 'label'=>Mage::helper('adminhtml')->__('Left')),
            array('value' => 'right', 'label'=>Mage::helper('adminhtml')->__('Right')),
            array('value' => 'bottom', 'label'=>Mage::helper('adminhtml')->__('Bottom')),
        );
    }
}
