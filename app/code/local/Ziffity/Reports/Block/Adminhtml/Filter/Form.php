<?php

class Ziffity_Reports_Block_Adminhtml_Filter_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
 
    protected $_fieldVisibility             = array();

    protected $_fieldOptions                = array();


    public function setFieldVisibility($fieldId, $visibility)
    {
        $this->_fieldVisibility[$fieldId] = $visibility ? true : false;
        return $this;
    }

   
    public function getFieldVisibility($fieldId, $defaultVisibility = true)
    {
        if (isset($this->_fieldVisibility[$fieldId])) {
            return $this->_fieldVisibility[$fieldId];
        }
        return $defaultVisibility;
    }

 
    public function setFieldOption($fieldId, $option, $value = null)
    {
        if (is_array($option)) {
            $options    = $option;
        } else {
            $options    = array($option => $value);
        }

        if (!isset($this->_fieldOptions[$fieldId])) {
            $this->_fieldOptions[$fieldId] = array();
        }

        foreach ($options as $key => $value) {
            $this->_fieldOptions[$fieldId][$key] = $value;
        }

        return $this;
    }

    protected function _prepareForm()
    {
       
        $actionUrl      = $this->getCurrentUrl();
        $form           = new Varien_Data_Form(array(
            'id'        => 'filter_form',
            'action'    => $actionUrl, 
            'method'    => 'get'
        ));

       
        $htmlIdPrefix   = 'ziffity_reports_';
        $form->setHtmlIdPrefix($htmlIdPrefix);

       
        $fieldset       = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('ziffity_reports')->__('Filter')));

      
        $dateFormatIso  = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('from', 'date', array(
            'name'      => 'from',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('ziffity_reports')->__('From'),
            'title'     => Mage::helper('ziffity_reports')->__('From'),
			'required'  => true
        ));
        $fieldset->addField('to', 'date', array(
            'name'      => 'to',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('ziffity_reports')->__('To'),
            'title'     => Mage::helper('ziffity_reports')->__('To'),
			'required'  => true
        ));
       /* $fieldset->addField('period_type', 'select', array(
            'name'      => 'period_type',
            'options'   => $this->_getPeriodTypeOptions(),
            'label'     => Mage::helper('ziffity_reports')->__('Period')
        ));*/

  

        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }

  
    protected function _getPeriodTypeOptions()
    {
        $options        = array(
            'day'       => Mage::helper('ziffity_reports')->__('Day'),
            'month'     => Mage::helper('ziffity_reports')->__('Month'),
            'year'      => Mage::helper('ziffity_reports')->__('Year'),
        );

        return $options;
    }

 
 

   
    protected function _initFormValues()
    {
        $filterData     = $this->getFilterData();
        $this->getForm()->addValues($filterData->getData());
        return parent::_initFormValues();
    }

  
    protected function _beforeHtml()
    {
        $result         = parent::_beforeHtml();

        $elements       = $this->getForm()->getElements();

 
        foreach ($elements as $element) {
            $this->_applyFieldVisibiltyAndOptions($element);
        }

        return $result;
    }

   
    protected function _applyFieldVisibiltyAndOptions($element) {
        if ($element instanceof Varien_Data_Form_Element_Fieldset) {
            foreach ($element->getElements() as $fieldElement) {
             
                if ($fieldElement instanceof Varien_Data_Form_Element_Fieldset) {
                    $this->_applyFieldVisibiltyAndOptions($fieldElement);
                    continue;
                }

                $fieldId = $fieldElement->getId();
             
                if (!$this->getFieldVisibility($fieldId)) {
                    $element->removeField($fieldId);
                    continue;
                }

              
                if (isset($this->_fieldOptions[$fieldId])) {
                    $fieldOptions = $this->_fieldOptions[$fieldId];
                    foreach ($fieldOptions as $k => $v) {
                        $fieldElement->setDataUsingMethod($k, $v);
                    }
                }
            }
        }

        return $element;
    }

}