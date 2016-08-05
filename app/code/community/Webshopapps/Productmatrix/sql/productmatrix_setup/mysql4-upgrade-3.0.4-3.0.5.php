<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn('shipping_productmatrix', 'udropship_vendor', "int (11) unsigned NOT NULL default '0'");
$installer->getConnection()->dropKey('shipping_productmatrix', 'dest_country');
$installer->getConnection()->addKey('shipping_productmatrix', 'dest_country',
    array('website_id','dest_country_id','dest_region_id','dest_city','dest_zip','dest_zip_to','package_id','udropship_vendor','weight_from_value',
    	  'weight_to_value','price_from_value','price_to_value','item_from_value','item_to_value','delivery_type','customer_group'), 'unique');
$installer->endSetup();