<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2013 Amasty (http://www.amasty.com)
* @package Amasty_Xnotif
*/
class Amasty_Xnotif_Model_Product extends Mage_Catalog_Model_Product
{
     public function getCollection()
    {
        return Mage::getModel('amxnotif/mysql4_product_collection');
    }
       
  }