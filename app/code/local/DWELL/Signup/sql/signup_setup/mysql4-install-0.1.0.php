<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
drop table if exists dwell_signup;
create table dwell_signup(
  dwell_signup_id int not null auto_increment,
  newsletter_subscriber_id int not null,
  source varchar(100) default 'Footer',
  subscribed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,  
  primary key(dwell_signup_id));
SQLTEXT;

$installer->run($sql);
$installer->endSetup();