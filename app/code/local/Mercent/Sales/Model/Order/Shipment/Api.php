<?php

class Mercent_Sales_Model_Order_Shipment_Api extends Mage_Sales_Model_Order_Shipment_Api
{
    /**
     * Retrive shipments by filters
     *
     * @param array $filters
     * @return array
     */
    public function listNew($filters = null)
    {
        throw new Exception("Mercent function cannot be called from V1 API, must use V2.");
    }

    /**
     * Confirm shipment received
     *
     * @param array $shipmentIdList
     * @return boolean
     */
    public function confirmReceipt($shipmentIdList)
    {
        throw new Exception("Mercent function cannot be called from V1 API, must use V2.");
    }


    /**
     * UnConfirm shipment received
     *
     * @param array $shipmentIdList
     * @return boolean
     */
    public function unConfirmReceipt($shipmentIncrementIdList)
    {
        throw new Exception("Mercent function cannot be called from V1 API, must use V2.");
    }
}

?>
