<?php

class Mercent_Sales_Model_Order_Api_V2 extends Mage_Sales_Model_Order_Api_V2
{

    public function listIdByPoNumber($poNumber)
    {
        $result = array();
        $orderPaymentCollection = Mage::getModel('sales/order_payment')->getCollection()->addFieldToFilter('po_number', $poNumber);
        foreach ($orderPaymentCollection as $payment) {
            $order = Mage::getModel('sales/order')->load($payment->getParentId());
            $result[] = $this->_getAttributes($order, 'order');
        }
        return $result;
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
        //TODO: add full name logic
        $billingAliasName = 'billing_o_a';
        $shippingAliasName = 'shipping_o_a';

        $collection = Mage::getModel("sales/order")->getCollection()
            ->addAttributeToSelect('*')
            ->addAddressFields()
            ->addExpressionFieldToSelect(
                'billing_firstname', "{{billing_firstname}}", array('billing_firstname'=>"$billingAliasName.firstname")
            )
            ->addExpressionFieldToSelect(
                'billing_lastname', "{{billing_lastname}}", array('billing_lastname'=>"$billingAliasName.lastname")
            )
            ->addExpressionFieldToSelect(
                'shipping_firstname', "{{shipping_firstname}}", array('shipping_firstname'=>"$shippingAliasName.firstname")
            )
            ->addExpressionFieldToSelect(
                'shipping_lastname', "{{shipping_lastname}}", array('shipping_lastname'=>"$shippingAliasName.lastname")
            )
            ->addExpressionFieldToSelect(
                    'billing_name',
                    "CONCAT({{billing_firstname}}, ' ', {{billing_lastname}})",
                    array('billing_firstname'=>"$billingAliasName.firstname", 'billing_lastname'=>"$billingAliasName.lastname")
            )
            ->addExpressionFieldToSelect(
                    'shipping_name',
                    'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}})',
                    array('shipping_firstname'=>"$shippingAliasName.firstname", 'shipping_lastname'=>"$shippingAliasName.lastname")
            );

        $preparedFilters = array();
        if (isset($filters->filter)) {
            foreach ($filters->filter as $_filter) {
                $preparedFilters[$_filter->key] = $_filter->value;
           }
        }
        if (isset($filters->complex_filter)) {
            foreach ($filters->complex_filter as $_filter) {
                $_value = $_filter->value;
                $preparedFilters[][$_filter->key] = array(
                    $_value->key => $_value->value
                );
            }
        }

        if (!empty($preparedFilters)) {
            try {
                foreach ($preparedFilters as $field => $value) {
                    if (isset($this->_attributesMap['order'][$field])) {
                        $field = $this->_attributesMap['order'][$field];
                    }

                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
            }
        }

        $result = array();

        foreach ($collection as $order) {
            $result[] = $this->_getAttributes($order, 'order');
        }

        return $result;
    }

    /**
     * Create new order
     *
     * @param array $orderData
     * @return string orderIncrementId
     */
    public function create($orderData)
    {
        $changedProductQuantity = array();

        //Get allowed currency rates
        $currencyRates = array();
        $baseToOrderRate = 1.00;
        if (isset($orderData->convert_base_to_usd) && strtolower($orderData->convert_base_to_usd) == 'true') {
            if (isset($orderData->order_currency_code) && $orderData->order_currency_code != 'USD') {
                $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
                if (in_array($orderData->order_currency_code, $allowedCurrencies)) {
                    $currencyRates = Mage::getModel('directory/currency')->getCurrencyRates('USD', $orderData->order_currency_code);
                    if (isset($currencyRates[$orderData->order_currency_code])) {
                        $baseToOrderRate = $currencyRates[$orderData->order_currency_code];
                    }
                }
            }
        }

        try {
            /*
            Load customer data and set customer and store on the sales quote
            */
            $customer = Mage::getModel('customer/customer')->load($orderData->customer_id);
            $storeId = $customer->getStoreId();
            $quote = Mage::getModel('sales/quote')->assignCustomer($customer);
            try {
                $store = $quote->getStore()->load($storeId);
                $quote->setStore($store);
                $quote->getShippingAddress()->setShippingMethod($orderData->shipping_method);
                $quote->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates();
                $quote->setQuoteCurrencyCode($orderData->order_currency_code);
                $quote->setBaseToQuoteRate($baseToOrderRate);
                $quote->setStoreToQuoteRate($baseToOrderRate);
                if (isset($orderData->convert_base_to_usd) && strtolower($orderData->convert_base_to_usd) == 'false') {
                    $quote->setBaseCurrencyCode($orderData->order_currency_code);
                }

                $cancelOutOfStock = strtolower($orderData->cancel_out_of_stock);

                /*
                Add items from order
                */
                foreach ($orderData->items as $itemData) {
                    // To load by sku, uncomment these two lines and comment out loadByAttribute
                    $productId = Mage::getModel('catalog/product')->getIdBySku($itemData->sku);
                    $product = Mage::getModel('catalog/product')->load($productId);
                    // Loads product by upc, if merchant is using upc as the Mercent sku
                    // was getting an error saying "The stock item for Product is not valid" when using what was returned by loadByAttribute
                    //$productTemp = Mage::getModel('catalog/product')->loadByAttribute('upc', $itemData->sku);
                    //$product = Mage::getModel('catalog/product')->load($productTemp->getId());

                    $stockItem = $product->getStockItem();

                    if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)
                    {
                        // This product is bundle product
                        $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product), $product);
                        foreach($selectionCollection as $option)
                        {
                            $bundleChildProduct = Mage::getModel('catalog/product')->load($option->product_id);
                            $bundleChildStockItem = $bundleChildProduct->getStockItem();

                            $originalQuantity = $bundleChildStockItem->getQty();
                            $originalIsInStock = $bundleChildStockItem->getIsInStock();

                            if (isset($orderData->is_fba) && strtolower($orderData->is_fba) == 'true') {
                                // If the order is an FBA order, increase the quantity, so even if not available the order will still go through
                                // Increments the quantity because placing the order decrements it
                                $bundleChildStockItem = $this->updateProductQuantity($bundleChildStockItem, $itemData->qty_ordered * $option->getSelectionQty());
                                $changedProductQuantity[$option->product_id] = $itemData->qty_ordered * $option->getSelectionQty();
                            }
                            else if(!$originalIsInStock || $originalQuantity < $itemData->qty_ordered * $option->getSelectionQty())
                            {
                                if($cancelOutOfStock == 'true')
                                {
                                    throw new Exception("Not all products are available in the requested quantity");
                                }
                                else
                                {
                                    // If the product is not in stock, increase quantity to allow order to be successful
                                    $bundleChildStockItem = $this->updateProductQuantity($bundleChildStockItem, $itemData->qty_ordered * $option->getSelectionQty() - $originalQuantity);
                                    $changedProductQuantity[$productId] = $itemData->qty_ordered * $option->getSelectionQty() - $originalQuantity;
                                }
                            }
                        }
                    }
                    else
                    {
                        $originalQuantity = $stockItem->getQty();
                        $originalIsInStock = $stockItem->getIsInStock();

                        if (isset($orderData->is_fba) && strtolower($orderData->is_fba) == 'true') {
                            // If the order is an FBA order, increase the quantity, so even if not available the order will still go through
                            // Increments the quantity because placing the order decrements it
                            $stockItem = $this->updateProductQuantity($stockItem, $itemData->qty_ordered);
                            $changedProductQuantity[$productId] = $itemData->qty_ordered;
                        }
                        else if(!$originalIsInStock || $originalQuantity < $itemData->qty_ordered)
                        {
                            if($cancelOutOfStock == 'true')
                            {
                                throw new Exception("Not all products are available in the requested quantity");
                            }
                            else
                            {
                                // If the product is not in stock, increase quantity to allow order to be successful
                                $stockItem = $this->updateProductQuantity($stockItem, $itemData->qty_ordered - $originalQuantity);
                                $changedProductQuantity[$productId] = $itemData->qty_ordered - $originalQuantity;
                            }
                        }
                    }
                    $quoteItem = Mage::getModel('sales/quote_item')
                        ->setProduct($product)
                        ->setQuote($quote)
                        ->setQty($itemData->qty_ordered);

                    $quote->addItem($quoteItem);
                }

                $quote->getShippingAddress()->setShippingMethod($orderData->shipping_method);
                $quote->setRecollect(true);

                $quote->collectTotals();
                $quote->save();

                /*
                Set the payment method
                */
                $quotePayment = $quote->getPayment();
                $quotePayment->setMethod($orderData->payment_method);
                if ($orderData->payment_method == 'purchaseorder') {
                    $quotePayment->setPoNumber($orderData->channel_order_id);
                }
                $quotePayment->save();
                $quote->setPayment($quotePayment);
                $quote->save();
            } catch (Exception $ex) {
                $msg = $ex->getMessage();
                $file = $ex->getFile().':'.$ex->getLine()."\n";
                $trace = $ex->getTraceAsString();
                $str = "\n$msg\n$file\n$trace\n";
                Mage::log($str);

                /*
                Remove the quote so it doesn't show as abandoned
                */
                $quote->setIsActive(false);
                $quote->delete();

                throw $ex;
            }

