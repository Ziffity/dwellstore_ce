<?php

class Ziffity_Reports_Block_Adminhtml_Filter_Formfull
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

		
		 $statuses = Mage::getModel('sales/order_config')->getStatuses();
            $values = array();
            foreach ($statuses as $code => $label) {
                if (false === strpos($code, 'pending')) {
                    $values[] = array(
                        'label' => Mage::helper('reports')->__($label),
                        'value' => $code
                    );
                }
            }
		
       
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
     
		
		
		   $fieldset->addField('show_order_statuses', 'select', array(
                'name'      => 'show_order_statuses',
                'label'     => Mage::helper('reports')->__('Order Status'),
                'options'   => array(
                        '0' => Mage::helper('reports')->__('Any'),
                        '1' => Mage::helper('reports')->__('Specified'),
                    ),
                'note'      => Mage::helper('reports')->__('Applies to Any of the Specified Order Statuses'),
            ), 'to');

            $fieldset->addField('order_statuses', 'multiselect', array(
                'name'      => 'order_statuses',
                'values'    => $values,
                'display'   => 'none'
            ), 'show_order_statuses');

            // define field dependencies
            if ($this->getFieldVisibility('show_order_statuses') && $this->getFieldVisibility('order_statuses')) {
                $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                    ->addFieldMap("{$htmlIdPrefix}show_order_statuses", 'show_order_statuses')
                    ->addFieldMap("{$htmlIdPrefix}order_statuses", 'order_statuses')
                    ->addFieldDependence('order_statuses', 'show_order_statuses', '1')
                );
            }
		

   /* $fieldset->addField('show_empty_rows', 'select', array(
            'name'      => 'show_empty_rows',
            'options'   => array(
                '1' => Mage::helper('reports')->__('Yes'),
                '0' => Mage::helper('reports')->__('No')
            ),
            'label'     => Mage::helper('reports')->__('Empty Rows'),
            'title'     => Mage::helper('reports')->__('Empty Rows')
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
        $data = $this->getFilterData()->getData();
        foreach ($data as $key => $value) {
            if (is_array($value) && isset($value[0])) {
                $data[$key] = explode(',', $value[0]);
            }
        }
        $this->getForm()->addValues($data);
        return parent::_initFormValues();
    }
 
   
    // protected function _initFormValues()
    // {
        // $filterData     = $this->getFilterData();
        // $this->getForm()->addValues($filterData->getData());
        // return parent::_initFormValues();
    // }

  
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