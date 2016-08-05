<?php

class Mercent_Sales_Model_Order_Api extends Mage_Sales_Model_Order_Api
{

    public function listIdByPoNumber($poNumber)
    {
        throw new Exception("Mercent function cannot be called from V1 API, must use V2.");
    }

    /**
     * Retrieve list of orders by filters
     * COPYING FROM CORE/MAGE CE 1.4.1.1
     * ForEach line differs and core method in EE 1.9.1.1 returns all orders
     * Correct line: foreach ($preparedFilters as $field => $value) {
     *
     * @param array $filters
     * @return array
     */
    public function items($filters = null)
    {
        throw new Exception("Mercent function cannot be called from V1 API, must use V2.");
    }

    /**
     * Create new order
     *
     * @param array $orderData
     * @return string orderIncrementId
     */
    public function create($orderData)
    {
        throw new Exception("Mercent function cannot be called from V1 API, must use V2.");
    }

    /**
     * Delete pending order
     *
     * @param $orderIncrementId
     * @return bool
     * Remove the order if there is an issue and the order is stuck in pending state, so it can be resubmitted
     */
    public function deletePending($orderIncrementId)
    {
        throw new Exception("Mercent function cannot be called from V1 API, must use V2.");
    }
}

?>
