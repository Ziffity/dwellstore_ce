<?php
/**
 * Dwell - adding config fetch for MyRegistry.com integration on product page.
 *
 */
class DWELL_Catalog_Helper_Data extends Mage_Catalog_Helper_Data
{
    /**
     * Config path
     */
    const DWELL_CATALOG_REGISTRY_ENABLED = 'dwell/registry/enabled';
 
    /**
     * Retrieve Registry enabled
     *
     * @return bool
     */
    public function isRegistryEnabled()
    {
        return Mage::getStoreConfig(self::DWELL_CATALOG_REGISTRY_ENABLED);
    }
    
}
