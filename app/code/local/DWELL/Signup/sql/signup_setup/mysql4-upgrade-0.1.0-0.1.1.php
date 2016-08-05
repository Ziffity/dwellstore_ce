<?php
$installer = $this;
$installer->startSetup();

$this->getConnection()->addKey(
  'dwell_signup',
  'newsletter_subscriber_id',
  'newsletter_subscriber_id',
  'unique');

$installer->endSetup();