<?php

class ES_Newssubscribers_Model_System_Config_Templatelist
{
    public function toOptionArray()
    {
        $templates = Mage::getModel('core/email_template')->getCollection();
        $list = array(
            '' => Mage::helper('adminhtml')->__('Please choose template')
        );
        if ($templates) {
            foreach ($templates as $template) {
            	$list[$template->getTemplateId()] = $template->getTemplateCode();
            }
        }
        return $list;
    }
}