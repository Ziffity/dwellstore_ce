<?php

class Ziffity_Reports_Adminhtml_Orderbreakdown_ReportsController
    extends Mage_Adminhtml_Controller_Action
{
    
    protected function _initAction()
    {
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('Order Breakdown Reports'));
		
        $this->loadLayout()
            ->_setActiveMenu('report/sales')
            ->_addBreadcrumb(Mage::helper('ziffity_reports')->__('Reports'), Mage::helper('ziffity_reports')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('ziffity_reports')->__('Sales'), Mage::helper('ziffity_reports')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('ziffity_reports')->__('Order Breakdown Reports'), Mage::helper('ziffity_reports')->__('Order Breakdown Reports'));
        return $this;
    }


    protected function _initReportAction($blocks)
    {
        if (!is_array($blocks)) {
            $blocks = array($blocks);
        }
 
        $requestData    = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('filter'));
	
        $requestData    = $this->_filterDates($requestData, array('from', 'to'));
        $params         = $this->_getDefaultFilterData();
		
        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
				
                $params->setData($key, $value);
            }
        }

		//exit;
        foreach ($blocks as $block) {
            if ($block) {
			
                $block->setFilterData($params);
            }
        }
		
        return $this;
    }

  
    public function indexAction()
    {
        $this->_initAction();

	
        $gridBlock = $this->getLayout()->getBlock('adminhtml_orderbreakdown.grid');
		
		
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');
        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

  
    public function exportCsvAction()
    {
        $fileName   = 'Order_Breakdown_Reports.csv';
        $grid       = $this->getLayout()->createBlock('ziffity_reports/adminhtml_orderbreakdown_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }


    public function exportExcelAction()
    {
        $fileName   = 'Order_Breakdown_Reports.xml';
        $grid       = $this->getLayout()->createBlock('ziffity_reports/adminhtml_orderbreakdown_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
    }

   
    protected function _getDefaultFilterData()
    {
        return new Varien_Object(array(
            'from'      => "",
            'to'        => ""
        ));
    }
}