<?php
$installer = $this;
$installer->startSetup();
$attribute  = array(
    'type' => 'int',
    'label'=> 'Surface on Landing Page',
    'input' => 'select',
	'source' => 'eav/entity_attribute_source_boolean',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => "1",
    'group' => "General Information"
);
$installer->addAttribute('catalog_category', 'surface_on_landing_page', $attribute);
$installer->endSetup();
?>