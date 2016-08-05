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
 * @since        Class available since Release 3.3
 */
	
class GoMage_Feed_Model_Adminhtml_System_Config_Source_Ftp{
    
    const FTP = 0;
    const SFTP = 1;
    
    public function toOptionArray()
    {    	
    	$helper = Mage::helper('gomage_feed');
    	
        return array(
            array('value' => self::FTP, 'label' => $helper->__('FTP / FTPS')),
            array('value' => self::SFTP, 'label' => $helper->__('SFTP (SSH)')),
        );
    }
    
	public static function toOptionHash()
    {    	
    	$helper = Mage::helper('gomage_feed');

        return array(
            array('value' => self::FTP, 'label' => $helper->__('FTP / FTPS')),
            array('value' => self::SFTP, 'label' => $helper->__('SFTP (SSH)')),
        );
    }

}