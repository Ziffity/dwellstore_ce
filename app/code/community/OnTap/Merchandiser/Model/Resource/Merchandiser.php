<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    OnTap
 * @package     OnTap_Merchandiser
 * @copyright Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */
class OnTap_Merchandiser_Model_Resource_Merchandiser extends Mage_Catalog_Model_Resource_Abstract
{
    /**
     * catalogCategoryProduct
     *
     * @var mixed
     */
    public $catalogCategoryProduct;

    /**
     * categoryValuesTable
     *
     * @var mixed
     */
    public $categoryValuesTable;

    /**
     * vmBuildTable
     *
     * @var mixed
     */
    public $vmBuildTable;

    /**
     * _construct
     */
    public function _construct()
    {
        parent::_construct();
        $this->catalogCategoryProduct = Mage::getSingleton('core/resource')->getTableName('catalog_category_product');
        $this->categoryValuesTable = Mage::getSingleton('core/resource')->getTableName('merchandiser_category_values');
        $this->vmBuildTable = Mage::getSingleton('core/resource')->getTableName('merchandiser_vmbuild');
        $this->setConnection('core_read', 'core_write');
    }

    /**
     * clearCategoryProducts
     *
     * @param mixed $categoryId
     * @return void
     */
    public function clearCategoryProducts($categoryId)
    {
        $write = $this->_getWriteAdapter();
        $whereCondition = array($write->quoteInto('category_id=?', $categoryId));
        try {
            $write->delete($this->catalogCategoryProduct, $whereCondition);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * insertMultipleProductsToCategory
     *
     * @param mixed $insertData
     * @return void
     */
    public function insertMultipleProductsToCategory($insertData)
    {
        $write = $this->_getWriteAdapter();
        try {
            $write->insertMultiple($this->catalogCategoryProduct, $insertData);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * updateCategoryProducts
     *
     * @param mixed $catId
     * @param mixed $categoryValueInsertData
     * @return void
     */
    public function updateCategoryProducts($catId,$categoryValueInsertData)
    {
        $write = $this->_getWriteAdapter();
        $condition = array($write->quoteInto('category_id=?', $catId));
        try {
            $write->update($this->categoryValuesTable, $categoryValueInsertData, $condition);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * getMaxPositionFromCategory
     *
     * @param mixed $categoryId
     * @return int
     */
    public function getMaxPositionFromCategory($categoryId)
    {
        $write = $this->_getWriteAdapter();
        try {
            $select = $write->select()->from($this->catalogCategoryProduct, "")
                ->where("category_id = ?", $categoryId)->columns("MAX(position) as max_pos");
            $row = $write->fetchRow($select);
            return $row['max_pos'];
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * deleteSpecificProducts
     *
     * @param mixed $categoryId
     * @param mixed $products
     * @return void
     */
    public function deleteSpecificProducts($categoryId,$products)
    {
        $write = $this->_getWriteAdapter();
        try {
            $whereCondition = array($write->quoteInto('category_id=? AND product_id IN ('.$products.')', $categoryId));
            $write->delete($this->catalogCategoryProduct, $whereCondition);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * fetchCategoriesValues
     *
     * @return array
     */
    public function fetchCategoriesValues()
    {
        $writeAdapter = $this->_getWriteAdapter();
        try {
            $select = $writeAdapter->select()
                ->from($this->categoryValuesTable)
                ->where('smart_attributes != "" OR automatic_sort != "none"');
            return $writeAdapter->fetchAll($select);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * fetchCategoriesValuesByCategoryId
     *
     * @param int $categoryId
     * @return array
     */
    public function fetchCategoriesValuesByCategoryId($categoryId)
    {
        $writeAdapter = $this->_getWriteAdapter();
        try {
            $select = $writeAdapter
                ->select()
                ->from($this->categoryValuesTable)->where('category_id = ?', $categoryId);
            return $writeAdapter->fetchAll($select);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * getOutofStockProducts
     *
     * @param mixed $categoryId
     * @return array
     */
    public function getOutofStockProducts($categoryId)
    {
        $write = $this->_getWriteAdapter();
        $catalogInventoryStockItem = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');
        try {
            $select = $write->select()
                ->from(array('si' => $catalogInventoryStockItem), '')
                ->join(
                    array('cp' => $this->catalogCategoryProduct),
                    "si.product_id = cp.product_id",
                    array('cp.product_id')
                )
                ->where("category_id = ? AND is_in_stock = 0", $categoryId);
            return $write->fetchAll($select);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * updateProductPosition
     *
     * @param mixed $categoryId
     * @param mixed $productId
     * @param mixed $position
     * @return void
     */
    public function updateProductPosition($categoryId,$productId,$position)
    {
        $write = $this->_getWriteAdapter();
        $updateData = array('position' => $position);
        try {
            $condition = array($write->quoteInto('category_id=? AND product_id = ' . $productId, $categoryId));
            $write->update($this->catalogCategoryProduct, $updateData, $condition);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * getSaleCategoryProducts
     *
     * @param mixed $categoryId
     * @param mixed $order
     * @return array
     */
    public function getSaleCategoryProducts($categoryId,$order)
    {
        $write = $this->_getWriteAdapter();
        $priceTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_index_price');
        try {
            $select = $write->select()->from(array('cat_product' => $this->catalogCategoryProduct))
                ->join(array('price_index' => $priceTable), "cat_product.product_id = price_index.entity_id",
                    array("price_index.price", "price_index.final_price"))
                ->where("category_id = $categoryId")
                ->group("product_id")
                ->order("(price_index.price<>price_index.final_price) $order");
            return $write->fetchAll($select);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * getCategoryValues
     *
     * @param mixed $categoryId
     * @param mixed $field (default: null)
     * @return array
     */
    public function getCategoryValues($categoryId,$field = null)
    {
        $write = $this->_getWriteAdapter();
        try {
            $select = $write->select()->from($this->categoryValuesTable)->where('category_id = ?', $categoryId);
            $categoryValues = $write->fetchRow($select);
            if ($field != null && isset($categoryValues[$field])) {
                return $categoryValues[$field];
            }
            return $categoryValues;
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * getCategoryProduct
     *
     * @param mixed $categoryId
     * @param string $order (default: "")
     * @return array
     */
    public function getCategoryProduct($categoryId,$order = "")
    {
        $write = $this->_getWriteAdapter();
        try {
            $select = $write->select()->from($this->catalogCategoryProduct)->where('category_id = ?', $categoryId);
            if ($order != "") {
                $select->order($order);
            }
            return $write->fetchAll($select);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * getVmBuildRows
     *
     * @param string $attributeCode (default: "")
     * @return array
     */
    public function getVmBuildRows($attributeCode = "")
    {
        $writeAdapter = $this->_getWriteAdapter();
        try {
            $select = $this->_getWriteAdapter()->select()->from($this->vmBuildTable);
            if ($attributeCode != "") {
                $condition = "attribute_code =  '$attributeCode'";
                $select->where($condition);
            }
            return $writeAdapter->fetchAll($select);
        } catch (Exception $e) {
        }
    }

    /**
     * removeCategoryValues
     *
     * @param mixed $condition
     * @return void
     */
    public function removeCategoryValues($condition)
    {
        $writeAdapter = $this->_getWriteAdapter();
        try {
            $writeAdapter->delete($this->categoryValuesTable, $condition);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * clearVmBuildTable
     *
     * @return void
     */
    public function clearVmBuildTable()
    {
        $writeAdapter = $this->_getWriteAdapter();
        try {
            $writeAdapter->delete($this->vmBuildTable);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * insertCategoryValues
     *
     * @param mixed $insertValues
     * @return void
     */
    public function insertCategoryValues($insertValues)
    {
        $insertValues = array(0 => $insertValues);
        try {
            $writeAdapter = $this->_getWriteAdapter();
            $writeAdapter->insertMultiple($this->categoryValuesTable, $insertValues);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * insertVmBuildRows
     *
     * @param mixed $iData
     * @return void
     */
    public function insertVmBuildRows($iData)
    {
        $writeAdapter = $this->_getWriteAdapter();
        try {
            $writeAdapter->insert($this->vmBuildTable, $iData);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * reindexCategoryValuesIndexCron
     *
     * @param mixed $vmBuildAttributeCodes
     * @return void
     */
    public function reindexCategoryValuesIndexCron($vmBuildAttributeCodes)
    {
        $whereCondition = ' automatic_sort <> "" OR automatic_sort <> "none"';
        if (count($vmBuildAttributeCodes) > 0) {
            $whereCondition .= ' OR ';
            $whereConditionArr = array();
            foreach ($vmBuildAttributeCodes as $attributeCode) {
                $whereConditionArr[] = 'FIND_IN_SET("'.$attributeCode.'", attribute_codes)';
            }
            $whereCondition .= implode(' OR ', $whereConditionArr);
        }

        $writeAdapter = $this->_getWriteAdapter();
        $sql = $writeAdapter
            ->select()
            ->from($this->categoryValuesTable)
            ->where($whereCondition);

        foreach ($writeAdapter->fetchAll($sql) as $values) {
            Mage::getModel('merchandiser/merchandiser')->affectCategoryBySmartRule($values['category_id']);
            $this->applySortAction($values['category_id']);
        }
    }

    /**
     * applySortAction
     *
     * @param mixed $catId
     * @return void
     */
    public function applySortAction($catId)
    {
        $actionIndex = Mage::getModel('merchandiser/merchandiser')->getCategoryValues($catId, 'automatic_sort');

        if ($actionIndex != '' && $actionIndex != 'none') {
            $actions = Mage::helper('merchandiser')->getConfigAction();
            $string = explode('::', $actions[$actionIndex]['sorting_function']);

            if (is_array($string) && isset($string[0]) && $string[0] != '' && isset($string[1]) && $string[1]  != '') {
                $controllerName = $string[0];
                $functionName = $string[1];
                $controllerObject = new $controllerName;
                call_user_func(array($controllerObject, $functionName), array('catId' => $catId));
            }

            $heroProducts = Mage::getModel('merchandiser/merchandiser')->getCategoryValues($catId, 'heroproducts');

            $categoryProductValues = $this->getCategoryProduct($catId, "position ASC");

            $iCounter = 0;
            $finalProductsArray = array();
            $productObject = Mage::getModel('catalog/product');
            $insertData = array();

            foreach (explode(",", $heroProducts) as $heroSKU) {
                if ($heroSKU != '' && $productId = $productObject->getIdBySku(trim($heroSKU))) {
                    if ($productId > 0) {
                        if (!in_array($productId, $finalProductsArray)) {
                            $iCounter++;
                            $finalProductsArray[] = $productId;
                            $insertData[] = array(
                                'category_id' => $catId,
                                'product_id' => $productId,
                                'position' => $iCounter
                            );
                        }
                    }
                }
            }

            if ($heroProducts != '' && sizeof($finalProductsArray) > 0) {
                foreach ($categoryProductValues as $product) {
                    $productId = $product['product_id'];
                    if (!in_array($productId, $finalProductsArray)) {
                        $finalProductsArray[] = $productId;
                        $iCounter++;
                        $insertData[] = array(
                            'category_id' => $catId,
                            'product_id' => $productId,
                            'position' => $iCounter
                        );
                    }
                }
                if (sizeof($insertData) > 0) {
                    $this->clearCategoryProducts($catId);
                    $this->insertMultipleProductsToCategory($insertData);
                }
            }
        }
    }

    /**
     * getMaxInstockPositionFromCategory
     *
     * @param mixed $categoryId
     * @return int
     */
    public function getMaxInstockPositionFromCategory($categoryId){
        $write = $this->_getWriteAdapter();
        $catalogInventoryStockItem = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');
        try {
            $select = $write->select()
                ->from(array('si' => $catalogInventoryStockItem), '')
                ->join(
                    array('cp' => $this->catalogCategoryProduct),
                    "si.product_id = cp.product_id",
                    array('cp.product_id')
                )
                ->where("category_id = ? AND is_in_stock = 1", $categoryId)
                ->columns("MAX(cp.position) as max_pos");
            $row = $write->fetchRow($select);
            return $row['max_pos'];
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }
}
