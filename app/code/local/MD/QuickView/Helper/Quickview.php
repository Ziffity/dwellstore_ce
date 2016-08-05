<?php

class MD_QuickView_Helper_Quickview extends Mage_Core_Helper_Abstract
{
    const XML_PATH_QUICK     = 'quickview/viewsetting/enableview';
    const XML_PATH_DIALOG_WIDTH     = 'quickview/viewsetting/dialog_width';
    const XML_PATH_DIALOG_HEIGHT    = 'quickview/viewsetting/dialog_height';
    const XML_PATH_ZOOM_HEIGHT         = 'quickview/viewsetting/zoom_height';
	const XML_PATH_ZOOM_WIDTH     = 'quickview/viewsetting/zoom_width';	
	const XML_PATH_ZOOM_POSITION     = 'quickview/viewsetting/zoom_position';	
	const XML_PATH_ZOOM_COLOR     = 'quickview/viewsetting/text_color';	
	
	const XML_PATH_IMAGE_WIDTH     = 'quickview/slidersetting/product_image_width';		
	const XML_PATH_IMAGE_HEIGHT     = 'quickview/slidersetting/product_image_height';
	const XML_PATH_PAGINATION_WIDTH     = 'quickview/slidersetting/pagination_image_width';		
	const XML_PATH_PAGINATION_HEIGHT     = 'quickview/slidersetting/pagination_image_height';
	const XML_PATH_LEFT_NEXT_BUTTON     = 'quickview/slidersetting/left_next_button';	
	const XML_PATH_LEFT_PREV_BUTTON     = 'quickview/slidersetting/left_prev_button';	
	const XML_PATH_LEFT_PREV_TOP_IMAGE     = 'quickview/slidersetting/top_image';	
	
	
    public function getTOP()
    {	if(Mage::getStoreConfig(self::XML_PATH_LEFT_PREV_TOP_IMAGE)==""){
			return 165;
		}else{
			return Mage::getStoreConfig(self::XML_PATH_LEFT_PREV_TOP_IMAGE);
		}
    }		
    public function getLeftNext()
    {	if(Mage::getStoreConfig(self::XML_PATH_LEFT_NEXT_BUTTON)==""){
			return 103.5;
		}else{
			return Mage::getStoreConfig(self::XML_PATH_LEFT_NEXT_BUTTON);
		}
    }		
    public function getLeftPrev()
    {	if(Mage::getStoreConfig(self::XML_PATH_LEFT_PREV_BUTTON)==""){
			return 386;
		}else{
			return Mage::getStoreConfig(self::XML_PATH_LEFT_PREV_BUTTON);
		}
    }	
    public function getPaginationHeight()
    {	if(Mage::getStoreConfig(self::XML_PATH_PAGINATION_HEIGHT)==""){
			return 56;
		}else{
			return Mage::getStoreConfig(self::XML_PATH_PAGINATION_HEIGHT);
		}
    }		
    public function getPaginationWidth()
    {	if(Mage::getStoreConfig(self::XML_PATH_PAGINATION_WIDTH)==""){
			return 56;
		}else{
			return Mage::getStoreConfig(self::XML_PATH_PAGINATION_WIDTH);
		}
    }	
    public function getImageWidth()
    {	if(Mage::getStoreConfig(self::XML_PATH_IMAGE_WIDTH)==""){
			return 310;
		}else{
			return Mage::getStoreConfig(self::XML_PATH_IMAGE_WIDTH);
		}
    }
    public function getImageHeight()
    {	if(Mage::getStoreConfig(self::XML_PATH_IMAGE_HEIGHT)==""){
			return 310;
		}else{
			return Mage::getStoreConfig(self::XML_PATH_IMAGE_HEIGHT);
		}
    }	    
	
	public function getDialogWidth()
    {	if(Mage::getStoreConfig(self::XML_PATH_DIALOG_WIDTH)==""){
			return 890;
		}else{
			return Mage::getStoreConfig(self::XML_PATH_DIALOG_WIDTH);
		}
    }	
    public function getDialogHeight()
    {	
		if(Mage::getStoreConfig(self::XML_PATH_DIALOG_HEIGHT)==""){
			return 420;
		}else{
			return Mage::getStoreConfig(self::XML_PATH_DIALOG_HEIGHT);
		}
    }
    public function getZoomWidth()
    {	if(Mage::getStoreConfig(self::XML_PATH_ZOOM_WIDTH)==""){
			return 260;
		}else{
			return Mage::getStoreConfig(self::XML_PATH_ZOOM_WIDTH);
		} 
    }    
	public function getZoomHeight()
    {
		if(Mage::getStoreConfig(self::XML_PATH_ZOOM_HEIGHT)==""){
			return 260;
		}else{
			return Mage::getStoreConfig(self::XML_PATH_ZOOM_HEIGHT);
		}		
    }	
	public function getZoomPosition()
    {
		if(Mage::getStoreConfig(self::XML_PATH_ZOOM_POSITION)==""){
			return 'right';
		}else{
			return Mage::getStoreConfig(self::XML_PATH_ZOOM_POSITION);
		}		
    }
    public function getZoomColor()
    {
		if(Mage::getStoreConfig(self::XML_PATH_ZOOM_COLOR)==""){
			$color="#000000";
		}else{
			$color="#".Mage::getStoreConfig(self::XML_PATH_ZOOM_COLOR);
		}
		return $color;
		
    }    
	public function getQuickview()
    {
        return Mage::getStoreConfig(self::XML_PATH_QUICK);
    }
}