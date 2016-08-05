<?php
class KD_Quickconnect_Block_Cms_View extends Mage_Core_Block_Template
{
    public $pageIdentifier;
	public $pageContent;
	public $pageTitle;
	public $html;
	
	public function _construct()
    {
        $this->pageIdentifier = $this->getRequest()->getParam('id', false);
		if(isset($this->pageIdentifier) && strlen($this->pageIdentifier)>0)
		{
			$page = Mage::getModel('cms/page')
				->setStoreId(Mage::app()->getStore()->getId())
				->load($this->pageIdentifier, 'identifier');
            
		}
		else 
		{
			$page = Mage::getSingleton('cms/page');
		}
		$this->setData('page', $page);
        $helper = Mage::helper('cms');
	    $processor = $helper->getPageTemplateProcessor();
		$this->html = $processor->filter($this->getPage()->getContent());
		$this->pageTitle = $processor->filter($this->getPage()->getTitle());
	    
    }
	public function getPageIdentifier()
    {
		return $this->pageIdentifier;
    }
    public function getPageContent()
    {
		$this->pageContent = $this->getMessagesBlock()->getGroupedHtml() . $this->html;
		return $this->pageContent;
    }
	public function getPageTitle()
    {
		return $this->pageTitle;
    }
}