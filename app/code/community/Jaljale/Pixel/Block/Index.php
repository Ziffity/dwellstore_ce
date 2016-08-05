<?php   
class Jaljale_Pixel_Block_Index extends Mage_Core_Block_Template{   

	public function isAudienceEnable()
    {
        return Mage::helper('pixel')->isAudienceEnable();
    }

    public function getAudienceCode()
    {
        return Mage::helper('pixel')->getAudienceCode();
    }

    public function isTrackingEnable()
    {
        return Mage::helper('pixel')->isTrackingEnable();
    }

    public function getTrackingCode()
    {
        return Mage::helper('pixel')->getTrackingCode();
    }

    public function getCurrency()
    {
        return Mage::helper('pixel')->getCurrency();
    }


    public function getPageName()
    {
        return Mage::helper('pixel')->getPageName();
    }

     public function getOrderValue()
    {
        //$_customerId = Mage::getSingleton('customer/session')->getCustomerId();

        $lastOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order = Mage::getSingleton('sales/order');
        $order->load($lastOrderId);

        return round($order->getGrandTotal(), 2);
    }



}