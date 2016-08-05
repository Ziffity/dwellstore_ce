<?php

$installer = $this;

$installer->startSetup();

if  (Mage::helper('wsalogger')->getNewVersion() >= 8 ) {
    if  (Mage::helper('wsalogger')->getNewVersion() > 10 ) {
        $trackingAttr = array(
            'type'    	=> Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'  	=> 255,
            'comment' 	=> 'Tracking',
            'nullable' 	=> 'true',
            'default' 	=> '0');
        $installer->getConnection()->addColumn($installer->getTable('sales/order'),'tracking',$trackingAttr);
    } else {
        $installer->run("
			ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD tracking varchar(255) COMMENT 'webshopapps tracking';
		");
    }

}

$installer->endSetup();