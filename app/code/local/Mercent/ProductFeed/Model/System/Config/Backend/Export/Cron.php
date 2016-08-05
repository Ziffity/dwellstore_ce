<?php

/****************************************************
  Copyright (c) Mercent Corporation.  All rights reserved.
  THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY 
  KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
  IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
  PARTICULAR PURPOSE.
  @author Tara Goshi
  Date: 2013-11-10
  Summary: Generates a Mercent Product Feed
  SCOPE OF LICENSE
  The software is licensed, not sold. This agreement only gives you some rights to use the software. Mercent reserves all other rights.
  You may not 
      * reverse engineer, decompile or disassemble the software, except and only to the extent that applicable law expressly permits, despite this limitation;
      * publish the software for others to copy;
      * rent, lease or lend the software;
      * transfer the software or this agreement to any third party; or
      * use the software for commercial software hosting services.
            
*********************************************************/

/**
 * Cron 
 *
 * @category    Mercent
 * @package     Mercent_ProductFeed
 * @author       Tara Goshi  email questions to: support@mercent.com
 */
class Mercent_ProductFeed_Model_System_Config_Backend_Export_Cron extends Mage_Core_Model_Config_Data
{
    const CRON_STRING_PATH_PRODUCT  = 'crontab/jobs/mercent_productfeed_exportproduct/schedule/cron_expr';
    const CRON_MODEL_PATH_PRODUCT   = 'crontab/jobs/mercent_productfeed_exportproduct/run/model';
    const CRON_STRING_PATH_INVENTORY  = 'crontab/jobs/mercent_productfeed_exportinventory/schedule/cron_expr';
    const CRON_MODEL_PATH_INVENTORY   = 'crontab/jobs/mercent_productfeed_exportinventory/run/model';
 
   /**
    * Set Cron settings after save
    *
    * @return Mage_Adminhtml_Model_System_Config_Backend_Log_Cron
    */
    protected function _afterSave()
    {
        $enabled_product      = $this->getData('groups/productfeedschedule_group/fields/productfeedschedule_active/value');
        $enabled_inventory    = $this->getData('groups/productfeedinventoryschedule_group/fields/productfeedinventoryschedule_active/value');
        
        if ($enabled_product) {
            $time_product       = $this->getData('groups/productfeedschedule_group/fields/productfeedschedule_time/value');
            $hour_product       = intval($time_product[0]);
            $minute_product     = intval($time_product[1]);
            $frequency_product  = $this->getData('groups/productfeedschedule_group/fields/productfeedschedule_frequency/value');
    
            $cronExprString_product = self::generateCronExp($hour_product, $minute_product, $frequency_product);
        }
        else {
            $cronExprString_product = '';
        }
        try {
            Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH_PRODUCT, 'path')
                ->setValue($cronExprString_product)
                ->setPath(self::CRON_STRING_PATH_PRODUCT)
                ->save();
 
            Mage::getModel('core/config_data')
                ->load(self::CRON_MODEL_PATH_PRODUCT, 'path')
                ->setValue((string) Mage::getConfig()->getNode(self::CRON_MODEL_PATH_PRODUCT))
                ->setPath(self::CRON_MODEL_PATH_PRODUCT)
                ->save();
        }
        catch (Exception $e) {
            Mage::throwException('Unable to save the mercent product feed cron expression.');
        }
        
        if ($enabled_inventory) {
            $time_inventory       = $this->getData('groups/productfeedinventoryschedule_group/fields/productfeedinventoryschedule_time/value');
            $hour_inventory       = intval($time_inventory[0]);
            $minute_inventory     = intval($time_inventory[1]);
            $frequency_inventory  = $this->getData('groups/productfeedinventoryschedule_group/fields/productfeedinventoryschedule_frequency/value');
    
            $cronExprString_inventory = self::generateCronExp($hour_inventory, $minute_inventory, $frequency_inventory);
        }
        else {
            $cronExprString_inventory = '';
        }
        try {
            Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH_INVENTORY, 'path')
                ->setValue($cronExprString_inventory)
                ->setPath(self::CRON_STRING_PATH_INVENTORY)
                ->save();
 
            Mage::getModel('core/config_data')
                ->load(self::CRON_MODEL_PATH_INVENTORY, 'path')
                ->setValue((string) Mage::getConfig()->getNode(self::CRON_MODEL_PATH_INVENTORY))
                ->setPath(self::CRON_MODEL_PATH_INVENTORY)
                ->save();
        }
        catch (Exception $e) {
            Mage::throwException('Unable to save the mercent product inventory feed cron expression.');
        }
    }

    private function generateCronExp($hour, $minute, $frequency)
    {
        if (empty($frequency))
        {
            $frequency = '24'; //if not set, use daily
        }
        
        $minutesCronExp = $minute;
        if (substr($frequency, -1) == 'm')
        {
            //Set cron for minutes if frequency ends with "m"
            $frequencyMin = substr($frequency, 0, strlen($frequency) - 1);
            $minute = $minute % $frequencyMin; //get the base minute to run
            $minutesCronExp = '';
    
            while ($minute < 60)
            {
                $minutesCronExp .= ($minutesCronExp=='' ? '' : ',').$minute;
                $minute += $frequencyMin;
            }
            $hoursCronExp = '*';
        }
        else
        {
            $hour = $hour % $frequency; //get the base hour to run
            $hoursCronExp = '';
    
            while ($hour < 24)
            {
                $hoursCronExp .= ($hoursCronExp=='' ? '' : ',').$hour;
                $hour += $frequency;
            }
        }

        $cronDayOfWeek = date('N');
        $cronExprArray = array(
            $minutesCronExp,                                            # Minute
            $hoursCronExp,                                              # Hour
            '*',                                                        # Day of the Month
            '*',                                                        # Month of the Year
            '*',                                                        # Day of the Week
        );
        $cronExprString = join(' ', $cronExprArray);
        
        return $cronExprString;
    }
}