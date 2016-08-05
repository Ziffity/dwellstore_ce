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
 * Observer
 *
 * @category    Mercent
 * @package     Mercent_ProductFeed
 * @author       Tara Goshi  email questions to: support@mercent.com
 */
class Mercent_ProductFeed_Model_Observer
{
    public static function runFeed($fileName, $isProductFeed, $isInventoryFeed)
    {
        Mage::app()->setCurrentStore(1);

        //Feed name and folder
        $fileDir = sprintf('%s/mercentfeed', Mage::getBaseDir('media'));
        $uploadFile = sprintf('%s/%s', $fileDir, $fileName);

        //Create output directory if not exists
        if(!file_exists($fileDir)){
            mkdir($fileDir);
            chmod($fileDir, 0777);
        }

        //Set memory limit
        ini_set("memory_limit", "1024M");
        ini_set("upload_max_filesize", "256M");
        ini_set("post_max_size", "256M");

        $isFeedActive = false;
        $storeId = 0;

        if (Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_active', $storeId))
        {
            $isFeedActive = true;
            //If the default store is set, run for store 1
            if ($isProductFeed)
            {
                self::generateAndFtpProductFeed(1, $uploadFile, $fileName, trim(Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_account', $storeId)), trim(Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_ftppassword', $storeId)), trim(Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_excludecategoryids', $storeId)), trim(Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_multiselectattribues', $storeId)));
            }
            elseif ($isInventoryFeed)
            {
                self::generateAndFtpInventoryFeed(1, $uploadFile, $fileName, trim(Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_account', $storeId)), trim(Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_ftppassword', $storeId)), trim(Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_excludecategoryids', $storeId)), trim(Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_multiselectattribues', $storeId)));
            }
        }
        else
        {
            foreach (Mage::app()->getStores() as $store)
            {
                $storeId = $store->getId();
                if (Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_active', $storeId))
                {
                    $isFeedActive = true;
                    $ftpUsername = trim(Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_account', $storeId));
                    if (empty($ftpUsername)) {
                        $ftpUsername = '/StoreID'.$storeId;
                    }
                    //Use the ftp username to set the media folder location when running feed for a specific store
                    if (strtoupper(substr($ftpUsername, -3))=='FTP') {
                        $storeFolder = '/'.substr($ftpUsername, 0, -3);
                    } else {
                        $storeFolder = '/'.$ftpUsername;
                    }
                    $fileDir = sprintf('%s/mercentfeed'.$storeFolder, Mage::getBaseDir('media'));
                    $uploadFile = sprintf('%s/%s', $fileDir, $fileName);

                    //Create output directory if not exists
                    if(!file_exists($fileDir)){
                        mkdir($fileDir);
                        chmod($fileDir, 0777);
                    }

                    if ($isProductFeed)
                    {
                        self::generateAndFtpProductFeed($storeId, $uploadFile, $fileName, trim(Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_account', $storeId)), trim(Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_ftppassword', $storeId)), trim(Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_excludecategoryids', $storeId)), trim(Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_multiselectattribues', $storeId)));
                    }
                    elseif ($isInventoryFeed)
                    {
                        self::generateAndFtpInventoryFeed($storeId, $uploadFile, $fileName, trim(Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_account', $storeId)), trim(Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_ftppassword', $storeId)), trim(Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_excludecategoryids', $storeId)), trim(Mage::getStoreConfig('mercent_productfeedconfig/productfeed_group/productfeed_multiselectattribues', $storeId)));
                    }
                }
            }
        }

        if (!$isFeedActive)
        {
            //Write to log file that no feed was enabled
            file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." The Generate Feed setting was not enabled for the default config or any stores\n");
        }
    }

    public static function runProductFeed()
    {
        $fileName = 'MercentProduct.txt';
        self::runFeed($fileName, true, false); //true = $isProductFeed, false = $isInventoryFeed
    }

    public static function runInventoryFeed()
    {
        $fileName = 'MercentInventory.txt';
        self::runFeed($fileName, false, true); //false = $isProductFeed, true = $isInventoryFeed
    }

    private static function generateAndFtpProductFeed($storeId, $uploadFile, $fileName, $ftpUsername, $ftpPassword, $ignoreCategoryIDs, $multiAttributeCodes)
    {
        $uploadFileExists = false;
        if (file_exists($uploadFile))
        {
            $uploadFileExists = true;
        }
        //Write to log file
        file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Starting Product Feed Script\n");
        if (!$uploadFileExists)
        {
            chmod(preg_replace('"\.txt$"', '_log.txt', $uploadFile), 0777);
        }

        //FTP information
        $host = 'sftp.mercent.com';
        $port = 21;
        $username = $ftpUsername;
        $password = $ftpPassword;
        $folder = 'incoming/';

        //Base url for store
        Mage::app()->setCurrentStore($storeId);
        $baseUrl = str_replace('/index.php', '', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB));
        $rootCategoryID = Mage::app()->getStore($storeId)->getRootCategoryId();

        //To use the cache directory, leave as empty string
        //otherwise populate with folder WITHOUT the '/' at the end
        $mainImagePath = $baseUrl . 'media/catalog/product';
        $smallImagePath = $baseUrl . 'media/catalog/product';

        //If the small image filename is different, ex xyz.jpg needs to be xyz_th.jpg
        //set smallImageSuffix to '_th'
        $smallImageSuffix = '';

        $mainImagePathSpecified = false;
        if ($mainImagePath != '') {
            $mainImagePathSpecified = true;
        }

        $smallImagePathSpecified = false;
        if ($smallImagePath != '') {
            $smallImagePathSpecified = true;
        }

        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $storeCode = Mage::app()->getStore()->getCode();
        $userModel = Mage::getModel('admin/user');
        $userModel->setUserId(0);

        $results;

        //Write to log file
        file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Starting Initialize Database\n", FILE_APPEND);

        //Table variables
        $catalogProductFlatTable = Mage::getSingleton('core/resource')->getTableName('catalog/product_flat') . '_' . $storeId;
        $catalogProductVarcharTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar');
        $catalogProductTextTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_text');
        $catalogProductIntTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_int');
        $catalogProductRelationTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_relation');
        $catalogProductWebsiteTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_website');
        $catalogCategoryProductTable = Mage::getSingleton('core/resource')->getTableName('catalog_category_product_index');
        $catalogCategoryEntityTable = Mage::getSingleton('core/resource')->getTableName('catalog_category_entity');
        $catalogProductBundleSelection = Mage::getSingleton('core/resource')->getTableName('catalog_product_bundle_selection');
        $eavAttributeTable = Mage::getSingleton('core/resource')->getTableName('eav/attribute');
        $ratingOptionVote = Mage::getSingleton('core/resource')->getTableName('rating_option_vote');
        $review = Mage::getSingleton('core/resource')->getTableName('review');
        $reviewDetail = Mage::getSingleton('core/resource')->getTableName('review_detail');
        $reviewStatus = Mage::getSingleton('core/resource')->getTableName('review_status');
        $catalogInventoryStockTable = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');
        $catalogProductMediaTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_media_gallery');
        $catalogProductMediaValueTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_media_gallery_value');

        //Initialize database connection
        try {
            $read = Mage::getSingleton('core/resource')->getConnection('core_read');

            $ignoreCategoryIDsArray = explode(',', $ignoreCategoryIDs);
            $ignoreCategoryIDsSql = "";
            foreach ($ignoreCategoryIDsArray as $ignoreCategoryID) {
                if (strlen($ignoreCategoryIDsSql) > 0) {
                    $ignoreCategoryIDsSql .= " or ";
                }
                $ignoreCategoryIDsSql .= "ce.path like('%/".trim($ignoreCategoryID)."/%') or ce.path like '%/".trim($ignoreCategoryID)."'";
            }

            $sql = "
    SELECT distinct flat.*,
    stock.qty_increments,
    rating.rating_count_approved,
    rating.rating_average_approved,
    coalesce(category.category_id, parent_category.category_id) as category_id,
    case when parent_flat.type_id = 'bundle' then null when flat.type_id in ('grouped', 'configurable') then flat.sku else parent_flat.sku end as parent_sku,
    case when parent_flat.type_id = 'bundle' then flat.url_path else coalesce(parent_flat.url_path, flat.url_path) end as parent_url_path,
    case when parent_flat.type_id = 'bundle' then null else parent_flat.image end as parent_image,
    case when parent_flat.type_id = 'bundle' then null else parent_flat.small_image end as parent_small_image,
    media_images.value_concat as alternate_image_url,
    case when parent_flat.type_id = 'grouped' then parent_flat.name end as parent_group_name,
    case when flat.type_id = 'simple' and flat.has_options = 1 then 'true' end as simple_with_options,
    case flat.visibility when 1 then 'Not Visible Individually' when 2 then 'Catalog' when 3 then 'Search' when 4 then 'Catalog, Search' end as visibility,
    flat.type_id as product_type,
    case when parent_flat.type_id = 'bundle' then flat.type_id else coalesce(parent_flat.type_id, flat.type_id) end as parent_product_type,
    case when bundle_selection_custom.parent_product_id is not null then 'true' when flat.type_id = 'bundle' and bundle_selection.quantity_changeable = 1 then 'true' when flat.type_id = 'bundle' and bundle_selection.quantity_changeable = 0 then 'false' end as bundle_custom_selection,
    case when flat.type_id = 'bundle' then coalesce(flat.weight, bundle_selection.bundle_weight) else null end as bundle_weight
    FROM " . $catalogProductFlatTable . " as flat
    inner join " . $catalogProductWebsiteTable . " as product_website on flat.entity_id=product_website.product_id
    inner join " . $catalogInventoryStockTable . " as stock on flat.entity_id=stock.product_id
    left outer join " . $catalogProductRelationTable . " as relation on flat.entity_id=relation.child_id
    left outer join " . $catalogProductFlatTable . " as parent_flat on relation.parent_id=parent_flat.entity_id
    left outer join (
        select cp.product_id, substring_index(group_concat(ce.entity_id order by level desc), ',', 1) as category_id from " . $catalogCategoryProductTable . " cp inner join " . $catalogCategoryEntityTable . " ce on cp.category_id=ce.entity_id and cp.store_id=".$storeId." and cp.visibility<>1 and cp.category_id>2 ".(strlen($ignoreCategoryIDs)>0 ? ("and not(".$ignoreCategoryIDsSql.") ") : "")."and (ce.path like('1/".$rootCategoryID."/%') or ce.path = '1/".$rootCategoryID."') group by cp.product_id
    ) as category on flat.entity_id = category.product_id
    left outer join (
        select cp.product_id, substring_index(group_concat(ce.entity_id order by level desc), ',', 1) as category_id from " . $catalogCategoryProductTable . " cp inner join " . $catalogCategoryEntityTable . " ce on cp.category_id=ce.entity_id and cp.store_id=".$storeId." and cp.visibility<>1 and cp.category_id>2 ".(strlen($ignoreCategoryIDs)>0 ? ("and not(".$ignoreCategoryIDsSql.") ") : "")."and (ce.path like('1/".$rootCategoryID."/%') or ce.path = '1/".$rootCategoryID."') group by cp.product_id
    ) as parent_category on case when parent_flat.type_id = 'bundle' then flat.entity_id else parent_flat.entity_id end = parent_category.product_id
    left outer join (
        select media.entity_id, group_concat(concat_ws('::', value, label) SEPARATOR '|') as value_concat, group_concat(concat_ws('::', value, label, position, media_value_store.store_id) SEPARATOR '|') as keep_position from " . $catalogProductMediaTable . " media inner join " . $catalogProductMediaValueTable . " media_value on media.value_id=media_value.value_id inner join " . $catalogProductFlatTable . " flat_media on flat_media.entity_id=media.entity_id inner join (select value_id, max(store_id) as store_id from " . $catalogProductMediaValueTable . " where store_id in (0, ".$storeId.") group by value_id) media_value_store on media_value.value_id=media_value_store.value_id and media_value.store_id=media_value_store.store_id and flat_media.image <> media.value where media_value.disabled=0 group by media.entity_id order by media.entity_id asc, media_value.position asc, media_value_store.store_id desc
    ) as media_images on flat.entity_id=media_images.entity_id
    left outer join (
        select rating_option_vote.entity_pk_value, count(*) as rating_count_approved, avg(rating_option_vote.value) as rating_average_approved
        from " . $ratingOptionVote . " rating_option_vote
        inner join " . $review . " review on rating_option_vote.review_id=review.review_id
        inner join " . $reviewDetail . " review_detail on review.review_id=review_detail.review_id
        inner join " . $reviewStatus . " review_status on review.status_id=review_status.status_id
        where review_status.status_code='Approved' and review_detail.store_id=".$storeId."
        group by rating_option_vote.entity_pk_value
    ) as rating on flat.entity_id=rating.entity_pk_value
    left outer join (
        select parent_product_id, max(selection_can_change_qty) as quantity_changeable, sum(weight * selection_qty) as bundle_weight from " . $catalogProductBundleSelection . " cpbs inner join " . $catalogProductFlatTable . " cpf on cpbs.product_id=cpf.entity_id group by parent_product_id
    ) as bundle_selection on flat.entity_id=bundle_selection.parent_product_id
    left outer join (
        select distinct parent_product_id from " . $catalogProductBundleSelection . " group by parent_product_id, option_id having count(*) > 1
    ) as bundle_selection_custom on flat.entity_id=bundle_selection_custom.parent_product_id
    where exists (SELECT * from " . $catalogProductIntTable . " as enabled inner join " . $eavAttributeTable . " as eav_attribute on enabled.attribute_id=eav_attribute.attribute_id where eav_attribute.attribute_code='status' and enabled.entity_id=flat.entity_id and enabled.value=1)
    and not(parent_flat.type_id = 'bundle' and flat.visibility=1)
    and not(parent_flat.sku is null and flat.visibility=1)
    and not(flat.type_id in ('giftcard', 'virtual', 'downloadable'))
    and product_website.website_id=".$websiteId;

            $results = $read->query($sql);

            //Write to log file
            file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Initialize Database Complete\n", FILE_APPEND);
        }
        catch (Exception $exception) {
            //Write error to log file
            file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Unable to Initialize Database\n".$exception."\n", FILE_APPEND);
            exit();
        }

        //Read each row and generate the file
        try {
            $file = fopen($uploadFile, 'w');
            if (!$uploadFileExists)
            {
                chmod($uploadFile, 0777);
            }

            $currencyRates = array();

            $isFirstRow = 1;

            $categoryArray = array();

            $queryImageUrl = true;

            $multiSelectAttributes = array();
            foreach(explode(',',$multiAttributeCodes) as $multiSelectAttribute) {
                $multiSelectAttributes[trim($multiSelectAttribute)] = self::array_name_value(Mage::getSingleton('eav/config')->getAttribute('catalog_product', trim($multiSelectAttribute))->getSource()->getAllOptions(false));
            }

            while ($details = $results->fetch(PDO::FETCH_ASSOC)) {
                $details['base_url'] = $baseUrl;

                foreach ($multiSelectAttributes as $key=>$value) {
                    if (!empty($details[$key])) {
                        $multiSelectValues = explode(',',$details[$key]);
                        $details[$key] = '';
                        foreach($multiSelectValues as $multiSelectValue) {
                            if (!empty($details[$key])) {
                                $details[$key] .= ',';
                            }
                            $details[$key] .= $value[$multiSelectValue];
                        }
                    }
                }

                //Remove whitespaces in description and keywords
                $details['description'] = str_replace(array("\r\n", "\r", "\n", "\t", "\0", "\x0B"), '', $details['description']);
                $details['short_description'] = str_replace(array("\r\n", "\r", "\n", "\t", "\0", "\x0B"), '', $details['short_description']);
                $details['meta_keyword'] = str_replace(array("\r\n", "\r", "\n", "\t", "\0", "\x0B"), '', $details['meta_keyword']);
                
                // Dwell specific - need to replace whitespace in details_dimensions attribute
				$details['details_dimensions'] = str_replace(array("\r\n", "\r", "\n", "\t", "\0", "\x0B"), '', $details['details_dimensions']);
				
                //Set images to empty if value is 'no_selection'
                if ($details['image'] == 'no_selection') {
                    $details['image'] = '';
                }
                if ($details['parent_image'] == 'no_selection') {
                    $details['parent_image'] = '';
                }
                if ($details['small_image'] == 'no_selection') {
                    $details['small_image'] = '';
                }
                if ($details['parent_small_image'] == 'no_selection') {
                    $details['parent_small_image'] = '';
                }

                //Get allowed currency rates
                if ($isFirstRow == 1) {
                    $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
                        $currencyRates = Mage::getModel('directory/currency')->getCurrencyRates('USD', array_values($allowedCurrencies));
                }
                foreach ($currencyRates as $key => $value) {
                    if ($key != 'USD') {
                        $details['currency_rate_'.$key] = $value;
                    }
                }

                unset($product);
                $product = null;

                if ($mainImagePathSpecified == false && (strlen($details['image']) > 0 || strlen($details['parent_image']) > 0)) {
                    if ($mainImagePath == '' || self::url_exists($mainImagePath.((strlen($details['image']) > 0) ? $details['image'] : $details['parent_image']))==0) {
                        $mainImageUrl = '';
                        if (strlen($details['image']) > 0) {
                            $mainImageUrl = $details['image'];
                            if (is_null($product))
                            {
                                $product = Mage::getModel('catalog/product')->load($details['entity_id']);
                            }
                        }
                        elseif (strlen($details['parent_image']) > 0 && strlen($details['parent_sku']) > 0) {
                            $mainImageUrl = $details['parent_image'];
                            if (is_null($product))
                            {
                                $parentProductId = Mage::getModel('catalog/product')->getIdBySku($details['parent_sku']);
                                $product = Mage::getModel('catalog/product')->load($parentProductId);
                            }
                        }

                        if (file_exists(Mage::getBaseDir('media').'/catalog/product'.((strlen($details['image']) > 0) ? $details['image'] : $details['parent_image']))){
                            $mainImageLocation = (string)Mage::helper('catalog/image')->init($product, 'image')->resize(500);
                            if ($pos=stripos($mainImageLocation, $mainImageUrl)){
                                $mainImagePath = substr($mainImageLocation, 0, $pos);
                            }
                        }
                        else {
                            $details['image'] = '';
                            $details['parent_image'] = '';
                        }
                    }
                }
                if ($smallImagePathSpecified == false && (strlen($details['small_image']) > 0 || strlen($details['parent_small_image']) > 0)) {
                    if ($smallImagePath == '' || self::url_exists($smallImagePath.((strlen($details['small_image']) > 0) ? $details['small_image'] : $details['parent_small_image']))==0) {
                        $smallImageUrl = '';
                        if (strlen($details['small_image']) > 0) {
                            $smallImageUrl = $details['small_image'];
                            if (is_null($product))
                            {
                                $product = Mage::getModel('catalog/product')->load($details['entity_id']);
                            }
                        }
                        elseif (strlen($details['parent_small_image']) > 0 && strlen($details['parent_sku']) > 0) {
                            $smallImageUrl = $details['parent_small_image'];
                            if (is_null($product))
                            {
                                $parentProductId = Mage::getModel('catalog/product')->getIdBySku($details['parent_sku']);
                                $product = Mage::getModel('catalog/product')->load($parentProductId);
                            }
                        }

                        if (file_exists(Mage::getBaseDir('media').'/catalog/product'.((strlen($details['small_image']) > 0) ? $details['small_image'] : $details['parent_small_image']))){
                            $smallImageLocation = (string)Mage::helper('catalog/image')->init($product, 'small_image')->resize(150);
                            if ($pos=stripos($smallImageLocation, $smallImageUrl)){
                                $smallImagePath = substr($smallImageLocation, 0, $pos);
                            }
                        }
                        else {
                            $details['small_image'] = '';
                            $details['parent_small_image'] = '';
                        }
                    }
                }

                $details['main_image_path'] = $mainImagePath;
                $details['small_image_path'] = $smallImagePath;

                if (strlen($details['small_image']) > 0 && strlen($smallImageSuffix) > 0)
                {
                    $smallImageExt = '.' . end(explode('.', $details['small_image']));
                    $details['small_image'] = str_replace($smallImageExt, $smallImageSuffix . $smallImageExt, $details['small_image']);
                }

                if (strlen($details['parent_small_image']) > 0 && strlen($smallImageSuffix) > 0)
                {
                    $smallImageExt = '.' . end(explode('.', $details['parent_small_image']));
                    $details['parent_small_image'] = str_replace($smallImageExt, $smallImageSuffix . $smallImageExt, $details['parent_small_image']);
                }

                //Retrieve category name and hierarchy if no merchant_category attribute defined
                if ((!array_key_exists('merchant_category', $details) || strlen($details['merchant_category']) == 0) && strlen($details['category_id'] > 0)){
                    if (array_key_exists($details['category_id'], $categoryArray)){
                        $details['merchant_category'] = $categoryArray[$details['category_id']];
                    }
                    else{
                        //Only look up the category value if have not looked up previously
                        $merchantCategory = self::getCategoryHierarchy(Mage::getModel('catalog/category')->load($details['category_id']));
                        $details['merchant_category'] = $merchantCategory;
                        $categoryArray[$details['category_id']] = $merchantCategory;
                    }
                }
                elseif (!array_key_exists('merchant_category', $details)) {
                    $details['merchant_category'] = '';
                }

                if ($isFirstRow == 1) {
                    $isFirstRow = 0;
                    fputcsv($file, array_keys($details), "\t", '"');
                }
                fputcsv($file, $details, "\t", '"');
            }

            fclose($file);

            //Write to log file
            file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." ".$fileName." Feed Generation Complete\n", FILE_APPEND);
        }
        catch (Exception $exception) {
            //Write error to log file
            file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Unable to Generate ".$fileName."\n".$exception."\n", FILE_APPEND);
            exit();
        }

        //GZip the file
        try {
            self::gzipFeed($uploadFile, $uploadFileExists);
        }
        catch (Exception $exception) {
            //Write error to log file
            file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Unable to GZip ".$fileName."\n".$exception."\n", FILE_APPEND);
        }

        //FTP the file
        try {
            if (self::ftpFeed($uploadFile, $fileName, $ftpUsername, $ftpPassword)) {
                //Write to log file
                file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." FTP of ".$fileName.".gz"." Complete\n", FILE_APPEND);
            }
        }
        catch (Exception $exception) {
            //Write error to log file
            file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile.".gz"), date(DateTime::ATOM)." Unable to FTP ".$fileName.".gz"." to ".$host."\n".$exception."\n", FILE_APPEND);
        }
    }

    private static function generateAndFtpInventoryFeed($storeId, $uploadFile, $fileName, $ftpUsername, $ftpPassword, $ignoreCategoryIDs, $multiAttributeCodes)
    {
        $uploadFileExists = false;
        if (file_exists($uploadFile))
        {
            $uploadFileExists = true;
        }
        //Write to log file
        file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Starting Inventory Feed Script\n");
        if (!$uploadFileExists)
        {
            chmod(preg_replace('"\.txt$"', '_log.txt', $uploadFile), 0777);
        }

        Mage::app()->setCurrentStore($storeId);
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $storeCode = Mage::app()->getStore()->getCode();
        $userModel = Mage::getModel('admin/user');
        $userModel->setUserId(0);

        $results;

        //Write to log file
        file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Starting Initialize Database\n", FILE_APPEND);

        //Table variables
        $catalogProductFlatTable = Mage::getSingleton('core/resource')->getTableName('catalog/product_flat') . '_' . $storeId;
        $catalogProductVarcharTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar');
        $catalogProductRelationTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_relation');
        $catalogProductIntTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_int');
        $catalogInventoryStockTable = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');
        $catalogProductWebsiteTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_website');
        $eavAttributeTable = Mage::getSingleton('core/resource')->getTableName('eav/attribute');
        $catalogProductBundleSelection = Mage::getSingleton('core/resource')->getTableName('catalog_product_bundle_selection');

        //Initialize database connection
        try {
            $read = Mage::getSingleton('core/resource')->getConnection('core_read');

            $sql = "
    SELECT distinct flat.entity_id as entity_id,
    flat.sku as sku,
    stock.is_in_stock as is_in_stock,
    stock.backorders as backorders,
    round(coalesce(bundle_selection.bundle_qty, stock.qty)) as qty,
    coalesce((SELECT max(value) from " . $catalogProductVarcharTable . " as entity_varchar inner join " . $eavAttributeTable . " as eav_attribute on entity_varchar.attribute_id=eav_attribute.attribute_id where eav_attribute.attribute_code='fulfillment_latency' and entity_varchar.entity_id=flat.entity_id), (SELECT max(value) from " . $catalogProductVarcharTable . " as entity_varchar inner join " . $eavAttributeTable . " as eav_attribute on entity_varchar.attribute_id=eav_attribute.attribute_id where eav_attribute.attribute_code='fulfillment_latency' and entity_varchar.entity_id=parent_flat.entity_id)) as fulfillment_latency
    FROM " . $catalogProductFlatTable . " as flat
    inner join " . $catalogProductWebsiteTable . " as product_website on flat.entity_id=product_website.product_id
    inner join " . $catalogInventoryStockTable . " as stock on flat.entity_id=stock.product_id
    left outer join " . $catalogProductRelationTable . " as relation on flat.entity_id=relation.child_id
    left outer join " . $catalogProductFlatTable . " as parent_flat on relation.parent_id=parent_flat.entity_id
    left outer join (
        select parent_product_id, min(qty DIV selection_qty) as bundle_qty from " . $catalogProductBundleSelection . " cpbs inner join " . $catalogInventoryStockTable . " ci on cpbs.product_id=ci.product_id group by parent_product_id
    ) as bundle_selection on flat.entity_id=bundle_selection.parent_product_id
    where exists (SELECT * from " . $catalogProductIntTable . " as enabled inner join " . $eavAttributeTable . " as eav_attribute on enabled.attribute_id=eav_attribute.attribute_id where eav_attribute.attribute_code='status' and enabled.entity_id=flat.entity_id and enabled.value=1)
    and not(parent_flat.type_id = 'bundle' and flat.visibility=1)
    and not(parent_flat.sku is null and flat.visibility=1)
    and not(flat.type_id in ('grouped', 'configurable', 'giftcard', 'virtual', 'downloadable'))
    and product_website.website_id=".$websiteId;

            $results = $read->query($sql);

            //Write to log file
            file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Initialize Database Complete\n", FILE_APPEND);
        }
        catch (Exception $exception) {
            //Write error to log file
            file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Unable to Initialize Database\n".$exception."\n", FILE_APPEND);
            exit();
        }

        //Read each row and generate the file
        try {
            $file = fopen($uploadFile, 'w');
            if (!$uploadFileExists)
            {
                chmod($uploadFile, 0777);
            }

            $isFirstRow = 1;

            while ($details = $results->fetch(PDO::FETCH_ASSOC)) {
                if ($isFirstRow == 1) {
                    $isFirstRow = 0;
                    fputcsv($file, array_keys($details), "\t", '"');
                }
                fputcsv($file, $details, "\t", '"');
            }

            fclose($file);

            //Write to log file
            file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." ".$fileName." Feed Generation Complete\n", FILE_APPEND);
        }
        catch (Exception $exception) {
            //Write error to log file
            file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Unable to Generate ".$fileName."\n".$exception."\n", FILE_APPEND);
            exit();
        }

        try {
            self::gzipFeed($uploadFile, $uploadFileExists);
        }
        catch (Exception $exception) {
            //Write error to log file
            file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." Unable to GZip ".$fileName."\n".$exception."\n", FILE_APPEND);
        }

        //FTP the file
        try {
            if (self::ftpFeed($uploadFile, $fileName, $ftpUsername, $ftpPassword)) {
                //Write to log file
                file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile), date(DateTime::ATOM)." FTP of ".$fileName.".gz"." Complete\n", FILE_APPEND);
            }
        }
        catch (Exception $exception) {
            //Write error to log file
            file_put_contents(preg_replace('"\.txt$"', '_log.txt', $uploadFile.".gz"), date(DateTime::ATOM)." Unable to FTP ".$fileName.".gz"."\n".$exception."\n", FILE_APPEND);
        }
    }

    private static function gzipFeed($uploadFile, $uploadFileExists)
    {
        $gzStream = gzopen($uploadFile.".gz", 'w9'); //write to compression level 9
        if (!$uploadFileExists)
        {
            chmod($uploadFile.".gz", 0777);
        }
        gzwrite($gzStream, file_get_contents($uploadFile));
        gzclose($gzStream);
    }

    private static function ftpFeed($uploadFile, $fileName, $ftpUsername, $ftpPassword)
    {
        //FTP information
        $host = 'sftp.mercent.com';
        $port = 21;
        $username = $ftpUsername;
        $password = $ftpPassword;
        $folder = 'incoming/';

        if($connection = ftp_connect($host, $port)) {
            $login = ftp_login($connection, $username, $password);
            ftp_pasv($connection, true);
            if(ftp_put($connection, $folder.$fileName.".gz", $uploadFile.".gz", FTP_BINARY)){
                return true;
            }
            else {
                throw new Exception('Could not upload file.');
            }
        }
    }

    private static function getCategoryHierarchy($category){
        $category_hierarchy = $category->getName();
        if ($category->getLevel() > 2){
            $parent_category = $category->getParentCategory();
            $category_hierarchy = self::getCategoryHierarchy($parent_category) . ' > ' . $category_hierarchy;
            unset($parent_category);
        }
        unset($category);
        return $category_hierarchy;
    }

    private static function url_exists($url) {
        if(@file_get_contents($url,0,NULL,0,1)) {
            return 1;
        }
        else {
            return 0;
        }
    }

    private static function array_name_value($array) {
        if(count($array) > 0) {
            $tempArray = array();
            for($i=0;$i<count($array);$i++) {
                $tempArray[$array[$i]["value"]] = $array[$i]["label"];
            }
            return $tempArray;
        }
        else {
            return array();
        }
    }
}