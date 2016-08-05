<?php

require_once 'Mage/Adminhtml/controllers/Catalog/CategoryController.php';
class Ziffity_Adminhtml_Adminhtml_Catalog_CategoryController extends Mage_Adminhtml_Catalog_CategoryController
{
    

    /**
     * Grid Action
     * Display list of products related to current category
     *
     * @return void
     */
    public function gridAction()
    {
		
		$attibutes=$this->getRequest()->getParam('attribute');
                $fields=$this->getRequest()->getParam('fields');
	if($attibutes[0]!='' || $fields==1){
		Mage::getSingleton('adminhtml/session')->setExtendAttributes($attibutes[0]);
		$attributes=explode(",",$attibutes);
		if(count($attributes)>5){
			$res=array();
			$res['error']='true';
			$res['message']='Select Maximum 5 Attributes';
			$jsonData = json_encode($res);
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$this->getResponse()->setBody($jsonData);
			return;
	}
	}
	
	Mage::register('fields-key', 1);
        if (!$category = $this->_initCategory(true)) {
            return;
        }
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/catalog_category_tab_product', 'category.product.grid')
                ->toHtml()
        );
    }

   
}
