<?php

/**
 * Event observer model
 *
 *
 */
class Mercent_Externalorder_Model_Observer
{
    /**
     * Adds virtual grid column to order grid records generation
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function addColumnToResource(Varien_Event_Observer $observer)
    {
        /* @var $resource Mage_Sales_Model_Mysql4_Order */
        $resource = $observer->getEvent()->getResource();
        $resource->addVirtualGridColumn(
            'order_channelorderid',
            'sales/order',
            array('entity_id' => 'entity_id'),
            'channel_order_id'
        );
        $resource->addVirtualGridColumn(
            'order_channelaccountid',
            'sales/order',
            array('entity_id' => 'entity_id'),
            'channel_account_id'
        );
        $resource->addVirtualGridColumn(
            'order_channelname',
            'sales/order',
            array('entity_id' => 'entity_id'),
            'channel_name'
        );
    }
}
