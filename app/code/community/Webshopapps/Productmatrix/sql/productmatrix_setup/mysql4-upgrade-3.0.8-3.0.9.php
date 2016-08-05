<?php

$installer = $this;

$installer->startSetup();

if  (Mage::helper('wsalogger')->getNewVersion() >= 8 ) {
    if  (Mage::helper('wsalogger')->getNewVersion() > 10 ) {
        $volumeFrom = array(
            'type'    	=> Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'scale'     => 4,
            'precision' => 12,
            'nullable'  => false,
            'default'   => '-1.0000',
            'comment'   => 'Volume From');

        $volumeTo = array(
            'type'    	=> Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'scale'     => 4,
            'precision' => 12,
            'nullable'  => false,
            'default'   => '10000000.0000',
            'comment'   => 'Volume To');

        $installer->getConnection()->addColumn($installer->getTable('shipping_productmatrix'),'volume_from_value',$volumeFrom);
        $installer->getConnection()->addColumn($installer->getTable('shipping_productmatrix'),'volume_to_value',$volumeTo);
    } else {
        $installer->run("
			ALTER IGNORE TABLE {$this->getTable('shipping_productmatrix')}  ADD volume_from_value decimal(12,4) default -1.0000 COMMENT 'Volume From';
			ALTER IGNORE TABLE {$this->getTable('shipping_productmatrix')}  ADD volume_to_value decimal(12,4) default 10000000.0000 COMMENT 'Volume From';
		");
    }

    $installer->run("
      ALTER TABLE {$this->getTable('shipping_productmatrix')} DROP INDEX `dest_country`
");
}

if (Mage::helper('wsacommon')->getVersion() == 1.6) {
    $installer->run("
	select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='catalog_product';

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
        frontend_label	= 'Width';

    insert ignore into {$this->getTable('eav_attribute')}
        set entity_type_id 	= @entity_type_id,
        attribute_code 	= 'ship_depth',
        backend_type	= 'decimal',
        frontend_input	= 'text',
        is_required	= 0,
        is_user_defined	= 1,
        frontend_label	= 'Depth';

		");
} else {
    $installer->run("
        select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='catalog_product';

        insert ignore into {$this->getTable('eav_attribute')}
            set entity_type_id 	= @entity_type_id,
            attribute_code 	= 'ship_height',
            backend_type	= 'decimal',
            frontend_input	= 'text',
            is_required	= 0,
            is_user_defined	= 1,
            frontend_label	= 'Product Height';

        select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='ship_height';

        insert ignore into {$this->getTable('catalog_eav_attribute')}
            set attribute_id = @attribute_id,
                is_visible 	= 1,
                used_in_product_listing	= 0,
                is_filterable_in_search	= 0;

        select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='catalog_product';

        insert ignore into {$this->getTable('eav_attribute')}
            set entity_type_id 	= @entity_type_id,
            attribute_code 	= 'ship_width',
            backend_type	= 'decimal',
            frontend_input	= 'text',
            is_required	= 0,
            is_user_defined	= 1,
            frontend_label	= 'Product Width';

        select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='ship_width';

        insert ignore into {$this->getTable('catalog_eav_attribute')}
            set attribute_id 	= @attribute_id,
                is_visible 	= 1,
                used_in_product_listing	= 0,
                is_filterable_in_search	= 0;

            select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='catalog_product';

        insert ignore into {$this->getTable('eav_attribute')}
            set entity_type_id 	= @entity_type_id,
            attribute_code 	= 'ship_depth',
            backend_type	= 'decimal',
            frontend_input	= 'text',
            is_required	= 0,
            is_user_defined	= 1,
            frontend_label	= 'Product Length';

        select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='ship_depth';

        insert ignore into {$this->getTable('catalog_eav_attribute')}
            set attribute_id 	= @attribute_id,
                is_visible 	= 1,
                used_in_product_listing	= 0,
    	        is_filterable_in_search	= 0;
");
}

$entityTypeId = $installer->getEntityTypeId('catalog_product');

$attributeSetArr = $installer->getConnection()->fetchAll("SELECT attribute_set_id FROM {$this->getTable('eav_attribute_set')} WHERE entity_type_id={$entityTypeId}");

$length = $installer->getAttributeId($entityTypeId,'ship_height');
$width = $installer->getAttributeId($entityTypeId,'ship_width');
$height = $installer->getAttributeId($entityTypeId,'ship_depth');

foreach( $attributeSetArr as $attr)
{
    $attributeSetId= $attr['attribute_set_id'];

    $installer->addAttributeGroup($entityTypeId,$attributeSetId,'Shipping','99');

    $attributeGroupId = $installer->getAttributeGroupId($entityTypeId,$attributeSetId,'Shipping');

    $installer->addAttributeToGroup($entityTypeId,$attributeSetId,$attributeGroupId,$length,'99');
    $installer->addAttributeToGroup($entityTypeId,$attributeSetId,$attributeGroupId,$width,'99');
    $installer->addAttributeToGroup($entityTypeId,$attributeSetId,$attributeGroupId,$height,'99');
};

$installer->endSetup();


