<?php

class MD_QuickView_Helper_Data extends Mage_Core_Helper_Abstract
{
	const PATH_PAGE_HEADING = 'quickview/general/heading';
	const PATH_CMS_HEADING = 'quickview/general/heading_block';
	const DEFAULT_LABEL = 'Quick View';

	public function getCmsBlockLabel()
	{
		$configValue = Mage::getStoreConfig(self::PATH_CMS_HEADING);
		return strlen($configValue) > 0 ? $configValue : self::DEFAULT_LABEL;
	}

	public function getPageLabel()
	{
		$configValue = Mage::getStoreConfig(self::PATH_PAGE_HEADING);
		return strlen($configValue) > 0 ? $configValue : self::DEFAULT_LABEL;
	}
}