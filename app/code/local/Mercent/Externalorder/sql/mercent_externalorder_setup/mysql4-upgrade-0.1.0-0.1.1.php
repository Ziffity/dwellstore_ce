<?php
/**
 * Setup scripts, add new columns and fills
 * its values to existing rows
 *
 */

/* @var $this Mage_Sales_Model_Mysql4_Setup */
$this->startSetup();

$this->addAttribute('creditmemo', 'mercent_adjustment_reason', array('type'=>'varchar'));

$this->endSetup();
