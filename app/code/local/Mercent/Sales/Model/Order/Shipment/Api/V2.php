<?php

class Mercent_Sales_Model_Order_Shipment_Api_V2 extends Mage_Sales_Model_Order_Shipment_Api_V2
{
    /**
     * Retrive shipments by filters
     *
     * @param array $filters
     * @return array
     */
    public function listNew($filters = null)
    {
        $collection = Mage::getResourceModel('sales/order_shipment_collection')
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('increment_id')
            ->addAttributeToSelect('order_id')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('total_qty')
            ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
            ->joinAttribute('order_increment_id', 'order/increment_id', 'order_id', null, 'left')
            ->join('order', 'order.entity_id=main_table.order_id', array('channel_order_id' => 'channel_order_id', 'channel_account_id' => 'channel_account_id'))
            ->addFieldToFilter('channel_order_id', array("notnull"=>true))
            ->addFieldToFilter('channel_account_id', array("notnull"=>true))
            ->addFieldToFilter('sent_to_mercent', array("null"=>true));

        $preparedFilters = array();
        if (isset($filters->filter)) {
            foreach ($filters->filter as $_filter) {
                $preparedFilters[$_filter->key] = $_filter->value;
            }
        }
        if (isset($filters->complex_filter)) {
            foreach ($filters->complex_filter as $_filter) {
                $_value = $_filter->value;
                $preparedFilters[$_filter->key] = array(
                    $_value->key => $_value->value
                );
            }
        }

        if (!empty($preparedFilters)) {
            try {
                foreach ($preparedFilters as $field => $value) {
                    if (isset($this->_attributesMap['shipment'][$field])) {
                        $field = $this->_attributesMap['shipment'][$field];
                    }

                    if (array_key_exists('in', $value) && !is_array($value['in']))
                    {
                        $value = array('in' => explode(',', $value['in']));
                    }
                    $collection->addFieldToFilter('main_table.'.$field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
            }
        }

        $result = array();

        foreach ($collection as $shipment) {
            $r = $this->_getAttributes($shipment, 'shipment');
            $shipment->load($shipment->getId());

            $r['items'] = array();
            foreach ($shipment->getAllItems() as $item) {
                $ritems = $this->_getAttributes($item, 'shipment_item');
                $orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
                $ritems['channel_order_item_id'] = $orderItem->getChannelOrderItemId();
                $r['items'][] = $ritems;
            }

            $r['tracks'] = array();
            foreach ($shipment->getAllTracks() as $track) {
                $r['tracks'][] = $this->_getAttributes($track, 'shipment_track');
            }

            $order = Mage::getModel('sales/order');
            $order->load($shipment->getOrderId());

            if ($order->getChannelOrderId() != '' && $order->getChannelAccountId() != '') {
                $r['channel_order_id'] = $order->getChannelOrderId();
                $r['channel_account_id'] = $order->getChannelAccountId();
                $r['channel_name'] = $order->getChannelName();

                $result[] = $r;
            }
            else {
                $shipment->setSentToMercent(date('Y-m-d H:i:s', time()));
                $shipment->save();
            }
        }

        return $result;
    }

    /**
     * Confirm shipment received
     *
     * @param array $shipmentIdList
     * @return boolean
     */
    public function confirmReceipt($shipmentIdList)
    {
        if (is_array($shipmentIdList)) {
            foreach ($shipmentIdList as $shipmentId) {
                $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
                $shipment->setSentToMercent(date('Y-m-d H:i:s', time()));
                $shipment->save();
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * UnConfirm shipment received
     *
     * @param array $shipmentIdList
     * @return boolean
     */
    public function unConfirmReceipt($shipmentIncrementIdList)
    {
        if (is_array($shipmentIncrementIdList)) {
            foreach ($shipmentIncrementIdList as $shipmentIncrementId) {
                $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentIncrementId);
                $shipment->setSentToMercent(null);
                $shipment->save();
            }
            return true;
        } else {
            return false;
        }
    }

}

?>
