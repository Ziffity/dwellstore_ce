<?php

//Include Mage and run as admin
require_once 'app/Mage.php';

Mage::app()->setCurrentStore(1);

$model = Mage::getModel('mercent_productfeed/observer');
$model->runProductFeed('mercent_feed', true, false);

?>
