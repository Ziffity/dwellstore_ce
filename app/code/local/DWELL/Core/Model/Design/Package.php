<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

require_once 'Mage/Core/Model/Design/Package.php';

class Dwell_Core_Model_Design_Package extends Mage_Core_Model_Design_Package
{

	/**
      * Get the timestamp of the newest file
      * 
      * @param array $files
      * @return int $timeStamp
      */
     protected function getNewestFileTimestamp($srcFiles) {
         $timeStamp = null;
         foreach ($srcFiles as $file) {
             if(is_null($timeStamp)) {
                //if is first file, set $timeStamp to filemtime of file
                $timeStamp = filemtime($file);
            } else {
                //get max of current files filemtime and the max so far
                $timeStamp = max($timeStamp, filemtime($file));
            }
         }
         return $timeStamp;
     }

    /**
     * Merge specified javascript files and return URL to the merged file on success
     *
     * @param $files
     * @return string
     */
    public function getMergedJsUrl($files)
    {
    	  //get timestamp of newest source file
        $filesTimeStamp = $this->getNewestFileTimestamp($files);
        
        $targetFilename = md5(implode(',', $files)) .  "_" . $filesTimeStamp . '.js';
        $targetDir = $this->_initMergerDir('js');
        if (!$targetDir) {
            return '';
        }
        if ($this->_mergeFiles($files, $targetDir . DS . $targetFilename, false, null, 'js')) {
            return Mage::getBaseUrl('media', Mage::app()->getRequest()->isSecure()) . 'js/' . $targetFilename;
        }
        return '';
    }

    /**
     * Merge specified css files and return URL to the merged file on success
     *
     * @param $files
     * @return string
     */
    public function getMergedCssUrl($files)
    {
        // secure or unsecure
        $isSecure = Mage::app()->getRequest()->isSecure();
        $mergerDir = $isSecure ? 'css_secure' : 'css';
        $targetDir = $this->_initMergerDir($mergerDir);
        if (!$targetDir) {
            return '';
        }

        // base hostname & port
        $baseMediaUrl = Mage::getBaseUrl('media', $isSecure);
        $hostname = parse_url($baseMediaUrl, PHP_URL_HOST);
        $port = parse_url($baseMediaUrl, PHP_URL_PORT);
        if (false === $port) {
            $port = $isSecure ? 443 : 80;
        }

        //get timestamp of newest source file
        $filesTimeStamp = $this->getNewestFileTimestamp($files);
        
        // merge into target file
        $targetFilename = md5(implode(',', $files) . "|{$hostname}|{$port}") . "_" . $filesTimeStamp . '.css';        
        
        //If the file with the proper timestamp as part of its filename already exists, there's no reason to check again to see if
        //we need to remerge the css files
        if(!file_exists($targetDir . DS . $targetFilename)) {        
            $mergeFilesResult = $this->_mergeFiles(
                $files, $targetDir . DS . $targetFilename,
                false,
                array($this, 'beforeMergeCss'),
                'css'
            );
            if ($mergeFilesResult) {
                return $baseMediaUrl . $mergerDir . '/' . $targetFilename;
            }        
        } else {
            return $baseMediaUrl . $mergerDir . '/' . $targetFilename;
        }        
        return '';
    }
}