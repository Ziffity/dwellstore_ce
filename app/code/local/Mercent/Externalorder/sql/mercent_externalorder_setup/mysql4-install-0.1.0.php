<?php
/**
 * Setup scripts, add new columns and fills
 * its values to existing rows
 *
 */

/* @var $this Mage_Sales_Model_Mysql4_Setup */
$this->startSetup();

$this->addAttribute('order', 'channel_order_id', array('type'=>'varchar'));
$this->addAttribute('order', 'channel_account_id', array('type'=>'varchar'));
$this->addAttribute('order', 'channel_name', array('type'=>'varchar'));

$this->addAttribute('order_item', 'channel_order_item_id', array('type'=>'varchar'));

$this->addAttribute('shipment', 'sent_to_mercent', array('type'=>'varchar'));

$this->addAttribute('creditmemo', 'sent_to_mercent', array('type'=>'varchar'));

// Add column to grid table
$this->getConnection()->addColumn(
    $this->getTable('sales/order_grid'),
    'order_channelorderid',
    "varchar(255) not null default ''"
);
$this->getConnection()->addColumn(
    $this->getTable('sales/order_grid'),
    'order_channelaccountid',
    "varchar(255) not null default ''"
);
$this->getConnection()->addColumn(
    $this->getTable('sales/order_grid'),
    'order_channelname',
    "varchar(255) not null default ''"
);

// Add key to table for this field,
// it will improve the speed of searching & sorting by the field
$this->getConnection()->addKey(
    $this->getTable('sales/order_grid'),
    'order_channelorderid',
    'order_channelorderid'
);
$this->getConnection()->addKey(
    $this->getTable('sales/order_grid'),
    'order_channelaccountid',
    'order_channelaccountid'
);
$this->getConnection()->addKey(
    $this->getTable('sales/order_grid'),
    'order_channelname',
    'order_channelname'
);

$this->endSetup();
