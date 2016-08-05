<?php

class MD_QuickView_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_PATH_QUICK     = 'quickview/viewsetting/enableview';
	const XML_PATH_LIBRARY     = 'quickview/viewsetting/library';
    const XML_PATH_DIALOG_WIDTH     = 'quickview/viewsetting/dialog_width';
    const XML_PATH_DIALOG_HEIGHT    = 'quickview/viewsetting/dialog_height';
    const XML_PATH_IS_MODAL         = 'quickview/viewsetting/is_modal';
	
    public function getDialogWidth()
    {
        return Mage::getStoreConfig(self::XML_PATH_DIALOG_WIDTH);
    }	
    public function getDialogHeight()
    {
        return Mage::getStoreConfig(self::XML_PATH_DIALOG_HEIGHT);
    }
    public function getLibrary()
    {
        return Mage::getStoreConfig(self::XML_PATH_LIBRARY);
    }
    public function getQuickview()
    {
        return Mage::getStoreConfig(self::XML_PATH_QUICK);
    }
	public function getIsModal()
    {
        return (Mage::getStoreConfig(self::XML_PATH_IS_MODAL) == '1' ? 'true' : 'false');
    }
		 public function getColumgrid()
    {
        return Mage::getStoreConfig("quickview/general/number_of_itemsgrid");
    }
}