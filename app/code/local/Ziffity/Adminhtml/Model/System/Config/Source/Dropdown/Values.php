<?php
class Ziffity_Adminhtml_Model_System_Config_Source_Dropdown_Values
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
		$mine=array();
		$attributeType = Mage::getModel('eav/entity_type')->loadByCode(Mage_Catalog_Model_Product::ENTITY);
		$attributeCollection = Mage::getResourceModel('eav/entity_attribute_collection')->setEntityTypeFilter($attributeType)->getData();
		foreach($attributeCollection as $attribute){
			if($attribute['frontend_label']!=''){
				$mine[]= array('value' => $attribute['attribute_code'], 'label' => Mage::helper('adminhtml')->__($attribute['frontend_label']));
			}
		}
	   return $mine;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
   /* public function toArray()
    {
        return array(
            0 => Mage::helper('adminhtml')->__('Data1'),
            1 => Mage::helper('adminhtml')->__('Data2'),
            3 => Mage::helper('adminhtml')->__('Data3'),
        );
    }*/
}