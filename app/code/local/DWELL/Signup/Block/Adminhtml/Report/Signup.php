<?php

class DWELL_Signup_Block_Adminhtml_Report_Signup extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_report_signup';
		$this->_blockGroup = "signup";
        $this->_headerText = Mage::helper('signup')->__('Signup Report');
        parent::__construct();
        $this->_removeButton('add');
    }
}