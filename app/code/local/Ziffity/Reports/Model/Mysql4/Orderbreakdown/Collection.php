<?php

class Ziffity_Reports_Model_Mysql4_Orderbreakdown_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
 
    protected $_periodType;

     protected $_orderStatus        = null;
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

  
   public function addOrderStatusFilter($orderStatus)
    {
        $this->_orderStatus = $orderStatus;
		
        return $this;
    }
	
	
	  /**
     * Apply order status filter
     *
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    protected function _applyOrderStatusFilter()
    {
		
		  $this->getSelect()->where('state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW
                ));
		
        if (is_null($this->_orderStatus)) {
	
            return $this;
        }
		
		
        $orderStatus = explode(',',$this->_orderStatus[0]);
    
        $this->getSelect()->where('status IN(?)', $orderStatus);
        return $this;
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

		$adapter=$this->getConnection();
		$resourceAdapter=new Mage_Sales_Model_Resource_Report_Order_Createdat();
		$periodExpr = $resourceAdapter->getStoreTZOffsetQuery(
                array('main_table' => $this->getResource()->getMainTable()),
                'main_table.' . 'created_at',null, null
            );
	
	$column=array('increment_id'=>'main_table.increment_id','base_subtotal'=>'main_table.base_subtotal','total_shipping_amount'          => new Zend_Db_Expr(
                    sprintf('(%s - %s) * %s',
                        $adapter->getIfNullSql('main_table.base_shipping_amount', 0),
                        $adapter->getIfNullSql('main_table.base_shipping_canceled', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),'created_at'=>'main_table.created_at','discount' =>new Zend_Db_Expr(
                    sprintf('(ABS(%s) - %s) * %s',
                        $adapter->getIfNullSql('main_table.base_discount_amount', 0),
                        $adapter->getIfNullSql('main_table.base_discount_canceled', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )),'total_refunded' =>new Zend_Db_Expr(
                    sprintf('%s * %s',
                        $adapter->getIfNullSql('main_table.base_total_refunded', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),'total_canceled' => new Zend_Db_Expr(
                    sprintf('%s * %s',
                        $adapter->getIfNullSql('main_table.base_total_canceled', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ),'total_tax_amount'  => new Zend_Db_Expr(
                    sprintf('(%s - %s) * %s',
                        $adapter->getIfNullSql('main_table.base_tax_amount', 0),
                        $adapter->getIfNullSql('main_table.base_tax_canceled', 0),
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                ));
	
        $this->getSelect()->from(array('main_table' => $this->getResource()->getMainTable())) ->reset(Zend_Db_Select::COLUMNS)->columns($column);
	
		$this->getSelect()->where('main_table.base_subtotal > ?',0);
        return $this;
    }

    
    protected function _applyDateRangeFilter()
    {
		
	
        if (!is_null($this->_from)) {
			
            $this->_from = $this->timeShift(date( 'Y-m-d G:i:s', strtotime( $this->_from)));
			
            $this->getSelect()->where('main_table.created_at >= ?', $this->_from);
        }
        if (!is_null($this->_to)) {
            $this->_to =  $this->timeShift(date( 'Y-m-d 23:59:59', strtotime($this->_to)));
			
             $this->getSelect()->where('main_table.created_at <= ?', $this->_to);
        }
		
        return $this;
    }
	
	
	 public function timeShift($datetime)
    {
            $dateObj = new Zend_Date($datetime, null, Mage::app()->getLocale()->getLocaleCode());
            $dateObj->setTimezone(Mage::getStoreConfig('general/locale/timezone'));
            $dateObj->set($datetime, Varien_Date::DATETIME_INTERNAL_FORMAT);
            $dateObj->setTimezone('UTC');
            return $dateObj->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
       
     
    }




    protected function _beforeLoad()
    {
        $this->_initSelect();
        return parent::_beforeLoad();
    }

 
    protected function _renderFiltersBefore()
    {
		parent::_renderFiltersBefore();
        $this->_applyDateRangeFilter();
	 $this->_applyOrderStatusFilter();
	
        return $this;
    }

}