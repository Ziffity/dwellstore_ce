<?php

/**
 * Extend Mage Customer core API.
 * @TODO:
 *   - Create custom adapter
 *   http://www.magentocommerce.com/wiki/doc/webservices-api/custom-api
 */

class DWELL_DrupalCart_Model_Api extends Mage_Customer_Model_Api_Resource {

    /**
     * Retrive shopping cart data
     */
    public function items($customerID) {

		try {
    		return self::collectCartItems($customerID);
    	}
        catch (Exception $e) {
        	// Implement custom adapter firts
        	// and then use it like $this->_fault('not_exists');
        	// See api.xml faults
        }

    }

    /**
     * Retrive shopping cart items count.
     */
    public function totalItems($customerID) {

    	$total = 0;

		try {

    		$items = self::collectCartItems($customerID);
    		$last = array_pop($items);

			$total = $last['items_qty'];

    	}
        catch (Exception $e) {
        	// Implement custom adapter firts
        	// and then use it like $this->_fault('not_exists');
        	// See api.xml faults
        }

        return round($total);

    }

    /**
     * Custom function to retrive cart data from database
     * http://stackoverflow.com/a/19102037/258899
     */
    private function collectCartItems($customerID) {

    	$resource = new Mage_Core_Model_Resource();
		$read = $resource->getConnection('core_read');
		$select = $read->select()->from('sales_flat_quote')->where('customer_id = ?', $customerID);
		return $read->fetchAll($select);

    }

}
