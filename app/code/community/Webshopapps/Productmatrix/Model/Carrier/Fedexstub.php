<?php
/* ProductMatrix
 *
 * @category   Webshopapps
 * @package    Webshopapps_productmatrix
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


class Webshopapps_Productmatrix_Model_Carrier_Fedexstub extends Mage_Usa_Model_Shipping_Carrier_Fedex
    implements Mage_Shipping_Model_Carrier_Interface

{
	
	public function doShipmentRequest(Varien_Object $request)
    {
    	
    	return parent::_doShipmentRequest($request);
    }

	
}