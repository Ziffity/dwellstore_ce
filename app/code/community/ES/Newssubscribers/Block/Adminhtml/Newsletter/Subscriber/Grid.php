<?php

class ES_Newssubscribers_Block_Adminhtml_Newsletter_Subscriber_Grid extends Mage_Adminhtml_Block_Newsletter_Subscriber_Grid
{


    protected function _prepareColumns()
    {
	  
		$this->addColumn('action',
		array(
          'header' => Mage::helper('newssubscribers')->__('Action'),
          'width' => '100',
          'type' => 'action',
          'getter' => 'getId',
          'actions' => array(
                 array(
                      'caption' => Mage::helper('newssubscribers')->__('Resend'),
                      'url' => array('base'=> '*/*/resend'),
                      'field' => 'subscriber_id'
                    )),
          'filter' => false,
          'sortable' => false,
          'index' => 'stores',
          'is_system' => true,
		));
    
     
        parent::_prepareColumns();
    }
}
