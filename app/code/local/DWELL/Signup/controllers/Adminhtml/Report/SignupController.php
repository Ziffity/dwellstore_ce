<?php
class DWELL_Signup_Adminhtml_Report_SignupController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Signup Report'));

        $this->loadLayout()
            ->_setActiveMenu('signup/signup')
            ->_addBreadcrumb(Mage::helper('reports')->__('Signup Report'),
                Mage::helper('reports')->__('Signup Report'))
            ->_addContent($this->getLayout()->createBlock('signup/adminhtml_report_signup'))
            ->renderLayout();
    }


    public function exportSignupCsvAction()
    {
        $fileName   = 'signup_report.csv';
        $content    = $this->getLayout()->createBlock('signup/adminhtml_report_signup_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    public function exportSignupExcelAction()
    {
        $fileName   = 'signup_report.xml';
        $content    = $this->getLayout()->createBlock('signup/adminhtml_report_signup_grid')
            ->getExcel($fileName);
        $this->_prepareDownloadResponse($fileName, $content);
    }

}