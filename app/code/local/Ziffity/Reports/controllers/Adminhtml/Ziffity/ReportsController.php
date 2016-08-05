<?php

class Ziffity_Reports_Adminhtml_Ziffity_ReportsController
    extends Mage_Adminhtml_Controller_Action
{
    
    protected function _initAction()
    {
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('Custom Tax Reports'));
		
        $this->loadLayout()
            ->_setActiveMenu('report/sales')
            ->_addBreadcrumb(Mage::helper('ziffity_reports')->__('Reports'), Mage::helper('ziffity_reports')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('ziffity_reports')->__('Sales'), Mage::helper('ziffity_reports')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('ziffity_reports')->__('Custom Tax Reports'), Mage::helper('ziffity_reports')->__('My Custom Reports'));
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

	
        $gridBlock = $this->getLayout()->getBlock('adminhtml_report.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');
        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

  
    public function exportCsvAction()
    {
        $fileName   = 'Avalara_Tax_Reports.csv';
        $grid       = $this->getLayout()->createBlock('ziffity_reports/adminhtml_report_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }


    public function exportExcelAction()
    {
        $fileName   = 'Avalara_Tax_Reports.xml';
        $grid       = $this->getLayout()->createBlock('ziffity_reports/adminhtml_report_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
    }

   
    protected function _getDefaultFilterData()
    {
        return new Varien_Object();
    }
}