<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2012 Amasty (http://www.amasty.com)
* @package Amasty_Xnotif
*/
class Amasty_Xnotif_Block_Product_View_Type_Configurable extends Amasty_Xnotif_Block_Product_View_Type_Configurable_Pure
{
    protected $_options;
    
    protected function _afterToHtml($html)
    {
        $html = parent::_afterToHtml($html);
        if ('product.info.options.configurable' == $this->getNameInLayout() && 'true' != (string)Mage::getConfig()->getNode('modules/Amasty_Stockstatus/active'))
        {
            $allProducts = $this->getProduct()->getTypeInstance(true)
                ->getUsedProducts(null, $this->getProduct());
                
            foreach ($allProducts as $product)
            {
                $key = array();
                for ($i = 0; $i < count($this->_options); $i++)
                {
                    foreach ($this->_options[$i] as $iOptionId => $productIds)
                    {
                        if (in_array($product->getId(), $productIds))
                        {
                            $key[] = $iOptionId;
                        }
                    }
                }
                $stockStatus = '';
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                if (!$product->isInStock())
                {
                    $stockStatus = Mage::helper('amxnotif')->__('Out of Stock') ;
                } 
                if ($key)
                {
                    $aStockStatus[implode(',', $key)] = array(
                        'is_in_stock'   => $product->isSaleable(),
                        'custom_status' => $stockStatus,
                        'is_qnt_0'      => (int)($product->isInStock() && $stockItem->getData('qty') <= 0),
                        'product_id'    => $product->getId(),
                        'stockalert'	=> Mage::helper('amxnotif')->getStockAlert($product, $this->helper('customer')->isLoggedIn()),
                    );
                }
            }
            foreach ($aStockStatus as $k=>$v){
                if (!$v['is_in_stock'] && !$v['custom_status']){
                    $v['custom_status'] = Mage::helper('amxnotif')->__('Out of Stock');
                    $aStockStatus[$k] = $v;
                }   
            }
            $html .= '<script type="text/javascript">var stStatus = new StockStatus(' . Zend_Json::encode($aStockStatus) . '); spConfig.loadStatus();</script>';
        }
        return $html;
    }
    
    public function getAllowProducts()
    {
        if (!$this->hasAllowProducts()) {
            $products = array();
            $allProducts = $this->getProduct()->getTypeInstance(true)
                ->getUsedProducts(null, $this->getProduct());
            foreach ($allProducts as $product) {
                /**
                * Should show all products (if setting set to Yes), but not allow "out of stock" to be added to cart
                */
                    if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_DISABLED)
                    {
                        $products[] = $product;
                    }
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }
    
    public function getJsonConfig()
    {
        $jsonConfig = parent::getJsonConfig();
        $config = Zend_Json::decode($jsonConfig);
        $i = 0;
        foreach ($config['attributes'] as $key1 => $attr)
        {
            foreach ($attr['options'] as $key2 => $options)
            {
                $this->_options[$i][$options['id']] = $options['products'];
                
                /**
                * this block is to handle 1-attribute products
                */
                if (1 == count($options['products']))
                {
                    $product = Mage::getModel('catalog/product')->load($options['products'][0]);
                    if (!$product->isSaleable())
                    {
                        $statusText = Mage::helper('catalog')->__('Out of Stock');
                        $config['attributes'][$key1]['options'][$key2]['label'] .= ' (' . $statusText . ')';
                    }
                }
                
            }
            $i++;
        }
        return Zend_Json::encode($config);
    }
}