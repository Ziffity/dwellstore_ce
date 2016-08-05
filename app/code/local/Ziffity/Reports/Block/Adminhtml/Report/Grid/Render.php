<?php
 class Ziffity_Reports_Block_Adminhtml_Report_Grid_Render extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function renderCss()
	{
   		return parent::renderCss() . ' a-center';
	}
	
    public function render(Varien_Object $row)
    {
        $index=$this->getColumn()->getIndex();
		
		if(strcmp($index,"itemline_number")==0){
			$current_id=$row->getData('invoice_id');
			$last_id = Mage::registry('ziffity_invoice_id');
			if(isset($last_id) && $current_id==$last_id){
				$tcount = Mage::registry('ziffity_invoice_item_count');
				Mage::unregister('ziffity_invoice_item_count');
				Mage::register('ziffity_invoice_item_count',++$tcount);
				return $tcount;
			}
			else{
				Mage::unregister('ziffity_invoice_item_count');
				Mage::unregister('ziffity_invoice_id');
				Mage::register('ziffity_invoice_item_count',1);
				Mage::register('ziffity_invoice_id',$current_id);
				return 1;
					
			}
		}
		
        if(strcmp($index,"process_code")==0)
            return "3";
 
        if(strcmp($index,"is_seller_importer_of_record")==0) 
            return "FALSE";
        if(strcmp($index,"doc_type")==0) 
            return "1";   
        if($this->getColumn()->getIndex() == 'region_id'){
            $value = $row->getData($this->getColumn()->getIndex());
            $region = Mage::getModel('directory/region')->load($value);
            return $region->getCode();
        }
        if($this->getColumn()->getIndex() == 'shipping_region_id'){
            $value = $row->getData($this->getColumn()->getIndex());
            $shipping_region = Mage::getModel('directory/region')->load($value);
            return $shipping_region->getCode();
        }
		if($this->getColumn()->getIndex() == 'shipping_postcode'){
            $value = $row->getData($this->getColumn()->getIndex());
			if($value[0] == '0'){
            	return "'".$value;
			} else {
				return $value;
			}
        }
		if($this->getColumn()->getIndex() == 'postcode'){
            $value = $row->getData($this->getColumn()->getIndex());
			if($value[0] == '0'){
            	return "'".$value;
			} else {
				return $value;
			}
        }
 
    }
}
?>