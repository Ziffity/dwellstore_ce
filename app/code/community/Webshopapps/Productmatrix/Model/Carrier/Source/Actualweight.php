<?php
/**
 * WebShopApps Shipping Module
 *
 * @category    WebShopApps
 * @package     WebShopApps_Productmatrix
 * User         karen
 * Date         25/10/2013
 * Time         11:25
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license     http://www.WebShopApps.com/license/license.txt - Commercial license
 *
 */
class Webshopapps_Productmatrix_Model_Carrier_Source_Actualweight
{
    public function toOptionArray()
    {
        $prodmax = Mage::getSingleton('productmatrix/carrier_productmatrix');
        $arr = array();
        foreach ($prodmax->getAllowedMethods() as $v) {
            $arr[] = array('value'=>$v, 'label'=>$v);
        }

        return $arr;
    }
}
