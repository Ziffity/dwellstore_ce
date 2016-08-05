<?php
    class DWELL_Signup_Model_Mysql4_Signup_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
    {

		public function _construct(){
			$this->_init("signup/signup");
		}

		
		/**
		 * Join fields
		 *
		 * @param string $from
		 * @param string $to
		 * @return Mage_Reports_Model_Resource_Customer_Totals_Collection
		 */
		protected function _joinFields($from = '', $to = '')
		{

			/*
			demo
			$this->getSelect()->group('tax_rate');
			$this->getSelect()->order('county','ASC');
			$this->getSelect()->order('city','ASC');
			$this->getSelect()->group('city');



			$this->getSelect()

            ->columns(array("out_of_state_sales_amount" => "SUM(IFNULL(out_of_state_sales,0))"))
            ->columns(array("non_taxable_sales_amount" => "SUM(IFNULL(non_taxable_sales,0))"))
            ->columns(array("taxed_sales_amount" => "SUM(IFNULL(taxed_sales,0))"))
            ->columns(array("total_order_amount_amount" => "SUM(IFNULL(total_order_amount,0))"))
            ->columns(array("tax_collected_amount" => "SUM(IFNULL(tax_collected,0))"));
			demo
			*/
			$this->addFieldToFilter('created_at', array('from' => $from, 'to' => $to, 'datetime' => true));
			return $this;
		}

		/**
		 * Set date range
		 *
		 * @param string $from
		 * @param string $to
		 * @return Mage_Reports_Model_Resource_Customer_Totals_Collection
		 */
		public function setDateRange($from, $to)
		{
			$this->_reset()
				->_joinFields($from, $to);
			return $this;
		}

		/**
		 * Set store filter collection
		 *
		 * @param array $storeIds
		 * @return Mage_Reports_Model_Resource_Customer_Totals_Collection
		 */
		public function setStoreIds($storeIds)
		{
			$vals = array_values($storeIds);
			if (count($storeIds) >= 1 && $vals[0] != '') {
				$this->addFieldToFilter('main_table.store_id', array('in' => (array)$storeIds));

			} else {

			}

			return $this;
		}
	 

    }
	 