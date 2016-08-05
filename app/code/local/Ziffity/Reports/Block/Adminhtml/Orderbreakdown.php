<?php

class Ziffity_Reports_Block_Adminhtml_Orderbreakdown
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    
    protected $_blockGroup      = 'ziffity_reports';
 
    protected $_controller      = 'adminhtml_orderbreakdown';

  
    public function __construct()
    {
 
        $this->_headerText = Mage::helper('ziffity_reports')->__('Order Breakdown Report');
   
        $this->setTemplate('report/grid/container.phtml');
  
        parent::__construct();
     
        $this->_removeButton('add');

        $this->addButton('filter_form_submit', array(
            'label'     => Mage::helper('ziffity_reports')->__('Show Report'),
            'onclick'   => 'filterFormSubmit()'
        ));
    }

   
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/index', array('_current' => true));
    }
}