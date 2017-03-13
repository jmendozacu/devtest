<?php

class Mofluid_Ganalyticsm_Block_Adminhtml_Form_Edit_Tab_Configuration extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * prepare form in tab
     */
    protected function _prepareForm()
    {
        $helper = Mage::helper('mofluid_ganalyticsm');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_');
        $form->setFieldNameSuffix('');

        $mofluid_google_analytics_model = Mage::getModel('mofluid_ganalyticsm/ganalyticsm')->load(23);
        $mof_ga_id = $mofluid_google_analytics_model->getData('mofluid_ga_id'); //
        $mof_ga_account_id = $mofluid_google_analytics_model->getData('mofluid_ga_accountid');
        $mof_ga_status = $mofluid_google_analytics_model->getData('mofluid_ga_status');
        $mof_ga_extras = $mofluid_google_analytics_model->getData('mofluid_ga_extras');
    
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('general_');
        $form->setFieldNameSuffix('general');

        $fieldset = $form->addFieldset('display', array(
            'legend'       => $helper->__('Configuration'),
            'class'        => 'fieldset-wide'
        ));
       
      $fieldset->addField('mofluid_ganalyticsm_status', 'select', array(
          'label'     => $helper->__('Enable'),
          'name'      => 'mofluid_ganalyticsm_status',
          'required'  => true,
          'class'     => 'validate-select',
          'value'     => $mof_ga_status,
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => $helper->__('Yes'),
              ),

              array(
                  'value'     => 0,
                  'label'     => $helper->__('No'),
              ),
          ),
      ));

        $fieldset->addField('mofluid_ganalyticsm_account_id', 'text', array(
            'name'         => 'mofluid_ganalyticsm_account_id',
            'label'        => $helper->__('Google Analytics ID'),
            'required'       => true,
            'value'         => $mof_ga_account_id
          //  'class'        => 'validate-alphanum',

        ));

         if (Mage::registry('mofluid_ganalyticsm')) {
            $form->setValues(Mage::registry('mofluid_ganalyticsm')->getData());
        }

        $this->setForm($form);
        return parent::_prepareForm();      
        
    }

}
