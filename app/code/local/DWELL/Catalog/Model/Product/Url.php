<?php
/**
 * 
 * DWELL-746 - setting '_ignore_category' to params to block category addition
 * to product links on category pages.
 *
 */
class DWELL_Catalog_Model_Product_Url extends Mage_Catalog_Model_Product_Url
{
    public function getProductUrl($product, $useSid = null)
    {  
        if ($useSid === null) {
            $useSid = Mage::app()->getUseSessionInUrl();
        }

        $params = array();

        // Triggers removal of category from url
        $params['_ignore_category'] = true;

        if (!$useSid) {
            $params['_nosid'] = true;
        }

        return $this->getUrl($product, $params);
    }
    
}
