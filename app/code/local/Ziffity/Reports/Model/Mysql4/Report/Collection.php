<?php

class Ziffity_Reports_Model_Mysql4_Report_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
 
    protected $_periodType;

 
    protected $_from;

  
    protected $_to;

    protected $_isShippingRateNonZeroOnly       = false;

    protected $_isTotals                        = false;

    protected $_aggregatedColumns               = array();

    
    public function __construct($resource = null)
    {
        $this->setModel('adminhtml/report_item');
        $this->setResourceModel('sales/order');
        $this->setConnection($this->getResource()->getReadConnection());
    }

  
    public function setPeriodType($periodType)
    {
        $this->_periodType = $periodType;
        return $this;
    }

   
    public function setDateRange($from, $to)
    {
        $this->_from = $from;
        $this->_to = $to;
        return $this;
    }

   


  
    public function setAggregatedColumns($columns)
    {
        $this->_aggregatedColumns = $columns;
        return $this;
    }


    public function isTotals($bool = null)
    {
        if (is_null($bool)) {
            return $this->_isTotals;
        }
        $this->_isTotals = $bool ? true : false;
        return $this;
    }

 

    protected function _getAggregatedColumns()
    {
        $aggregatedColumns          = array();
        foreach ($this->_aggregatedColumns as $columnId => $total) {
            $aggregatedColumns[$columnId] = $this->_getAggregatedColumn($columnId, $total);
        }
        return $aggregatedColumns;
    }

 
    protected function _getAggregatedColumn($columnId, $total)
    {
        switch ($columnId) {
            case 'shipping_rate' : {
                $expression         = "{$total}((base_shipping_amount / base_grand_total) * 100)";
            } break;
            default : {
                $expression         = "{$total}({$columnId})";
            } break;
        }

        return $expression;
    }

   
    protected function _getPeriodFormat()
    {
        $adapter = $this->getConnection();
        if ('month' == $this->_periodType) {
           // $periodFormat = 'DATE_FORMAT(main_table.created_at, \'%Y-%m\')';
            // From Magento EE 1.12 you should use the adapter's appropriate method:
             $periodFormat = $adapter->getDateFormatSql('main_table.created_at', '%Y-%m');
        } else if ('year' == $this->_periodType) {
            //$periodFormat = 'EXTRACT(YEAR FROM main_table.created_at)';
            // From Magento EE 1.12 you should use the adapter's appropriate method:
             $periodFormat = $adapter->getDateExtractSql('main_table.created_at', Varien_Db_Adapter_Interface::INTERVAL_YEAR);
        } else {
            //$periodFormat = 'main_table.created_at';
            // From Magento EE 1.12 you should use the adapter's appropriate method:
             $periodFormat = $adapter->getDateFormatSql('main_table.created_at', '%Y-%m-%d');
        }

        return $periodFormat;
    }

 
 
   public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
    
        return parent::load($printQuery, $logQuery);
    }
 
    protected function _initSelect()
    {
        $this->getSelect()->reset();

        $this->getSelect()->from(array('main_table' => $this->getResource()->getMainTable()));
		
		
		$this ->join(array('a' => 'sales/order_address'), 'main_table.entity_id = a.parent_id AND a.address_type != \'billing\'', array(
                'region'       => 'region',
				'region_id'       => 'region_id',
				'postcode' => 'postcode'
				
            ))
		
			 ->join(array('b' => 'sales/order_address'), 'main_table.entity_id = b.parent_id AND b.address_type != \'shipping\'', array(
                'shipping_region'       => 'region',
				'shipping_region_id'       => 'region_id',
				'shipping_postcode' => 'postcode'
				
            ))
			
			  ->join(array('i' => 'sales/invoice_grid'), 'main_table.entity_id = i.order_id', array(
                'invoice_date' => 'created_at',
				'customer_name' => 'billing_name',
				'amount'=>'grand_total',
				'invoice_id'=>'entity_id'
            ))
			
			  ->join(array('c' => 'sales/invoice_item'), 'i.entity_id = c.parent_id', array(
                'itemtotal' => 'row_total',
				'itemquantity' => 'qty',
				'itemcode' => 'sku'
            ));
		
		
      
		
		$this->getSelect()->where('c.row_total != ?', 'NULL');
		

        return $this;
    }

    
    protected function _applyDateRangeFilter()
    {
        if (!is_null($this->_from)) {
            $this->_from = date('Y-m-d G:i:s', strtotime($this->_from));
            $this->getSelect()->where('main_table.created_at >= ?', $this->_from);
        }
		else{
			 $this->getSelect()->where('1 >= ?', 2);
		}
        if (!is_null($this->_to)) {
            $this->_to = date('Y-m-d G:i:s', strtotime($this->_to));
            $this->getSelect()->where('main_table.created_at <= ?', $this->_to);
        }

        return $this;
    }




    protected function _beforeLoad()
    {
        $this->_initSelect();
        return parent::_beforeLoad();
    }

 
    protected function _renderFiltersBefore()
    {
        $this
            ->_applyDateRangeFilter()
           ;
        return $this;
    }

}