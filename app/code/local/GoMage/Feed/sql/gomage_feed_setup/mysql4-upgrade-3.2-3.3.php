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

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('gomage_feed_entity')}` ADD COLUMN `delimiter_prefix` varchar(32) default NULL;
ALTER TABLE `{$this->getTable('gomage_feed_entity')}` ADD COLUMN `delimiter_sufix` varchar(32) default NULL;
ALTER TABLE `{$this->getTable('gomage_feed_entity')}` ADD COLUMN `ftp_port` varchar(128) DEFAULT '';
ALTER TABLE `{$this->getTable('gomage_feed_entity')}` ADD COLUMN `ftp_protocol` varchar(128) DEFAULT '';

");


$gomage_feed_entity = $installer->getConnection()->fetchAll("
    SELECT * FROM {$this->getTable('gomage_feed_entity')}
");


foreach ($gomage_feed_entity as $data){
    $host_info = explode(':', $data['ftp_host']);

    if(isset($host_info[1])){
        $port = $host_info[1];
    }else{
        $port = 21;
    }

    $protocol = $port == 22 ? GoMage_Feed_Model_Adminhtml_System_Config_Source_Ftp::SFTP : GoMage_Feed_Model_Adminhtml_System_Config_Source_Ftp::FTP;

    $installer->run("UPDATE {$this->getTable('gomage_feed_entity')}
                 SET ftp_protocol={$protocol}, ftp_host='".$host_info[0]."', ftp_port={$port}
                WHERE id = {$data['id']}");

}

$installer->endSetup();