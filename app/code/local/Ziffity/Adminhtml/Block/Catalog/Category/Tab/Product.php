<?php
 class Ziffity_Adminhtml_Block_Catalog_Category_Tab_Product extends Mage_Adminhtml_Block_Catalog_Category_Tab_Product
{
	function getAttributeLabel($attribute_code){
			$attributeCollection=Mage::getSingleton('adminhtml/session')->getAttributeCollection();
			foreach($attributeCollection as $attribute){
				if($attribute['attribute_code']==$attribute_code)
					return  $attribute['frontend_label'];
			}		
	}
	protected function _toHtml(){
		$html="";
		$configValueOne = explode(",",Mage::getStoreConfig('attributeselection/attributeselection_group/levelone_dropdown'));
		$configValueTwo = explode(",",Mage::getStoreConfig('attributeselection/attributeselection_group/leveltwo_dropdown'));
                $configValueLevelThree = explode(",",Mage::getStoreConfig('attributeselection/attributeselection_group/levelthree_dropdown'));
	if(Mage::registry('fields-key')!=1){	
	//$productAttrs = Mage::getResourceModel('catalog/product_attribute_collection')->addFieldToFilter('frontend_input', array('eq'=>'text'))->getData();
		$attributeType = Mage::getModel('eav/entity_type')->loadByCode(Mage_Catalog_Model_Product::ENTITY);
		//$attributeCollection = Mage::getResourceModel('eav/entity_attribute_collection')->setEntityTypeFilter($attributeType)->addFieldToFilter('frontend_input', array('in'=>array('text','textarea')))->getData();
		$attributeCollection = Mage::getResourceModel('eav/entity_attribute_collection')->setEntityTypeFilter($attributeType)->getData();
		// echo "<pre>";
		// var_dump($attributeCollection = Mage::getResourceModel('eav/entity_attribute_collection')->setEntityTypeFilter($attributeType)->getData());
		// exit;
		$attributes=explode(",",Mage::getSingleton('adminhtml/session')->getExtendAttributes());
		$html="<div id='category_prod_filter'><h3>Select Atrributes</h3><form method=get id=person-example><select multiple id='attribute' name='attribute[]'>";
		$html.="  <optgroup label='Level 1'>";
		foreach($attributeCollection as $attribute){
		
			$isSelect="";
			if(!in_array($attribute['attribute_code'],$configValueOne))
				continue;
			if($attribute['frontend_label']!=''){
				if(in_array($attribute['attribute_code'],$attributes))
					$isSelect="selected";
			$html.="<option $isSelect value=$attribute[attribute_code]>$attribute[frontend_label]</option>";
			}
		}
		$html.="</optgroup><optgroup label='Level 2'>"	;
		foreach($attributeCollection as $attribute){
		
			$isSelect="";
			if(!in_array($attribute['attribute_code'],$configValueTwo))
				continue;
			if($attribute['frontend_label']!=''){
				if(in_array($attribute['attribute_code'],$attributes))
					$isSelect="selected";
			$html.="<option $isSelect value=$attribute[attribute_code]>$attribute[frontend_label]</option>";
			}
		}
		
                $html.="</optgroup><optgroup label='Level 3'>"	;
		foreach($attributeCollection as $attribute){
		
			$isSelect="";
			if(!in_array($attribute['attribute_code'],$configValueLevelThree))
				continue;
			if($attribute['frontend_label']!=''){
				if(in_array($attribute['attribute_code'],$attributes))
					$isSelect="selected";
			$html.="<option $isSelect value=$attribute[attribute_code]>$attribute[frontend_label]</option>";
			}
		}
                
		$html.="</optgroup></select> <button title=Reset Filter type=button class=scalable  onclick=catalog_category_productsJsObject.fieldsFilter() style=''><span><span><span>Update Fields</span></span></span></button> </form> <script>  jQuery(function(){jQuery('#attribute').multiselect(); }); </script></div>".parent::_toHtml();
		Mage::getSingleton('adminhtml/session')->setAttributeCollection($attributeCollection);
	}
	else{
	Mage::unregister('fields-key');
		$html=parent::_toHtml();		
	}
	return $html;
	}

  protected function _prepareCollection()
    {
        if ($this->getCategory()->getId()) {
            $this->setDefaultFilter(array('in_category'=>1));
        }
				
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('*')
           /*  ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price') */
            ->addStoreFilter($this->getRequest()->getParam('store'))
            ->joinField('position',
                'catalog/category_product',
                'position',
                'product_id=entity_id',
                'category_id='.(int) $this->getRequest()->getParam('id', 0),
                'left');
		
		 if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }
        $this->setCollection($collection);

        if ($this->getCategory()->getProductsReadonly()) {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
        }

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
	
	
    protected function _prepareColumns()
    {
        if (!$this->getCategory()->getProductsReadonly()) {
            $this->addColumn('in_category', array(
                'header_css_class' => 'a-center',
                'type'      => 'checkbox',
                'name'      => 'in_category',
                'values'    => $this->_getSelectedProducts(),
                'align'     => 'center',
                'index'     => 'entity_id'
            ));
        }

        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'entity_id'
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name'
        ));
        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => '80',
            'index'     => 'sku'
        ));
  
		
		
        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $this->addColumn('qty',
                array(
                    'header'=> Mage::helper('catalog')->__('Qty'),
                    'width' => '100px',
                    'type'  => 'number',
                    'index' => 'qty',
            ));
        }
		
		 $this->addColumn('type',
            array(
                'header'=> Mage::helper('catalog')->__('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));
		

		
       
		
        $this->addColumn('position', array(
            'header'    => Mage::helper('catalog')->__('Position'),
            'width'     => '1',
            'type'      => 'number',
            'index'     => 'position',
            'editable'  => !$this->getCategory()->getProductsReadonly()
            
        ));
  
		if(Mage::getSingleton('adminhtml/session')->getExtendAttributes()){

		$attributes=explode(",",Mage::getSingleton('adminhtml/session')->getExtendAttributes());
        
		foreach($attributes as $attribute){
                   if( $this->getAttributeLabel($attribute)=="Price")
                     $str= (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
                 else 
                    $str="";   
                 
                   $this->addColumn($attribute, array(
                            'header'    => Mage::helper('catalog')->__($this->getAttributeLabel($attribute)),
                            'index'     => $attribute,
                            'renderer'  => 'Ziffity_Adminhtml_Block_Catalog_Category_Tab_Render_Fields',
                            'currency_code' => $str,
        ));
		}
		}
	
		$data=Mage::getSingleton('adminhtml/session')->getAttributeValue();
	

        return parent::_prepareColumns();
    }

    

}