            /*
            Convert the quote to an order
            */
            $service = Mage::getModel('sales/service_quote', $quote);

            /*
            Remove the quote so it doesn't show as abandoned
            */
            $quote->setIsActive(false);
            $quote->delete();

            // submitAll and getOrder does not work with Enterprise 1.8, but does work for CE 1.4.1.1
            //$service->submitAll();
            //$order = $service->getOrder();
            $order = $service->submit();
            $orderId = $order->getId();

            try {
                //load order object again, if there is an issue with items
                if (count($order->getAllItems()) < count($orderData->items)) {
                    Mage::log('Order item count for Mercent submitted order number '.$orderData->channel_order_id.' is not correct.');
                    $order = Mage::getModel('sales/order')->load($orderId);
                }
                if (count($order->getAllItems()) < count($orderData->items)) {
                    Mage::log('Order item count for Mercent submitted order number '.$orderData->channel_order_id.' is not correct even after reloading order.');
                    throw new Exception("Order item count for Mercent submitted order is not correct");
                }

                $order->setCanShipPartiallyItem(false);

                /*
                Override order details, most importantly dollar amounts, from the channel order
                */
                $order->setChannelOrderId($orderData->channel_order_id)
                    ->setChannelAccountId($orderData->channel_account_id)
                    ->setChannelName($orderData->channel_name)
                    ->setCreatedAt($orderData->created_at)
                    ->setOrderCurrencyCode($orderData->order_currency_code)
                    ->setBaseToOrderRate($baseToOrderRate)
                    ->setStoreToOrderRate($baseToOrderRate)
                    ->setShippingMethod($orderData->shipping_method)
                    ->setShippingDescription($orderData->shipping_description)
                    ->setCustomerId($orderData->customer_id)
                    ->setTaxAmount($orderData->tax_amount)
                    ->setBaseTaxAmount($orderData->tax_amount * $baseToOrderRate)
                    ->setShippingAmount($orderData->shipping_amount)
                    ->setBaseShippingAmount($orderData->shipping_amount * $baseToOrderRate)
                    ->setShippingTaxAmount($orderData->shipping_tax_amount)
                    ->setBaseShippingTaxAmount($orderData->shipping_tax_amount * $baseToOrderRate)
                    ->setShippingInclTax($orderData->shipping_amount + $orderData->shipping_tax_amount)
                    ->setBaseShippingInclTax(($orderData->shipping_amount + $orderData->shipping_tax_amount) * $baseToOrderRate)
                    ->setDiscountAmount($orderData->discount_amount)
                    ->setBaseDiscountAmount($orderData->discount_amount * $baseToOrderRate)
                    ->setSubtotal($orderData->subtotal)
                    ->setBaseSubtotal($orderData->subtotal * $baseToOrderRate)
                    ->setGrandTotal($orderData->grand_total)
                    ->setBaseGrandTotal($orderData->grand_total * $baseToOrderRate)
                    ->setBillingName($orderData->billing_name)
                    ->setShippingName($orderData->shipping_name);
                if (isset($orderData->convert_base_to_usd) && strtolower($orderData->convert_base_to_usd) == 'false') {
                    $order->setBaseCurrencyCode($orderData->order_currency_code);
                }

                /*
                Override order item details, most importantly dollar amounts, from the channel order item
                */
                foreach ($order->getAllItems() as $item) {
                    foreach ($orderData->items as $key => $itemData) {

                        $productId = Mage::getModel('catalog/product')->getIdBySku($itemData->sku);
                        $product = Mage::getModel('catalog/product')->load($productId);

                        // If using Magento sku as Mercent sku, uncomment following line
                        $sku = $itemData->sku;

                        // To load the product by upc to get magento sku
                        //$productTemp = Mage::getModel('catalog/product')->loadByAttribute('upc', $itemData->sku);
                        //$sku = $productTemp->getSku();

                        if ($item->getSku() == $sku) {
                            $item->setChannelOrderItemId($itemData->channel_order_item_id)
                                ->setQtyOrdered($itemData->qty_ordered)
                                ->setOriginalPrice($itemData->original_price)
                                ->setBaseOriginalPrice($itemData->original_price * $baseToOrderRate)
                                ->setPrice($itemData->price)
                                ->setBasePrice($itemData->price * $baseToOrderRate)
                                ->setDiscountAmount($itemData->discount_amount)
                                ->setBaseDiscountAmount($itemData->discount_amount * $baseToOrderRate)
                                ->setTaxPercent($itemData->tax_percent)
                                ->setTaxAmount($itemData->tax_amount)
                                ->setBaseTaxAmount($itemData->tax_amount * $baseToOrderRate)
                                ->setRowTotal($itemData->row_total)
                                ->setBaseRowTotal($itemData->row_total * $baseToOrderRate);
                            if (isset($itemData->shipping_amount)) {
                                $item->setShippingAmount($itemData->shipping_amount);
                            }
                            if (isset($itemData->shipping_tax_amount)) {
                                $item->setShippingTaxAmount($itemData->shipping_tax_amount * $baseToOrderRate);
                            }

                            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)
                            {
                                // This product is bundle product
                                $this->processBundleInOrder($order, $item, $product);
                            }
                            $parentCount = 0;
                            $parentArr = array();
                            $configurable_product = Mage::getModel('catalog/product_type_configurable');
                            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
                            {
                                $parentArr = $configurable_product->getParentIdsByChild($product->getId());
                                $parentCount = count($parentArr);
                            }
                            if ($parentCount > 0)
                            {
                                // This product is a child of a configurable sku
                                $optionArr = array();
                                $optionArr = $item->getProductOptions();
                                $parentProduct = Mage::getModel('catalog/product')->load($parentArr[0]);
                                $configurableAttributeCollection=$parentProduct->getTypeInstance()->getConfigurableAttributes();
                                $configurableAttributeNum = 0;
                                foreach($configurableAttributeCollection as $attribute){
                                    $optionArr['attributes_info'][$configurableAttributeNum] = array('label' => $attribute->getProductAttribute()->getFrontend()->getLabel(), 'value' => $product->getAttributeText($attribute->getProductAttribute()->getAttributeCode()));
                                    $configurableAttributeNum++;
                                }
                                $item->setProductOptions($optionArr);
                            }
                            unset($orderData->items[$key]);
                            break;
                        }
                    }
                }

