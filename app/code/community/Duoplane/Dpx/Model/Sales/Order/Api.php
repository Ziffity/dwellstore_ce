<?php
class Duoplane_Dpx_Model_Sales_Order_Api extends Mage_Sales_Model_Order_Api
{

  public function ping()
  {
    return true;
  }

  public function gift_message_info($orderIncrementId)
  {
    $order = Mage::getModel('sales/order');
    $order->loadByIncrementId($orderIncrementId);
    if (!$order->getId()) {
        $this->_fault('not_exists');
    }
    $gift_message_id = $order->getGiftMessageId();
    $message = Mage::getModel('giftmessage/message');
    $result = array();
    if(!is_null($gift_message_id)) {
      $message->load((int)$gift_message_id);
      $gift_sender = $message->getData('sender');
      $gift_recipient = $message->getData('recipient');
      $gift_message = $message->getData('message');
      $result = array("sender" => $gift_sender, "recipient" => $gift_recipient, "message" => $gift_message);
    }

    return $result;
  }

}
