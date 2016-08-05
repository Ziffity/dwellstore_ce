<?php
 /**
 * GoMage.com
 *
 * GoMage Feed Pro
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.3
 * @since        Class available since Release 3.2
 */

class GoMage_Feed_Model_Adminhtml_Config_Form_Renderer_Type extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    
 	public function toOptionArray()
    {
        return array(
        		array('value'=> 0, 'label' => $this->__('New Attribute')),
        		array('value'=> 1, 'label' => $this->__('Attribute'))

        	);
    }
}