                /*
                Place the order
                */
                $order->save();

                /*
                Invoice the order
                */
                $this->InvoiceOrder($order);

                /*
                Set comments for payment transaction id, cba, and fba
                */
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);

                if (isset($orderData->payment_transaction_id)) {
                    $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PROCESSING, 'PaymentTransactionID: '.$orderData->payment_transaction_id, false);
                }

                if (isset($orderData->is_cba) && strtolower($orderData->is_cba) == 'true') {
                    $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PROCESSING, 'CBA Order', false);
                }

                if (isset($orderData->is_fba) && strtolower($orderData->is_fba) == 'true') {
                    //the setState function fails saying that the complete state must not be set automatically, but setData('state', 'complete') works
                    $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
                    $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE, 'FBA Order', false);
                }

                $order->save();

            } catch (Exception $ex) {
                $msg = $ex->getMessage();
                $file = $ex->getFile().':'.$ex->getLine()."\n";
                $trace = $ex->getTraceAsString();
                $str = "\n$msg\n$file\n$trace\n";
                Mage::log($str);

                /*
                Remove the order if there was a failure
                */
                //Load order from ID because possibly the order data set was the cause of exception on save
                if (isset($order)) {
                    $order->addStatusToHistory('pending', 'Order is incomplete!', false)->save;
                    //Delete the order
                    Mage::register('isSecureArea', 1);
                    $order->cancel()->save();
                    $order->delete();
                }

                throw $ex;
            }

            /*
            Remove the quote so it doesn't show as abandoned
            */
            $quote->setIsActive(false);
            $quote->delete();

        } catch (Exception $ex) {
            $msg = $ex->getMessage();
            $file = $ex->getFile().':'.$ex->getLine()."\n";
            $trace = $ex->getTraceAsString();
            $str = "\n$msg\n$file\n$trace\n";
            Mage::log($str);

            foreach ($changedProductQuantity as $productId => &$quantityIncreased) {
                $product = Mage::getModel('catalog/product')->load($productId);
                $stockItem = $product->getStockItem();

                if ($stockItem->getQty() < $quantityIncreased) {
                    $this->updateProductQuantity($stockItem, $stockItem->getQty() * -1);
                }
                else {
                    $this->updateProductQuantity($stockItem, $quantityIncreased * -1);
                }
            }

            throw $ex;
        }

        $result = $this->_getAttributes($order, 'order');
        return $result;
    }

    /**
     * Invoice Order
     *
     * @param int $orderId
     */
    private function InvoiceOrder($order)
    {
        try {
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

            foreach ($order->getAllItems() as $item) {
                //ensure bundle children are decremented
                foreach ($item->getChildrenItems() as $child) {
                    Mage::getSingleton('cataloginventory/stock')->registerItemSale($child);
                }
            }

            $invoice->register();

            $transaction = Mage::getModel('core/resource_transaction');
            $transaction->addObject($invoice);
            $transaction->addObject($invoice->getOrder());
            $transaction->save();

            $invoice->save();

        } catch (Exception $ex) {
            $msg = $ex->getMessage();
            $file = $ex->getFile().':'.$ex->getLine()."\n";
            $trace = $ex->getTraceAsString();
            $str = "\n$msg\n$file\n$trace\n";
            Mage::log($str);
            throw $ex;
        }
    }

    private function processBundleInOrder($order, $orderItem, $product)
    {
        $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);
        $optionCollection   =$product->getTypeInstance(true)->getOptionsCollection($product);
        $selectionCollection=$product->getTypeInstance(true)->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product), $product);
        $optionCollection->appendSelections($selectionCollection);

        $optionArr = array();
        $optionArr['info_buyRequest'] = array('qty' => (int)$orderItem->getQtyOrdered(), 'options' => array());

        foreach($product->getTypeInstance(true)->getOrderOptions($product) as $vTempOptionField => $vTempOptionValue ){
            $optionArr[$vTempOptionField] = $vTempOptionValue;
        }

        $orderItem->setStoreId(1)
            ->setProductType($product->getTypeId())
            ->setProductOptions($optionArr)
            ->setWeight($product->getWeight())
            ->setIsVirtual(0)
            ->setIsQtyDecimal($product->getIsQtyDecimal()?$product->getIsQtyDecimal():0);

        $order->addItem($orderItem);

        $selectionIds=array();

        $totalPrice = 0;
        $totalBundleChildren = 0;
        $percentagePrice = 1;

        foreach ($optionCollection as $option)
        {
            $obj = $option->getSelections();

            //determine total expected price and percentage cost
            foreach ($obj as $p)
            {
                $price = $p->getPrice() * $orderItem->getQtyOrdered() * $p->getSelectionQty();
                if ($p->getSpecialPrice() > 0) {
                    $price = $p->getSpecialPrice() * $orderItem->getQtyOrdered() * $p->getSelectionQty();
                }
                $totalPrice += $price;
                $totalBundleChildren++;
            }
        }
        $percentagePrice = ($orderItem->getPrice() * $orderItem->getQtyOrdered())/$totalPrice;
        $remainingBundleChildren = $totalBundleChildren;
        $remainingPrice = $totalPrice;

        foreach ($optionCollection as $option)
        {
            $obj = $option->getSelections();
            foreach ($obj as $p)
            {
                $price = $p->getPrice();
                if ($p->getSpecialPrice() > 0) {
                    $price = $p->getSpecialPrice();
                }

                //Processing bundle item
                $this->addBundleItemToOrder($order, $orderItem, $product, $p, $option, $percentagePrice, $price);
                //bundle item's sku is added to order items.
                $remainingBundleChildren--;
                $remainingPrice -= number_format($price * $percentagePrice, 2) * $orderItem->getQtyOrdered() * $p->getSelectionQty();
            }
        }
    }

    private function addBundleItemToOrder($order, $orderItem, $product, $productItem, $optionItem, $percentagePrice, $price)
    {
        //TODO: for dynamic priced bundles (not currently supported), may need to pass in price on last item
        $newBundleOrderItem = Mage::getModel('sales/order_item')->setProductId($productItem->getId())
            ->setSku($productItem->getSku())
            ->setName($productItem->getName())
            ->setWeight($productItem->getWeight())
            ->setTaxClassId($productItem->getTaxClassId())
            ->setCost($productItem->getCost())
            ->setOriginalPrice($price)
            ->setIsQtyDecimal($productItem->getIsQtyDecimal());

        $newBundleOrderItem->setProduct($productItem)
            ->setStoreId(1)
            ->setProductType($productItem->getTypeId())
            ->setPrice(number_format($price * $percentagePrice, 2))
            ->setQtyOrdered((float)$orderItem->getQtyOrdered() * (float)$productItem->getSelectionQty())
            ->setTaxAmount($orderItem->getTaxAmount)
            ->setTaxPercent($orderItem->getTaxPercent())
            ->setRowTotal(number_format($price * $percentagePrice, 2) * $orderItem->getQtyOrdered() * $productItem->getSelectionQty())
            ->setRowWeight(0);

        $attributes = array(
            'price' => $newBundleOrderItem->getPrice(),
            'qty' => $productItem->getSelectionQty(),
            'option_label' => $optionItem->getTitle(),
            'option_id' => $optionItem->getId()
        );

        $vproductOptions['info_buyRequest'] =  array("qty" => (int)$productItem->getSelectionQty(), "options" => array());

        $vproductOptions['bundle_selection_attributes'] = serialize($attributes);

        $newBundleOrderItem->setProductOptions($vproductOptions);

        $newBundleOrderItem->setParentItem($orderItem);

        $order->addItem($newBundleOrderItem);
    }

    private function updateProductQuantity($stockItem, $qty)
    {
        $stockItem->addQty($qty);
        if ($stockItem->getCanBackInStock() && $stockItem->getQty() > $stockItem->getMinQty()) {
            $stockItem->setIsInStock(true)->setStockStatusChangedAutomaticallyFlag(true);
        }
        $stockItem->save();
        return $stockItem;
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
        $isMercentSubmittedOrder = false;
        try {
            $orderToDelete = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
            if (!isset($orderToDelete) || $orderToDelete->getId() == '') {
                throw new Exception("Order does not exist.");
            }
            $orderPayment = $orderToDelete->getPayment();

            //Check that order is a Mercent submitted order and status is pending
            if ($orderToDelete->getChannelOrderId() != '' && $orderToDelete->getChannelAccountId() != '') {
                $isMercentSubmittedOrder = true;
            }
            //If the mercent order fields are empty, possibly that code hadn't been reached, but the po_number would be set
            elseif (isset($orderPayment) && $orderPayment->getPoNumber() != '') {
                $isMercentSubmittedOrder = true;
            }

            if ($isMercentSubmittedOrder == true && ($orderToDelete->getStatus() == 'pending' || $orderToDelete->getStatus() == 'canceled')) {
                //Set status in case there is an issue deleteing
                $orderToDelete->addStatusToHistory('pending', 'Order is incomplete! Will force deletion.', false);
                $orderToDelete->save();
                //Delete the order
                Mage::register('isSecureArea', 1);
                $orderToDelete->delete();
                return true;
            }
            else {
                throw new Exception("Order is either not Mercent Submitted or is not in Pending status. Is Mercent Submitted: ".$isMercentSubmittedOrder."; Status: ".$orderToDelete->getStatus());
            }
        }
        catch (Exception $ex) {
            throw $ex;
        }
        return false;
    }

}

?>
