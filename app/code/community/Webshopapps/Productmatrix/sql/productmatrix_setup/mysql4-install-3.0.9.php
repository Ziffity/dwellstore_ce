<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('shipping_productmatrix')};
CREATE TABLE {$this->getTable('shipping_productmatrix')} (
  pk int(10) unsigned NOT NULL auto_increment,
  website_id int(11) NOT NULL default '0',
  dest_country_id varchar(4) NOT NULL default '0',
  dest_region_id int(10) NOT NULL default '0',
  dest_city varchar(30) NOT NULL default '',
  dest_zip varchar(10) NOT NULL default '',
  dest_zip_to varchar(10) NOT NULL default '',
  package_id varchar(30) NOT NULL default '',
  udropship_vendor int (11) unsigned NOT NULL default '0',
  weight_from_value decimal(12,4) NOT NULL default '0.0000',
  weight_to_value decimal(12,4) NOT NULL default '0.0000',
  price_from_value decimal(12,4) NOT NULL default '0.0000',
  price_to_value decimal(12,4) NOT NULL default '0.0000',
  item_from_value decimal(12,4) NOT NULL default '0.0000',
  item_to_value decimal(12,4) NOT NULL default '0.0000',
  volume_from_value decimal(12,4) NOT NULL default '0.0000',
  volume_to_value decimal(12,4) NOT NULL default '0.0000',
  price decimal(12,4) NOT NULL default '0.0000',
  algorithm varchar(255) NOT NULL default '',
  customer_group varchar(30) NOT NULL default '',
  cost decimal(12,4) NOT NULL default '0.0000',
  delivery_type varchar(255) NOT NULL default '',
  notes varchar(255) NULL,
  PRIMARY KEY(`pk`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

if  (Mage::helper('wsacommon')->getVersion() == 1.6) {

    $installer->run("
	select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='catalog_product';

	insert ignore into {$this->getTable('eav_attribute')}
	    set entity_type_id 	= @entity_type_id,
		attribute_code 	= 'package_id',
		backend_type	= 'int',
		frontend_input	= 'select',
		is_required	= 0,
		is_user_defined	= 1,
		used_in_product_listing	= 0,
		is_filterable_in_search	= 0,
		frontend_label	= 'Shipping Group';

	insert ignore into {$this->getTable('eav_attribute')}
	    set entity_type_id 	= @entity_type_id,
		attribute_code 	= 'shipping_qty',
		backend_type	= 'int',
		frontend_input	= 'text',
		is_required	= 0,
		is_user_defined	= 1,
		used_in_product_listing	= 0,
		is_filterable_in_search	= 0,
		frontend_label	= 'Shipping Qty';

	insert ignore into {$this->getTable('eav_attribute')}
        set entity_type_id 	= @entity_type_id,
        attribute_code 	= 'ship_height',
        backend_type	= 'decimal',
        frontend_input	= 'text',
        is_required	= 0,
        is_user_defined	= 1,
        used_in_product_listing	= 1,
        is_filterable_in_search	= 0,
        frontend_label	= 'Height';

    insert ignore into {$this->getTable('eav_attribute')}
        set entity_type_id 	= @entity_type_id,
        attribute_code 	= 'ship_width',
        backend_type	= 'decimal',
        frontend_input	= 'text',
        is_required	= 0,
        is_user_defined	= 1,
        used_in_product_listing	= 1,
        is_filterable_in_search	= 0,
        frontend_label	= 'Width';

    insert ignore into {$this->getTable('eav_attribute')}
        set entity_type_id 	= @entity_type_id,
        attribute_code 	= 'ship_depth',
        backend_type	= 'decimal',
        frontend_input	= 'text',
        is_required	= 0,
        is_user_defined	= 1,
        used_in_product_listing	= 1,
        is_filterable_in_search	= 0,
        frontend_label	= 'Depth';
");

} else {
    $installer->run("
    select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='catalog_product';

    insert ignore into {$this->getTable('eav_attribute')}
        set entity_type_id 	= @entity_type_id,
        attribute_code 	= 'package_id',
        backend_type	= 'int',
        frontend_input	= 'select',
        is_required	= 0,
        is_user_defined	= 1,
        frontend_label	= 'Shipping Group';

    select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='package_id';

    insert ignore into {$this->getTable('catalog_eav_attribute')}
        set attribute_id 	= @attribute_id,
            is_visible 	= 1,
            used_in_product_listing	= 0,
            is_filterable_in_search	= 0;

    insert ignore into {$this->getTable('eav_attribute')}
        set entity_type_id 	= @entity_type_id,
        attribute_code 	= 'shipping_qty',
        backend_type	= 'int',
        frontend_input	= 'text',
        is_required	= 0,
        is_user_defined	= 1,
        frontend_label	= 'Shipping Qty';

    select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='shipping_qty';

    insert ignore into {$this->getTable('catalog_eav_attribute')}
        set attribute_id 	= @attribute_id,
            is_visible 	= 1,
            used_in_product_listing	= 0,
            is_filterable_in_search	= 0;

    insert ignore into {$this->getTable('eav_attribute')}
        set entity_type_id 	= @entity_type_id,
        attribute_code 	= 'ship_height',
        backend_type	= 'decimal',
        frontend_input	= 'text',
        is_required	= 0,
        is_user_defined	= 1,
        frontend_label	= 'Height';

    select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='ship_height';

    insert ignore into {$this->getTable('catalog_eav_attribute')}
        set attribute_id 	= @attribute_id,
            is_visible 	= 1,
            used_in_product_listing	= 1,
            is_filterable_in_search	= 0;

    insert ignore into {$this->getTable('eav_attribute')}
        set entity_type_id 	= @entity_type_id,
        attribute_code 	= 'ship_width',
        backend_type	= 'decimal',
        frontend_input	= 'text',
        is_required	= 0,
        is_user_defined	= 1,
        frontend_label	= 'Width';

    select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='ship_width';

    insert ignore into {$this->getTable('catalog_eav_attribute')}
        set attribute_id 	= @attribute_id,
            is_visible 	= 1,
            used_in_product_listing	= 1,
            is_filterable_in_search	= 0;

    insert ignore into {$this->getTable('eav_attribute')}
        set entity_type_id 	= @entity_type_id,
        attribute_code 	= 'ship_depth',
        backend_type	= 'decimal',
        frontend_input	= 'text',
        is_required	= 0,
        is_user_defined	= 1,
        frontend_label	= 'Depth';

    select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='ship_depth';

    insert ignore into {$this->getTable('catalog_eav_attribute')}
        set attribute_id 	= @attribute_id,
            is_visible 	= 1,
            used_in_product_listing	= 1,
            is_filterable_in_search	= 0;
  ");
}

if  (Mage::helper('wsalogger')->getNewVersion() >= 8 ) {
    if  (Mage::helper('wsalogger')->getNewVersion() > 10 ) {
        $trackingAttr = array(
            'type'    	=> Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'  	=> 255,
            'comment' 	=> 'Tracking',
            'nullable' 	=> 'true',
            'default' 	=> '0');
        $installer->getConnection()->addColumn($installer->getTable('sales/order'),'tracking',$trackingAttr);
    } else {
        $installer->run("
			ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD tracking varchar(255) COMMENT 'webshopapps tracking';
		");
    }
}

$entityTypeId = $installer->getEntityTypeId('catalog_product');

$attributeSetArr = $installer->getConnection()->fetchAll("SELECT attribute_set_id FROM {$this->getTable('eav_attribute_set')} WHERE entity_type_id={$entityTypeId}");

$attributeId = $installer->getAttributeId($entityTypeId,'package_id');

foreach( $attributeSetArr as $attr)
{
    $attributeSetId= $attr['attribute_set_id'];

    $installer->addAttributeGroup($entityTypeId,$attributeSetId,'Shipping','99');

    $attributeGroupId = $installer->getAttributeGroupId($entityTypeId,$attributeSetId,'Shipping');

    $installer->addAttributeToGroup($entityTypeId,$attributeSetId,$attributeGroupId,$attributeId,'99');
};

$installer->endSetup();


