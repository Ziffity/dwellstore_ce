<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2012 Amasty (http://www.amasty.com)
* @package Amasty_Xnotif
*/
class Amasty_Xnotif_Block_Product_View_Type_Grouped extends Amasty_Xnotif_Block_Product_View_Type_Grouped_Pure
{
    protected function _toHtml()
    {
        $this->setTemplate('amasty/amxnotif/product/view/type/grouped.phtml');
        return parent::_toHtml();
    }
}