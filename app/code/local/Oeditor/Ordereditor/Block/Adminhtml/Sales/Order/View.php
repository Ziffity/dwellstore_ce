<?php
/**
 * Magento Order Editor Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the License Version.
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 *
 * @category   Order Editor
 * @package    Oeditor_Ordereditor
 * @copyright  Copyright (c) 2010 
 * @version    0.4.1
*/


class Oeditor_Ordereditor_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View 
{
	
	public function  __construct() {

        parent::__construct();

		$order = $this->getOrder();
		$orderId = $order->getId();
		
		$allowedInvoice = $this->_isAllowedAction('invoice');
		$canInvoice = $order->canInvoice();
	
		 
			
		 if (!$order->canInvoice()) 
		 {
		 
                        //$deleteInvoiceShipMemoPath = $this->getUrl('ordereditor/adminhtml_ordereditor/deleteInvoiceShipCreditMemo').'?order_id='.$orderId;
		        $deleteInvoiceShipMemoPath = $this->getUrl('*/ordereditor/deleteInvoiceShipCreditMemo').'?order_id='.$orderId;
		
		 	$onclickInvoiceJs = 'deleteConfirm(\''
			. Mage::helper('sales')->__('Are you sure? It will delete the created Invoice(s) and then you can create new invoice.')
			. '\', \'' . $deleteInvoiceShipMemoPath . '\');';
		 }else
		 {
		 	$onclickInvoiceJs = 'alert(\''
			. Mage::helper('sales')->__('Sorry, no invoice is created,please create new Invoice')
			. '\');';	
		 }
		 
         $_label = Mage::helper('sales')->__('Delete Invoice');
            $this->_addButton('delete_invoice', array(
                'label'     => $_label,
                'onclick'   => $onclickInvoiceJs,
                'class'     => 'go'
         ),0,15);
		 
    }
}
