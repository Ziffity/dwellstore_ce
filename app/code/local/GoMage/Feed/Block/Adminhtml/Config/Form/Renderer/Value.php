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


class GoMage_Feed_Block_Adminhtml_Config_Form_Renderer_Value extends GoMage_Feed_Block_Adminhtml_Config_Form_Renderer_Control
{
  	
	public function getExtraParams(){
		$column = $this->getColumn(); 
		if ($column && isset($column['style'])){
			return 'onchange="selectvalue" style="'.$column['style'].'; " id="value"';
		}else{
			return '';
		}	
                
	}
        
        protected function _toHtml()
    {
        if (!$this->_beforeToHtml()) {
            return '';
        }

      
        $html = '<select name="'.$this->getInputName().'" class="'.$this->getClass().'" '.$this->getExtraParams().' >';


        $isArrayOption = true;
        foreach ($this->getOptions() as $key => $option) {
            if ($isArrayOption && is_array($option)) {
                $value  = $option['value'];
                $label  = $option['label'];
                $params = (!empty($option['params'])) ? $option['params'] : array();
            } else {
                $value = $key;
                $label = $option;
                $isArrayOption = false;
                $params = array();
            }

            if (is_array($value)) {
                $html.= '<optgroup label="'.$label.'">';
                foreach ($value as $keyGroup => $optionGroup) {
                    if (!is_array($optionGroup)) {
                        $optionGroup = array(
                            'value' => $keyGroup,
                            'label' => $optionGroup
                        );
                    }
                    $html.= $this->_optionToHtml(
                        $optionGroup,
                       $this->getResultSelected($option['value'], $keyGroup)
                    );
                }
                $html.= '</optgroup>';
            } else {
                
 
                
                $html.= $this->_optionToHtml(array(
                    'value' => $value,
                    'label' => $label,
                    'params' => $params
                ),
                    $this->getResultSelected($option['value'], $value)
                );
            }
        }
        $html.= '</select>';
     
        $html .= '<input onchange="inputvalue" type="text" name="'.$this->getInputName().'" class="'.$this->getClass().'"  id="value_txt" style="display:none" value="#{value}" >'; 
	
        return $html;
    }
        
   
    
}
