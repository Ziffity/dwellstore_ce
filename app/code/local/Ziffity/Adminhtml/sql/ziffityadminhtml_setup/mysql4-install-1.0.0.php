<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('salesrule'),
        'disclaimer',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'default' => NULL,
            'comment' => 'Disclaimer Message'
        )
    );

$installer->endSetup();
?>
