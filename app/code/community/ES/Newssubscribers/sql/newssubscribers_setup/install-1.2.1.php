<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('newsletter_subscriber'),
    'newsletter_coupon_code', 'varchar(50) AFTER subscriber_confirm_code');


