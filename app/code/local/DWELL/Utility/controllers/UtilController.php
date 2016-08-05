<?php

class DWELL_Utility_UtilController extends Mage_Core_Controller_Front_Action
{

	public function setProductDefaultTaxClassAction() {
		
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		
		echo "----- Starting the setup process -----<br/>";
		
		$write->query("update eav_attribute set default_value=208 where attribute_code='taxable' and entity_type_id=4");
		
		echo "----- Setup process finished: default product taxable attribute set to 'TRUE' -----<br/>";
	}
	
	
	public function setProductTaxableAttributeValueAction() {
		
		echo "----- Starting the setup process -----<br/>";
		
		Mage::getModel("dwell_utility/observer")->setTaxClassForAllProducts();
				
		echo "----- Setup process finished: all products' taxable attribute set to 'TRUE' -----<br/>";
	}

}


?>