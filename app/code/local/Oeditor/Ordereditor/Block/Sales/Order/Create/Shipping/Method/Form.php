<?php
class Oeditor_Ordereditor_Block_Sales_Order_Create_Shipping_Method_Form extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form
{
    protected $_code    = 'customshippingrate';

    /**
     * Retrieve custom shipping price
     *
     * @return float | false
     */
    public function getCustomShippingPrice()
    { 
	
        //if ($this->getShippingMethod() == $this->_code) {
            return $this->getAddress()->getBaseShippingAmount();
       // }

        return false;
    }

    /**
     * Retrieve custom shipping title
     *
     * @return string | false
     */
    public function getCustomTitle()
    {
	
        if ($this->getShippingMethod() == $this->_code) {
            return $this->getAddress()->getShippingDescription();
        }

        return false;
    } 

    /**
     * Retrieve array of shipping rates groups
     *
     * @return array
     */
    public function getShippingRates()
    {
        if (empty($this->_rates)) {
            $groups = $this->getAddress()->getGroupedAllShippingRates();

            if (!empty($groups)) {
                foreach ($groups as $code => $groupItems) {
                    if ($code == $this->_code) {
                        unset($groups[$code]);
                    }
                }
            }

            return $this->_rates = $groups;
        }
        return $this->_rates;
    }

}
