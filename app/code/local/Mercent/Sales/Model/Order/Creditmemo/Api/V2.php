<?php

class Mercent_Sales_Model_Order_Creditmemo_Api_V2 extends Mercent_Sales_Model_Order_Creditmemo_Api
{

    /**
     * Retrive creditmemos by filters
     *
     * @param array $filters
     * @return array
     */
    public function listNew($filters = null)
    {
        $collection = Mage::getResourceModel('sales/order_creditmemo_collection')
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('increment_id')
            ->addAttributeToSelect('order_id')
            ->addAttributeToSelect('created_at')
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
                    if (isset($this->_attributesMap['creditmemo'][$field])) {
                        $field = $this->_attributesMap['creditmemo'][$field];
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

        foreach ($collection as $creditmemo) {
            $r = $this->_getAttributes($creditmemo, 'creditmemo');
            $creditmemo->load($creditmemo->getId());
            $r['creditmemo_id'] = $creditmemo->getId();
            $r['mercent_adjustment_reason'] = $creditmemo->getMercentAdjustmentReason();
            $r['shipping_amount'] = $creditmemo->getShippingAmount();
            $r['shipping_tax_amount'] = $creditmemo->getShippingTaxAmount();
            $r['adjustment'] = $creditmemo->getAdjustment();

            $order = Mage::getModel('sales/order')->load($creditmemo->getOrderId());

            if ($order->getChannelOrderId() != '' && $order->getChannelAccountId() != '') {
                $r['channel_order_id'] = $order->getChannelOrderId();
                $r['channel_account_id'] = $order->getChannelAccountId();
                $r['channel_name'] = $order->getChannelName();

                foreach ($order->getAllItems() as $orderItem) {
                    $ritems = array();
                    foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                        if ($creditmemoItem->getOrderItemId() == $orderItem->getId()) {
                            $ritems = $this->_getAttributes($creditmemoItem, 'creditmemo_item');
                            //creditmemo_item_id is not useful
                            //$ritems['creditmemo_item_id'] = $creditmemoItem->getIncrementId();
                            break;
                        }
                    }
                    $ritems['channel_order_item_id'] = $orderItem->getChannelOrderItemId();
                    $ritems['order_item_id'] = $orderItem->getId();
                    $ritems['original_price'] = $orderItem->getRowTotal();
                    $ritems['original_tax_amount'] = $orderItem->getTaxAmount();
                    $ritems['original_shipping_amount'] = $orderItem->getShippingAmount();
                    $ritems['original_shipping_tax_amount'] = $orderItem->getShippingTaxAmount();
                    if (!isset($ritems['qty'])) {
                        $ritems['qty'] = 0;
                    }
                    $r['items'][] = $ritems;
                }

                $result[] = $r; //only include the order if it has channel data populated
            }
            else {
                $creditmemo->setSentToMercent(date('Y-m-d H:i:s', time()));
                $creditmemo->save();
            }
        }

        return $result;
    }

    /**
     * Confirm creditmemo received
     *
     * @param array $creditmemoIdList
     * @return boolean
     */
    public function confirmReceipt($creditmemoIdList)
    {
        if (is_array($creditmemoIdList)) {
            foreach ($creditmemoIdList as $creditmemoId) {
                $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
                $creditmemo->setSentToMercent(date('Y-m-d H:i:s', time()));
                $creditmemo->save();
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * UnConfirm creditmemo received
     *
     * @param array $creditmemoIdList
     * @return boolean
     */
    public function unConfirmReceipt($creditmemoIncrementIdList)
    {
        if (is_array($creditmemoIncrementIdList)) {
            foreach ($creditmemoIncrementIdList as $creditmemoIncrementId) {
                //there is no creditmemo loadByIncrementId, therefore calling own function with incrementId as the filter criteria
                $creditmemoId = $this->getCreditmemoIdByIncrementId($creditmemoIncrementId);
                if ($creditmemoId == 0) {
                    return false;
                }
                $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
                $creditmemo->setSentToMercent(null);
                $creditmemo->save();
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * getCreditmemoIdByIncrementId
     *
     * @param string $incrementId
     * @return int
     */
    private function getCreditmemoIdByIncrementId($incrementId)
    {
        $ids = Mage::getModel('sales/order_creditmemo')->getCollection()
            ->addAttributeToFilter('increment_id', $incrementId)
            ->getAllIds();

        if (!empty($ids)) {
            reset($ids);
            return current($ids);
        }
        return 0;
    }
}

?>
