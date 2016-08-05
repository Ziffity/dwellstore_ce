<?php

      class Ziffity_Adminhtml_Block_Catalog_Category_Tab_Render_Fields extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
      {
 
        public function render(Varien_Object $row)
        {
			
			$index=$this->getColumn()->getIndex();
			$value=$row->getData($index);
			$productModel = Mage::getModel('catalog/product');
			  $attr = $productModel->getResource()->getAttribute($index);
			if( $attr->getFrontendInput()=='select'){
			  
			  $color_label = $attr->getSource()->getOptionText($value);
			  return $color_label;
			}
			elseif( $attr->getFrontendInput()=='boolean'){
				if($value==1)
					return "Yes";
				if($value==0)
					return "No";
				return "";
			}			
			else
				return $value;
			
       }
 
}