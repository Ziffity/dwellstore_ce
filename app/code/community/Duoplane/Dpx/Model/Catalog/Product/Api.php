<?php
class Duoplane_Dpx_Model_Catalog_Product_Api extends Mage_Catalog_Model_Product_Api
{
	
	public function ping()
	{
		return true;
	}
	
	public function configure($configurable_product_id, $simple_product_ids, $configuration_attribute_ids)
	{
		$configurable_product = Mage::getModel('catalog/product')->load($configurable_product_id);
		
		/* Make sure given simple IDs are valid simple products */
		$simple_product_ids = Mage::getModel('catalog/product')->getCollection()
		                ->addFieldToFilter('entity_id', array('in' => (array) $simple_product_ids))
		                ->addFieldToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
		                ->getAllIds();
		$simple_product_ids = array_unique($simple_product_ids);
		        if (!$configurable_product->isConfigurable() || empty($simple_product_ids)) {
		            return $this;
		        }
				
		
		// $existing_product_ids = Mage::getModel('catalog/product_type_configurable')->setProduct($configurable_product)->getUsedProductCollection()
		//                 ->addAttributeToSelect('*')
		//                 ->addFilterByRequiredOptions()
		//                 ->getAllIds();
		
		        $configurable_product->setConfigurableProductsData(array_flip($simple_product_ids));
				
		        $product_type_instance = $configurable_product->getTypeInstance(true);
		        $product_type_instance->setProduct($configurable_product);
		        $attributes_data = $product_type_instance->getConfigurableAttributesAsArray();
		
		        if (empty($attributes_data)) {
					// Gather list of available attributes
		            $attribute_ids = array();
		            foreach ($product_type_instance->getSetAttributes() as $attribute) {
		                if ($product_type_instance->canUseAttribute($attribute)) {
		                    $attribute_ids[] = $attribute->getAttributeId();
		                }
		            }
		            $product_type_instance->setUsedProductAttributeIds($attribute_ids);
		            $attributes_data = $product_type_instance->getConfigurableAttributesAsArray();
		        }
				// only use the specified attributes that are actually available
		        if (!empty($configuration_attribute_ids)){
		            foreach ($attributes_data as $idx => $val) {
		                if (!in_array($val['attribute_id'], $configuration_attribute_ids)) {
		                    unset($attributes_data[$idx]);
		                }
		            }
		        }
		
		        $simple_products = Mage::getModel('catalog/product')->getCollection()->addIdFilter($simple_product_ids);
		
		        if (count($simple_products)) {
		            foreach ($attributes_data as &$attribute) {
		                $attribute['label'] = $attribute['frontend_label'];
		                $attribute_code = $attribute['attribute_code'];
		                foreach ($simple_products as $product) {
		                    $product->load($product->getId());
		                    $option_id = $product->getData($attribute_code);
		                    $is_percent = 0;
		                    $price_change = 0;
		                    // if (!empty($price_changes) && isset($price_changes[$attribute_code])) {
		                    //     $optionText = $product->getResource()
		                    //         ->getAttribute($attribute['attribute_code'])
		                    //         ->getSource()
		                    //         ->getOptionText($option_id);
		                    //     if (isset($price_changes[$attribute_code][$optionText])) {
		                    //         if (false !== strpos($price_changes[$attribute_code][$optionText], '%')) {
		                    //             $is_percent = 1;
		                    //         }
		                    //         $price_change = preg_replace('/[^0-9\.,-]/', '', $price_changes[$attribute_code][$optionText]);
		                    //         $price_change = (float) str_replace(',', '.', $price_change);
		                    //     }
		                    // }
		                    $attribute['values'][$option_id] = array(
		                        'value_index' => $option_id,
		                        'is_percent' => $is_percent,
		                        'pricing_value' => $price_change,
		                    );
		                }
		            }
		            $configurable_product->setConfigurableAttributesData($attributes_data);
		        }
				$configurable_product->save();
		        return true;
			
			
	}
	
}
	
	
?>
