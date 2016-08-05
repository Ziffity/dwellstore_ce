<?php
class VINDESIGN_Updatepdp_Block_Updatepdp extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getUpdatepdp()     
     { 
        if (!$this->hasData('updatepdp')) {
            $this->setData('updatepdp', Mage::registry('updatepdp'));
        }
        return $this->getData('updatepdp');
        
    }
}