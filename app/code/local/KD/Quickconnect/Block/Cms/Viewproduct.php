<?php
class KD_Quickconnect_Block_Cms_Viewproduct extends Mage_Catalog_Block_Product_View
{
    public $productId;
	
	public function _construct()
    {
        $this->productId = $this->getRequest()->getParam('id', false);
		/*if(isset($this->productId) && strlen($this->productId)>0)
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
		$this->pageTitle = $processor->filter($this->getPage()->getTitle());*/
	    
    }
	public function getProductId()
    {
		return $this->productId;
    }
	
	public function getProduct()
    {
        if (!Mage::registry('product') && $this->getProductId()) {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            Mage::register('product', $product);
        }
        return Mage::registry('product');
    }
}