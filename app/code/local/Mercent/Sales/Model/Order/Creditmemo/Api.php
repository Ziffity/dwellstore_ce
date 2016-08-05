<?php

class Mercent_Sales_Model_Order_Creditmemo_Api extends Mage_Sales_Model_Api_Resource
{

    /**
     * Retrive creditmemos by filters
     *
     * @param array $filters
     * @return array
     */
    public function listNew($filters = null)
    {
        throw new Exception("Mercent function cannot be called from V1 API, must use V2.");
    }

    /**
     * Confirm creditmemo received
     *
     * @param array $creditmemoIdList
     * @return boolean
     */
    public function confirmReceipt($creditmemoIdList)
    {
        throw new Exception("Mercent function cannot be called from V1 API, must use V2.");
    }

    /**
     * UnConfirm creditmemo received
     *
     * @param array $creditmemoIdList
     * @return boolean
     */
    public function unConfirmReceipt($creditmemoIncrementIdList)
    {
        throw new Exception("Mercent function cannot be called from V1 API, must use V2.");
    }
}

?>
