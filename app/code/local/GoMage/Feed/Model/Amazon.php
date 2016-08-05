<?php

/**
 * GoMage.com
 *
 * GoMage Feed Pro
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.3
 * @since        Class available since Release 3.2
 */

class GoMage_Feed_Model_Amazon  extends Varien_Object {


               
               public function createAttribute($label, $code)
                    {
                  
                $model=Mage::getModel('eav/entity_setup','core_setup');
                $data=array(
                'type'=>'varchar',
                'input'=>'text',
                'label'=>$label,
                'global'=>Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'is_required'=>'0',
                'is_comparable'=>'0',
                'is_searchable'=>'0',
                'is_unique'=>'1',
                'is_configurable'=>'1',
                'use_defined'=>'1'
                );

                $model->addAttribute('catalog_product', $code ,$data);
            }

            public function codeGenerationItem($name, $type,  $condition){
               
         $products = Mage::getModel('catalog/product')->getCollection();
         $products->addAttributeToSelect('name');
         $products->addAttributeToSelect($name);
                foreach($products as $key =>$product) {
                    if($condition == 0){
                        $code = $this->getLocalGenerationCode($type);
                  $product->setData($name, $code)->save();
                        
                    }
                     if($condition == 1){                       
                  $vall_atr =  $product->getData($name);                
                   if(empty($vall_atr)){
                       $code = $this->getLocalGenerationCode($type);
                 $product->setData($name, $code)->save();
                   }
                    }
                }
            }
            
            public function saveConfig($array){
                $core_config = Mage::getModel('core/config');
                $core_config->saveConfig('gomage_feedpro/amazon/config', serialize($array), 'default', 0); 
            }


    public function getLocalGenerationCode($type){
        if($type == 'upc')
            $n=12;
        if($type == 'ean')
            $n=13;
        if($type == 'mpn')
            $n=8;

        $code = '';
        for($i=0; $i<$n;$i++){
            $code .=  rand(0,9);
        }
        return $code;
    }
}