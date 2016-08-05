<?php

class DWELL_Utility_Model_Observer
{
	
	public function setTaxClassForAllProducts() {
		
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		
		$queryRes = $read->fetchAll("select attribute_id from eav_attribute where attribute_code='taxable' and entity_type_id=4");
				
		if(!count($queryRes))
			return; 
			
		$taxAttributeId = $queryRes[0]['attribute_id'];
		
		
		$queryRes = $read->fetchAll("select v.option_id from eav_attribute_option_value v, eav_attribute_option o where v.option_id=o.option_id and v.value='TRUE' and o.attribute_id=$taxAttributeId");
				
		if(!count($queryRes))
			return; 
			
		$taxOptionId = $queryRes[0]['option_id'];
						
		
		//updating existing products
		$write->query("update catalog_product_entity_int set value=$taxOptionId where attribute_id=$taxAttributeId and entity_type_id=4");
		
		//inserting for new products		
		$products = $read->fetchAll("select entity_id from catalog_product_entity where entity_id not in (select entity_id from catalog_product_entity_int where attribute_id=$taxAttributeId and entity_type_id=4)");
		
		foreach($products as $p) {
			$entityId = $p['entity_id'];
			$write->query("insert into catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) values (4, $taxAttributeId, 0, $entityId, $taxOptionId)");
		}

	}
	
}