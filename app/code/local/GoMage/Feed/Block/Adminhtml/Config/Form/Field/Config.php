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

class GoMage_Feed_Block_Adminhtml_Config_Form_Field_Config extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
	
	protected $_renders = array();
	
    public function __construct(){
  
        $data= Mage::getStoreConfig('gomage_feedpro/amazon/config');
       
        if(empty($data)){
          $inchooSwitch = new Mage_Core_Model_Config();
         
         $inchooSwitch ->saveConfig('gomage_feedpro/amazon/config', 'a:3:{s:32:"generator_settings_'.uniqid().'";a:4:{s:4:"type";s:1:"0";s:5:"value";s:0:"";s:6:"action";s:1:"0";s:10:"input_code";s:3:"upc";}s:32:"generator_settings_'.uniqid().'";a:4:{s:4:"type";s:1:"0";s:5:"value";s:0:"";s:6:"action";s:1:"0";s:10:"input_code";s:3:"ean";}s:32:"generator_settings_'.uniqid().'";a:4:{s:4:"type";s:1:"0";s:5:"value";s:0:"";s:6:"action";s:1:"0";s:10:"input_code";s:3:"mpn";}}', 'default', 0);  
          
          
          Mage::app()->getFrontController()->getResponse()->setRedirect('#'); 
        }

        
        
        
        $layout = Mage::app()->getFrontController()->getAction()->getLayout();
        
        $renderer = $layout->createBlock('gomage_feed/adminhtml_config_form_renderer_type', '',
                							array('is_render_to_js_template' => true));
        $renderer->setOptions(Mage::getModel('gomage_feed/adminhtml_config_form_renderer_type')->toOptionArray());
        $this->addColumn('type', array(
            'label' => Mage::helper('gomage_feed')->__('Type'),
            'style' => 'width:120px',
        	'renderer' => $renderer
        ));
        $this->_renders['type'] = $renderer;
        
 
        

        $renderer = $layout->createBlock('gomage_feed/adminhtml_config_form_renderer_value', '',
                							array('is_render_to_js_template' => true));
        $renderer->setOptions(Mage::getModel('gomage_feed/adminhtml_config_form_renderer_value')->toOptionArray());
        $this->addColumn('value', array(
                'label' => Mage::helper('gomage_feed')->__('Value'),
                'style' => 'width:145px;',
                'renderer' => $renderer,

            ));   
 
    
        
        $this->_renders['value'] = $renderer;
        
      
        $renderer = $layout->createBlock('gomage_feed/adminhtml_config_form_renderer_action', '',
                							array('is_render_to_js_template' => true));                
        $renderer->setOptions(Mage::getModel('gomage_feed/adminhtml_config_form_renderer_action')->toOptionArray());
        $this->addColumn('action', array(
            'label' => Mage::helper('gomage_feed')->__('Action'),
            'style' => 'width:145px',
        	'renderer' => $renderer
        ));
        $this->_renders['action'] = $renderer;        
        
      
         $this->addColumn('input_code', array(
            'label' => Mage::helper('gomage_feed')->__('code'),
            'style' => 'width:45px'
        
        ));
        $this->_renders['input_code'] = $renderer;  
        
                
        $this->_addAfter = false;  
      

        if (!$this->getTemplate()) {
            $this->setTemplate('gomage/feed/system/config/form/field/config.phtml');
        }
      
    }
    
  protected function _prepareArrayRow(Varien_Object $row){
    	
    	foreach ($this->_renders as $key => $render){
	        $row->setData(
	            'option_extra_attr_' . $render->calcOptionHash($row->getData($key)),
	            'selected="selected"'
	        );
    	}
    } 
  

}
