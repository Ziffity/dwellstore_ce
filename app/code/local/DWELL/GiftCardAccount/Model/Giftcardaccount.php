<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_GiftCardAccount
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Enter description here ...
 *
 * @method Enterprise_GiftCardAccount_Model_Resource_Giftcardaccount _getResource()
 * @method Enterprise_GiftCardAccount_Model_Resource_Giftcardaccount getResource()
 * @method string getCode()
 * @method Enterprise_GiftCardAccount_Model_Giftcardaccount setCode(string $value)
 * @method int getStatus()
 * @method Enterprise_GiftCardAccount_Model_Giftcardaccount setStatus(int $value)
 * @method string getDateCreated()
 * @method Enterprise_GiftCardAccount_Model_Giftcardaccount setDateCreated(string $value)
 * @method string getDateExpires()
 * @method Enterprise_GiftCardAccount_Model_Giftcardaccount setDateExpires(string $value)
 * @method int getWebsiteId()
 * @method Enterprise_GiftCardAccount_Model_Giftcardaccount setWebsiteId(int $value)
 * @method float getBalance()
 * @method Enterprise_GiftCardAccount_Model_Giftcardaccount setBalance(float $value)
 * @method int getState()
 * @method Enterprise_GiftCardAccount_Model_Giftcardaccount setState(int $value)
 * @method int getIsRedeemable()
 * @method Enterprise_GiftCardAccount_Model_Giftcardaccount setIsRedeemable(int $value)
 *
 * @category    Enterprise
 * @package     Enterprise_GiftCardAccount
 * @author      Magento Core Team <core@magentocommerce.com>
 */
 
 
require_once 'Enterprise/GiftCardAccount/Model/Giftcardaccount.php';

class DWELL_GiftCardAccount_Model_Giftcardaccount extends Enterprise_GiftCardAccount_Model_Giftcardaccount
{

    /**
     * DWELL - overloading addToCart to save totals (line 91).
     * 			also added display update to:
     * 			/app/design/frontend/enterprise/enterprise/template/giftcardaccount/cart/block1.phtml
     */
    public function addToCart($saveQuote = true, $quote = null)
    {
        if (is_null($quote)) {
            $quote = $this->_getCheckoutSession()->getQuote();
        }
        $website = Mage::app()->getStore($quote->getStoreId())->getWebsite();
        if ($this->isValid(true, true, $website)) {
            $cards = Mage::helper('enterprise_giftcardaccount')->getCards($quote);
            if (!$cards) {
                $cards = array();
            } else {
                foreach ($cards as $one) {
                    if ($one['i'] == $this->getId()) {
                        Mage::throwException(Mage::helper('enterprise_giftcardaccount')->__('This gift card account is already in the quote.'));
                    }
                }
            }
            $cards[] = array(
                'i'=>$this->getId(),        // id
                'c'=>$this->getCode(),      // code
                'a'=>$this->getBalance(),   // amount
                'ba'=>$this->getBalance(),  // base amount
            );
            Mage::helper('enterprise_giftcardaccount')->setCards($quote, $cards);

            if ($saveQuote) {
                $quote->collectTotals()->save();
            }
        }

        return $this;
    }
}