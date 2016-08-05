<?php

class Ziffity_Reports_Block_Adminhtml_Orderbreakdown_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
 
    protected $_resourceCollectionName      = 'ziffity_reports/orderbreakdown_collection';


    protected $_aggregatedColumns;

 
    public function __construct()
    {
        parent::__construct();


        $this->setPagerVisibility(false);
        $this->setUseAjax(false);
        $this->setFilterVisibility(false);

        $this->setEmptyCellLabel(Mage::helper('ziffity_reports')->__('No records found.'));

$this->setDefaultLimit(9000);
        $this->setId('mxReportsGrid');

       $this->setCountTotals(true);
    }


    public function getResourceCollectionName()
    {
        return $this->_resourceCollectionName;
    }


    public function getResourceCollection()
    {
        $resourceCollection = Mage::getResourceModel($this->getResourceCollectionName());
		
        return $resourceCollection;
    }

  
    public function getCurrentCurrencyCode()
    {
        return Mage::app()->getStore()->getBaseCurrencyCode();
    }


    public function getRate($toCurrency)
    {
        return Mage::app()->getStore()->getBaseCurrency()->getRate($toCurrency);
    }


    public function getTotals()
    {
         $result                 = parent::getTotals();
        if (!$result && $this->getCountTotals()) {
            $filterData         = $this->getFilterData();
            $totalsCollection   = $this->getResourceCollection();
            
 
            $this->_addCustomFilter(
                $totalsCollection,
                $filterData
            );

 
            $totalsCollection->isTotals(true);
  //$totalObj = Mage::getModel('reports/totals');
    //    $this->setTotals($totalObj->countTotals($this, $from, $to));
     
   $tempCollection=$this->getCollection();
   //var_dump($tempCollection->count());
	
				 $totals = new Varien_Object();
        $fields = array(
            'base_subtotal' => 0,
			'total_shipping_amount' => 0,	//actual column index, see _prepareColumns()
            'total_tax_amount' => 0,
            'discount' => 0,
			'total_refunded' => 0,
			'total_canceled' => 0,
        );
		$i=0;
        foreach ($tempCollection as $item) {
			$i=1;
            foreach($fields as $field=>$value){
                $fields[$field]+=$item->getData($field);
            }
        }
		
		foreach($fields as $field=>$value){
			if($value<1)
                $fields[$field]="0.0000";
            }
		
        //First column in the grid
        $fields['created_at']='Totals';
        $totals->setData($fields);
				
				if($i==0)
					 $this->setTotals(new Varien_Object);
				 else
					$this->setTotals($totals);
            

            $result             = parent::getTotals();
        }

        return $result;
    }

  
    protected function _prepareColumns()
    {

        $currencyCode           = $this->getCurrentCurrencyCode();
        $rate                   = $this->getRate($currencyCode);
		$helper = Mage::helper('ziffity_reports');
          $this->addColumn('period', array(
            'header'        => $helper->__('Date'),
            'index'         => 'created_at',
            'width'         => 100,
            'sortable'      => false,
            'period_type'   => $this->getPeriodType(),
            'renderer'      => 'Ziffity_Reports_Block_Adminhtml_Orderbreakdown_Grid_Render',
            'totals_label'  => $helper->__('Total'),
            'html_decorators' => array('nobr'),
        ));
		$this->addColumn('order_id', array(
            'header' => $helper->__('Order ID'),
            'index'  => 'increment_id',
			'type'      => 'number',
			 'sortable'  => false
        ));
		   
        $this->addColumn('sales_total', array(
            'header'    => $helper->__('Product Total'),
            'index'     => 'base_subtotal',
			 'type'          => 'currency',
          'currency_code' => $currencyCode,
		      'rate'          => $rate,
            'total'     => 'sum',
            'sortable'  => false
        ));
		
		 $this->addColumn('shipping', array(
            'header'    => $helper->__('Shipping'),
            'index'     => 'total_shipping_amount',
			'type'          => 'currency',
          'currency_code' => $currencyCode,
		      'rate'          => $rate,
            'total'     => 'sum',
            'sortable'  => false
        ));
		
		 $this->addColumn('tax', array(
            'header'    => $helper->__('Tax'),
            'index'     => 'total_tax_amount',
         'currency_code' => $currencyCode,
		     'rate'          => $rate,
			'type'          => 'currency',
            'total'     => 'sum',
            'sortable'  => false
        ));
			 $this->addColumn('discount', array(
            'header'    => $helper->__('Discount'),
            'index'     => 'discount',
		
           'type'          => 'currency',
		    'currency_code' => $currencyCode,
            'total'     => 'sum',
			 'rate'          => $rate,
			 
            'sortable'  => false
        ));
			
			 $this->addColumn('refunded', array(
            'header'    => $helper->__('Refunded'),
            'index'     => 'total_refunded',
		
           'currency_code' => $currencyCode,
		  'rate'          => $rate,
			'type'          => 'currency',
            'total'     => 'sum',
            'sortable'  => false
        ));
		 $this->addColumn('canceled', array(
            'header'    => $helper->__('Canceled'),
            'index'     => 'total_canceled',
          'currency_code' => $currencyCode,
		    'rate'          => $rate,
			'type'          => 'currency',
            'total'     => 'sum',
            'sortable'  => false
        ));
	   
	     	
        // add export types
        $this->addExportType('*/*/exportCsv', Mage::helper('ziffity_reports')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('ziffity_reports')->__('MS Excel XML'));

        return parent::_prepareColumns();
    }

  
    protected function _prepareCollection()
    {
        $filterData             = $this->getFilterData();
        $resourceCollection     = $this->getResourceCollection();

        $this->_addCustomFilter(
            $resourceCollection,
            $filterData
        );

        $this->setCollection($resourceCollection);

     
        if ($this->_isExport) {
            return $this;
        }


        if ($this->getCountTotals()) {
            $this->getTotals();
        }

        return parent::_prepareCollection();
    }


    protected function _addCustomFilter($collection, $filterData)
    {
		
        $collection
            ->setPeriodType($filterData->getPeriodType())
            ->setDateRange($filterData->getFrom(), $filterData->getTo())
           ->addOrderStatusFilter($filterData->getData('order_statuses'))
            ->setAggregatedColumns($this->_getAggregatedColumns());

			
        return $this;
    }

    protected function _getAggregatedColumns()
    {
        if (!isset($this->_aggregatedColumns) && $this->getColumns()) {
            $this->_aggregatedColumns = array();
            foreach ($this->getColumns() as $column) {
			
				
                if ($column->hasTotal()) {
						//echo $column->getId()."==". $column->getTotal();
                    $this->_aggregatedColumns[$column->getId()] = $column->getTotal();
                }
            }
			//exit;
        }

        return $this->_aggregatedColumns;
    }

}