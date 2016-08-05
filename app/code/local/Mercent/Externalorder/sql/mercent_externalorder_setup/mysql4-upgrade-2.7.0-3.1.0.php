<?php
/**
 * Setup scripts, add new columns and fills
 * its values to existing rows
 *
 */

/* @var $this Mage_Sales_Model_Mysql4_Setup */
$this->startSetup();

$this->addAttribute('order_item', 'shipping_amount', array('type'=>'decimal'));
$this->addAttribute('order_item', 'shipping_tax_amount', array('type'=>'decimal'));

$this->endSetup();
