<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2012 Amasty (http://www.amasty.com)
* @package Amasty_Xnotif
*/       
class Amasty_Xnotif_Block_Subscr extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amxnotif/subscr.phtml');
     
        $stockAlertTable = Mage::getSingleton('core/resource')->getTableName('productalert/stock');
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect('name');             
                    
        $select = $collection->getSelect();
        $select->joinInner(array('s'=> $stockAlertTable), 's.product_id = e.entity_id', array( 'add_date','alert_stock_id','parent_id'))
               ->where('send_count=0')
               ->where('customer_id=? OR email=?', Mage::getSingleton('customer/session')->getCustomer()->getId(), Mage::getSingleton('customer/session')->getCustomer()->getEmail())
               ->group(array('s.product_id'));
          
        $this->setSubscriptions($collection);
    }

    public function getProduct($id){
        $product = Mage::getModel('catalog/product')->load($id);
        return $product;
    }
    
    public function getRemoveUrl($id){
        return Mage::getUrl('xnotif/subscr/remove',
            array('item' => $id)
        );
    }
    
    public function getProductUrl($_order){
        if($_order->getParentId()){
             $url = $this->getProduct($_order->getParentId())->getProductUrl();
        }
        else{
            $url =  $this->getProduct($_order->getEntityId())->getProductUrl();
        }
	if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "")
        {
            $url = str_replace('http:', 'https:', $url);
        }
        return $url;
    }
}
 