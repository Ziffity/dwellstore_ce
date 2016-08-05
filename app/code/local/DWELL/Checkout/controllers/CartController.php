<?php

/**
 * Custom coupon code error messaging (DWELL-857)
 */
 
require_once 'Mage/Checkout/controllers/CartController.php';

class DWELL_Checkout_CartController extends Mage_Checkout_CartController
{

	const PROMO_CODE_ERROR_MESSAGE = "Promotional offers such as discount codes apply to select merchandise only, excluding gift cards, shipping, and taxes. These offers are nontransferable and cannot be combined with any other promotions or discounts, including but not limited to items from Artek, Cherner, Fermob, FLOS, Libratone, Marimekko, and Vitra. Offers cannot be applied to past purchases, and items cannot be held for future delivery.";
        const DWELL_CARTPAGE_PROMO_CODE_ERROR_MESSAGE = 'disclaimer_text/message/value';
    public function couponPostAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }

        try {
            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();

            if (strlen($couponCode)) {
                if ($couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->_getSession()->addSuccess(
                        $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                }
                else {
                    $message = $this->getCustomErrorMessage($couponCode);
                    $this->_getSession()->addError(
                      $message
                    );
                }
            } else {
                $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }

        $this->_goBack();
    }
    
    private function getCustomErrorMessage($couponCode) {

	  $msg = null;

	  if($this->isExistingCode($couponCode)) {
		  //$msg = $this->__(Mage::getStoreConfig(self::DWELL_CARTPAGE_PROMO_CODE_ERROR_MESSAGE));
                  $msg = $this->__($this->getDisclaimerMessage($couponCode));
	  }	
	  else {
		 $msg = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
	  }

      return $msg;
    }
    
	private function getDisclaimerMessage($couponCode) {
		
		$coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
                $disclaimer = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
                $disclaimerMsg = $disclaimer->getData('disclaimer');
               /**
                * If the disclaimer message is present at the shopping cart price rules then return it.
                * Else return default message from Dwell->Disclaimer message section
                */ 
		if(!empty($disclaimerMsg)){
			return $disclaimerMsg;
                }else{
			return Mage::getStoreConfig(self::DWELL_CARTPAGE_PROMO_CODE_ERROR_MESSAGE);
                }
	}
        
	private function isExistingCode($couponCode) {
		
		$coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
	    
		if($coupon && $coupon->getRuleId())
			return true;
		else
			return false;		
	}


	private function isNewsletterCode($couponCode) {
		
		$coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
	    $promo_rule = Mage::getModel('salesrule/rule')->load('15% off promo from newsletter subscribe', 'name');
	    $rule_id = $promo_rule->getId();
	    
		if($coupon->getRuleId() == $rule_id)
			return true;
			
		return false;			
	}
    
    public function couponPostAjaxAction()
	{
		$couponCode = (string) $this->getRequest()->getParam('coupon_code');
		$remove = (string) $this->getRequest()->getParam('is_remove');

		$isNewsletterCode = $this->isNewsletterCode($couponCode);

		// Remove the code if flag is set.
		if ($remove == "1") {
		   $this->_getQuote()->setCouponCode('');
		   $this->_getQuote()->collectTotals()->save();
		   $result = Mage::helper('core')->jsonEncode(
        		array('status' => 'removed', 'msg' => 'Code has been removed.')
        	);
        	print $result;
        	die;
        }
        
        
		if (!strlen($couponCode)) {
			$result = Mage::helper('core')->jsonEncode(
				array('status' => 'error', 'msg' => 'No code to apply.')
			);
			print $result;
			die;
		}
		
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        $result = false;

        if (strlen($oldCouponCode)) {
        	$result = Mage::helper('core')->jsonEncode(
        		array('status' => 'applied', 'msg' => 'Quote has code applied.')
        	);
        }
        
        if (!$result) {
        	try {
            	$this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            	$this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                	->collectTotals()
                	->save();

            	if (strlen($couponCode)) {
                	if ($couponCode == $this->_getQuote()->getCouponCode()) {
                    	$result = Mage::helper('core')->jsonEncode(
                    		array('status' => 'success', 'msg' => 'Code applied.')
                    	);
                	}
                	else {
                   		$message = $this->getCustomErrorMessage($couponCode);
                    	
						if($isNewsletterCode)
							$result = Mage::helper('core')->jsonEncode(
	                    		array('status' => 'error', 'msg' => 'Code not applied', 'newslettercode' => 1)
	                    	);
						else								
							$result = Mage::helper('core')->jsonEncode(
                    			array('status' => 'error', 'msg' => $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode)))
                    		);
                	}
	            }
			} catch (Mage_Core_Exception $e) {
            	
				if($isNewsletterCode)
					$result = Mage::helper('core')->jsonEncode(
                		array('status' => 'error', 'msg' => 'Code not applied', 'newslettercode' => 1)
                	);
				else
					$result = Mage::helper('core')->jsonEncode(
            			array('status' => 'error', 'msg' => $e->getMessage() )
            			);
        	} catch (Exception $e) {
            	if($isNewsletterCode)
					$result = Mage::helper('core')->jsonEncode(
                		array('status' => 'error', 'msg' => 'Code not applied', 'newslettercode' => 1)
                	);
				else
					$result = Mage::helper('core')->jsonEncode(
            			array('status' => 'error', 'msg' => 'Cannot apply the coupon code.' )
            		);
       	 	}
       	}
		
		print $result;
		exit;
		
    }


	/**
     * Shopping cart display action
     */
    public function indexAction()
    {	
        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();

            if (!$this->_getQuote()->validateMinimumAmount()) {
                $minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                    ->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

                $warning = Mage::getStoreConfig('sales/minimum_order/description')
                    ? Mage::getStoreConfig('sales/minimum_order/description')
                    : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

                $cart->getCheckoutSession()->addNotice($warning);
            }
        }

        // Compose array of messages to add
        $messages = array();
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
                $messages[] = $message;
            }
        }

		// DWELL fix - checking Free Shipping Exclusion		
		if(! Mage::helper("dwell_utility")->checkCartItemsForFreeShipping($cart->getQuote()->getAllItems())) {
			
			$message = Mage::getModel("core/message_error");
			$message->setCode(strip_tags( Mage::getModel('cms/block')->setStoreId(Mage::app()->getStore()->getStoreId())->load("free_shipping_error_message")->getData('content') ));
			$messages[] = $message;
		}
			
        $cart->getCheckoutSession()->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_getSession()->setCartWasUpdated(true);

        Varien_Profiler::start(__METHOD__ . 'cart_display');
        $this
            ->loadLayout()
            ->_initLayoutMessages('checkout/session')
            ->_initLayoutMessages('catalog/session')
            ->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
        $this->renderLayout();
        Varien_Profiler::stop(__METHOD__ . 'cart_display');
    }
    
	/**
     * Add product to shopping cart action
     *
     * @return Mage_Core_Controller_Varien_Action
     * @throws Exception
     */
    public function addAction()
    {
        if(Mage::getSingleton('core/session')->getFormKey()== $this->getRequest()->getParam('form_key', null)){
        if (!$this->_validateFormKey()) {
            $this->_goBack();
            return;
        }
		}
		
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
    }
    
}
