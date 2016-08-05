<?php

class Ziffity_Reports_Block_Adminhtml_Report_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
 
    protected $_resourceCollectionName      = 'ziffity_reports/report_collection';

	protected $_exportPageSize = 10000;
    protected $_aggregatedColumns;

 
    public function __construct()
    {
        parent::__construct();


        $this->setPagerVisibility(false);
        $this->setUseAjax(false);
        $this->setFilterVisibility(false);

        $this->setEmptyCellLabel(Mage::helper('ziffity_reports')->__('No records found.'));

		$this->setDefaultLimit(500);
        $this->setId('mxReportsGrid');

        $this->setCountTotals(false);
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

   
            if ($totalsCollection->count() < 1) {
                $this->setTotals(new Varien_Object);
            } else {
                $this->setTotals($totalsCollection->getFirstItem());
            }

            $result             = parent::getTotals();
        }

        return $result;
    }

  
    protected function _prepareColumns()
    {

        $currencyCode           = $this->getCurrentCurrencyCode();
        $rate                   = $this->getRate($currencyCode);
		$helper = Mage::helper('ziffity_reports');
         $this->addColumn('ProcessCode', array(
            'header' => $helper->__('Process Code'),
              'width' => '50px',
			   'sortable'      => false,
              'renderer'  => 'Ziffity_Reports_Block_Adminhtml_Report_Grid_Render',
			  'index'  => 'process_code'
        ));
		$this->addColumn('DocCode', array(
            'header' => $helper->__('Doc Code'),
			 'sortable'      => false,
            'index'  => 'invoice_id'
        ));
		    $this->addColumn('DocType', array(
            'header' => $helper->__('Doc Type'),
			 'sortable'      => false,
              'renderer'  => 'Ziffity_Reports_Block_Adminhtml_Report_Grid_Render',
			  'index'  => 'doc_type'
        ));
		
		$this->addColumn('DocDate', array(
            'header' => $helper->__('Doc Date'),
			 'width' => 100,
			  'sortable'      => false,
			'type'   => 'datetime',
            'index'  => 'invoice_date'
        ));
		$this->addColumn('CustomerCode', array(
            'header' => $helper->__('Customer Code'),
				 'sortable'      => false,
            'index'  => 'customer_name'
        ));
		
			$this->addColumn('SKU', array(
            'header' => $helper->__('SKU'),
				 'sortable'      => false,
            'index'  => 'itemcode'
        ));
		
		$this->addColumn('LineNo', array(
            'header' => $helper->__('Line No'),
				 'sortable'      => false,
				 'renderer'  => 'Ziffity_Reports_Block_Adminhtml_Report_Grid_Render',
            'index'  => 'itemline_number'
        ));
       
	    	$this->addColumn('itemquantity', array(
            'header' => $helper->__('Item Quantity'),
            'index'  => 'itemquantity',
			 'sortable'      => false,
			'type'=>'number'
        ));
	   
	     	$this->addColumn('itemtotal', array(
            'header' => $helper->__('Item Total'),
			'type'      => 'currency',
			'currency' => 'base_currency_code',
			 'sortable'      => false,
            'index'  => 'itemtotal'
        ));
	   
	   $this->addColumn('Amount', array(
            'header' => $helper->__('Amount'),
			'type'      => 'currency',
            'align'     => 'right',
			'currency' => 'base_currency_code',
			 'sortable'      => false,
            'index'  => 'amount'
        ));
		$this->addColumn('DestRegion', array(
            'header' => $helper->__('Dest Region'),
			 'sortable'      => false,
             'renderer'  => 'Ziffity_Reports_Block_Adminhtml_Report_Grid_Render',
            'index'  => 'shipping_region_id'
        ));
		$this->addColumn('DestPostalCode', array(
            'header' => $helper->__('Dest Postal Code'),
			 'sortable'      => false,
			 'renderer'  => 'Ziffity_Reports_Block_Adminhtml_Report_Grid_Render',
            'index'  => 'shipping_postcode',
			'type'   => 'text',
        ));
	
		
		$this->addColumn('OrigRegion', array(
            'header' => $helper->__('Orig Region'),
			 'sortable'      => false,
            'renderer'  => 'Ziffity_Reports_Block_Adminhtml_Report_Grid_Render',
            'index'  => 'region_id'
        ));
		$this->addColumn('OrigPostalCode', array(
            'header' => $helper->__('Orig PostalCode'),
			 'sortable'      => false,
			 'renderer'  => 'Ziffity_Reports_Block_Adminhtml_Report_Grid_Render',
            'index'  => 'postcode',
			'type'   => 'text',
        ));
		    $this->addColumn('IsSellerImporterOfRecord', array(
            'header' => $helper->__('IsSellerImporterOfRecord Code'),
			 'sortable'      => false,
              'renderer'  => 'Ziffity_Reports_Block_Adminhtml_Report_Grid_Render',
			  'index'  => 'is_seller_importer_of_record'
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
            //->setPeriodType($filterData->getPeriodType())
            ->setDateRange($filterData->getFrom(), $filterData->getTo())
           
            ->setAggregatedColumns($this->_getAggregatedColumns());

        return $this;
    }

    protected function _getAggregatedColumns()
    {
        if (!isset($this->_aggregatedColumns) && $this->getColumns()) {
            $this->_aggregatedColumns = array();
            foreach ($this->getColumns() as $column) {
                if ($column->hasTotal()) {
                    $this->_aggregatedColumns[$column->getId()] = $column->getTotal();
                }
            }
        }

        return $this->_aggregatedColumns;
    }

}