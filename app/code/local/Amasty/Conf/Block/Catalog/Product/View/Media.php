<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Conf
*/
class Amasty_Conf_Block_Catalog_Product_View_Media extends Mage_Catalog_Block_Product_View_Media
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function _toHtml()
    {
        if(Mage::getStoreConfig('amconf/general/use_zoom_lightbox')) {
                $this->setTemplate('amasty/amconf/media.phtml');    
        }
      
        $html = $this->renderView();
        return $html;
    }
    
}