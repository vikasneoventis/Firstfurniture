<?php

/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

namespace LitExtension\CartMigration\Model\Cart;

use LitExtension\CartMigration\Model\Custom;

class Magento14 extends \LitExtension\CartMigration\Model\Cart
{

    const TYPE_TAX_CUSTOMER_GROUP = 'tax_customer_group';

    public function checkRecent()
    {
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'serialize' => true,
            'query' => serialize(array(
                'taxes' => "SELECT COUNT(1) FROM _DBPRF_tax_calculation_rule WHERE tax_calculation_rule_id > {$this->_notice['taxes']['id_src']}",
                'manufacturers' => "SELECT COUNT(1) FROM _DBPRF_eav_attribute as ea
                                                LEFT JOIN _DBPRF_eav_attribute_option as eao ON ea.attribute_id = eao.attribute_id
                                                WHERE attribute_code = 'manufacturer' AND eao.option_id > {$this->_notice['manufacturers']['id_src']}",
                'categories' => "SELECT COUNT(1) FROM _DBPRF_catalog_category_entity WHERE entity_id > {$this->_notice['categories']['id_src']} AND level > 1",
                'products' => "SELECT COUNT(1) FROM _DBPRF_catalog_product_entity as cpe LEFT JOIN _DBPRF_catalog_product_relation as cpr ON cpe.entity_id = cpr.child_id WHERE cpr.child_id IS NULL AND cpe.entity_id > {$this->_notice['products']['id_src']}",
                'customers' => "SELECT COUNT(1) FROM _DBPRF_customer_entity WHERE entity_id > {$this->_notice['customers']['id_src']}",
                'orders' => "SELECT COUNT(1) FROM _DBPRF_sales_flat_order WHERE entity_id > {$this->_notice['orders']['id_src']}",
                'reviews' => "SELECT COUNT(1) FROM _DBPRF_review WHERE review_id > {$this->_notice['reviews']['id_src']}"
            ))
        ));
        if(!$data || $data['result'] != 'success'){
            return $this;
        }
        foreach($data['object'] as $type => $row){
            $count = $this->arrayToCount($row);
            $this->_notice[$type]['new'] = $count;
        }
        return $this;
    }

    /**
     * Process and get data use for config display
     *
     * @return array : Response as success or error with msg
     */
    public function displayConfig() {
        $response = array();
        $default_cfg = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'serialize' => true,
            'query' => serialize(array(
                "core_store" => "SELECT * FROM _DBPRF_core_store WHERE store_id != 0",
                "currencies" => "SELECT * FROM _DBPRF_core_config_data WHERE path = 'currency/options/default'",
                "eav_entity_type" => "SELECT * FROM _DBPRF_eav_entity_type",
            ))
        ));
        if (!$default_cfg || $default_cfg['result'] != 'success') {
            return $this->errorConnector();
        }
        $object = $default_cfg['object'];
        if ($object && $object['core_store'] && $object['eav_entity_type']) {
            $this->_notice['config']['default_lang'] = $this->_getDefaultLanguage($object['core_store']);
            foreach ($object['eav_entity_type'] as $row) {
                $this->_notice['extend'][$row['entity_type_code']] = $row['entity_type_id'];
            }
        }
        if ($object['currencies']) {
            $this->_notice['config']['default_currency'] = isset($object['currencies']['0']['value']) ? $object['currencies']['0']['value'] : 'USD';
        } else {
            $this->_notice['config']['default_currency'] = 'USD';
        }
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            "serialize" => true,
            "query" => serialize(array(
                "core_store" => "SELECT * FROM _DBPRF_core_store WHERE code != 'admin'",
                "currencies" => "SELECT * FROM _DBPRF_core_config_data WHERE path = 'currency/options/allow'",
                "sales_order_status" => "SELECT DISTINCT status FROM _DBPRF_sales_flat_order_status_history",
                "customer_group" => "SELECT * FROM _DBPRF_customer_group",
                "eav_attribute_set" => "SELECT * FROM _DBPRF_eav_attribute_set WHERE entity_type_id = {$this->_notice['extend']['catalog_product']}",
                "core_store_group" => "SELECT a.entity_id, b.value FROM _DBPRF_catalog_category_entity a, _DBPRF_catalog_category_entity_varchar b, _DBPRF_eav_attribute c
                                            WHERE a.level = '1'
                                            AND b.entity_id = a.entity_id
                                            AND b.attribute_id = c.attribute_id
                                            AND b.store_id = 0
                                            AND c.attribute_code = 'name'
                                            AND c.entity_type_id = {$this->_notice['extend']['catalog_category']}"
            ))
        ));
        if (!$data || $data['result'] != 'success') {
            return $this->errorConnector();
        }
        $obj = $data['object'];
        $language_data = $currency_data = $order_status_data = $category_data = $attribute_data = $customer_group_data = $attribute_group_data = array();
        //$category_data = array("Default Root Category");
        $attribute_data = array("Default Attribute Set");
        foreach ($obj['core_store'] as $language_row) {
            $lang_id = $language_row['store_id'];
            $lang_name = $language_row['name'] . "(" . $language_row['code'] . ")";
            $language_data[$lang_id] = $lang_name;
        }
        if ($obj['currencies']) {
            $currencies = explode(',', $obj['currencies'][0]['value']);
            foreach ($currencies as $currency_row) {
                $currency_id = $currency_row;
                $currency_name = $currency_row;
                $currency_data[$currency_id] = $currency_name;
            }
        } else {
            $currency_data['USD'] = 'USD';
        }
        if ($obj['sales_order_status']) {
            foreach ($obj['sales_order_status'] as $order_status_row) {
                $order_status_id = $order_status_row['status'];
                $order_status_name = ucwords(str_replace("_", " ", $order_status_row['status']));
                $order_status_data[$order_status_id] = $order_status_name;
            }
        } else {
            $order_status_data['pending'] = 'Pending';
        }
        foreach($obj['customer_group'] as $cus_status_row){
            $cus_status_id = $cus_status_row['customer_group_id'];
            $cus_status_name = $cus_status_row['customer_group_code'];
            $customer_group_data[$cus_status_id] = $cus_status_name;
        }
        foreach ($obj['eav_attribute_set'] as $attr_grp_row) {
            $attr_grp_id = $attr_grp_row['attribute_set_id'];
            $attr_grp_name = $attr_grp_row['attribute_set_name'];
            $attribute_group_data[$attr_grp_id] = $attr_grp_name;
        }
        foreach ($obj['core_store_group'] as $store) {
            $cat_id = $store['entity_id'];
            $cat_name = $store['value'];
            $category_data[$cat_id] = $cat_name;
        }
        $this->_notice['config']['category_data'] = $category_data;
        $this->_notice['config']['attribute_data'] = $attribute_group_data;
        $this->_notice['config']['languages_data'] = $language_data;
        $this->_notice['config']['currencies_data'] = $currency_data;
        $this->_notice['config']['order_status_data'] = $order_status_data;
        $this->_notice['config']['customer_group_data'] = $customer_group_data;
        //$this->_notice['config']['config_support']['customer_group_map'] = false;
        $response['result'] = 'success';
        return $response;
    }

    /**
     * Save config of use in config step to notice
     */
    public function displayConfirm($params) {
        parent::displayConfirm($params);
        return array(
            'result' => 'success'
        );
    }

    /**
     * Get data for import display
     *
     * @return array : Response as success or error with msg
     */
    public function displayImport() {
        $recent = $this->getRecentNotice();
        if ($recent) {
            $types = array('taxes', 'manufacturers', 'categories', 'products', 'customers', 'orders', 'reviews');
            foreach ($types as $type) {
                if ($this->_notice['config']['add_option']['add_new'] || !$this->_notice['config']['import'][$type]) {
                    $this->_notice[$type]['id_src'] = $recent[$type]['id_src'];
                    $this->_notice[$type]['imported'] = 0;
                    $this->_notice[$type]['error'] = 0;
                    $this->_notice[$type]['point'] = 0;
                    $this->_notice[$type]['finish'] = 0;
                }
            }
        }
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'serialize' => true,
            'query' => serialize(array(
                'taxes' => "SELECT COUNT(1) FROM _DBPRF_tax_calculation_rule WHERE tax_calculation_rule_id > {$this->_notice['taxes']['id_src']}",
                'manufacturers' => "SELECT COUNT(1) FROM _DBPRF_eav_attribute as ea 
                                                LEFT JOIN _DBPRF_eav_attribute_option as eao ON ea.attribute_id = eao.attribute_id
                                                WHERE attribute_code = 'manufacturer' AND eao.option_id > {$this->_notice['manufacturers']['id_src']}",
                'categories' => "SELECT COUNT(1) FROM _DBPRF_catalog_category_entity WHERE entity_id > {$this->_notice['categories']['id_src']} AND level > 1",
                'products' => "SELECT COUNT(1) FROM _DBPRF_catalog_product_entity as cpe LEFT JOIN _DBPRF_catalog_product_relation as cpr ON cpe.entity_id = cpr.child_id WHERE cpr.child_id IS NULL AND cpe.entity_id > {$this->_notice['products']['id_src']}",
                'customers' => "SELECT COUNT(1) FROM _DBPRF_customer_entity WHERE entity_id > {$this->_notice['customers']['id_src']}",
                'orders' => "SELECT COUNT(1) FROM _DBPRF_sales_flat_order WHERE entity_id > {$this->_notice['orders']['id_src']}",
                'reviews' => "SELECT COUNT(1) FROM _DBPRF_review WHERE review_id > {$this->_notice['reviews']['id_src']}"
            ))
        ));
        if (!$data || $data['result'] != 'success') {
            return $this->errorConnector();
        }
        $totals = array();
        foreach ($data['object'] as $type => $row) {
            $count = $this->arrayToCount($row);
            $totals[$type] = $count;
        }
        $iTotal = $this->_limitDemoModel($totals);
        foreach ($iTotal as $type => $total) {
            $this->_notice[$type]['total'] = $total;
        }
        $this->_notice['taxes']['time_start'] = time();
        if (!$this->_notice['config']['add_option']['add_new']) {
            $delete = $this->_deleteLeCaMgImport($this->_notice['config']['cart_url']);
            if (!$delete) {
                return $this->errorDatabase(true);
            }
        }
        return array(
            'result' => 'success'
        );
    }

    /**
     * Config currency
     */
    public function configCurrency() {
        if(!parent::configCurrency()) return;
        $allowCur = $this->_notice['config']['currencies'];
        $allow_cur = implode(',', $allowCur);
        $this->_process->currencyAllow($allow_cur);
        $default_cur = $this->_notice['config']['currencies'][$this->_notice['config']['default_currency']];
        $this->_process->currencyDefault($default_cur);
        $currencies = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => "SELECT dcr.* FROM _DBPRF_core_config_data as ccd LEFT JOIN _DBPRF_directory_currency_rate as dcr ON ccd.value = dcr.currency_from WHERE ccd.path = 'currency/options/base'"
        ));
        if ($currencies && $currencies['result'] == 'success') {
            $data = array();
            foreach ($currencies['object'] as $currency) {
                $currency_id = $currency['currency_to'];
                $currency_value = $currency['rate'];
                $currency_mage = $this->_notice['config']['currencies'][$currency_id];
                $data[$currency_mage] = $currency_value;
            }
            $this->_process->currencyRate(array(
                $default_cur => $data
            ));
        }
        return;
    }

    /**
     * Process before import taxes
     */
    public function prepareImportTaxes() {
        parent::prepareImportTaxes();
    }

    /**
     * Query for get data of table convert to tax rule
     *
     * @return string
     */
    protected function _getTaxesMainQuery() {
        $id_src = $this->_notice['taxes']['id_src'];
        $limit = $this->_notice['setting']['taxes'];
        $query = "SELECT * FROM _DBPRF_tax_calculation_rule WHERE tax_calculation_rule_id > {$id_src} ORDER BY tax_calculation_rule_id ASC LIMIT {$limit}";
        return $query;
    }

    /**
     * Query for get data relation use for import tax rule
     *
     * @param array $taxes : Data of connector return for query in function getTaxesMainQuery
     * @return array
     */
    protected function _getTaxesExtQuery($taxes) {
        $taxRuleIds = $this->duplicateFieldValueFromList($taxes['object'], 'tax_calculation_rule_id');
        $tax_rule_id_con = $this->arrayToInCondition($taxRuleIds);
        $ext_query = array(
            'tax_calculation' => "SELECT * FROM _DBPRF_tax_calculation WHERE tax_calculation_rule_id IN {$tax_rule_id_con}",
        );
        return $ext_query;
    }

    /**
     * Query for get data relation use for import tax rule
     *
     * @param array $taxes : Data of connector return for query in function getTaxesMainQuery
     * @param array $taxesExt : Data of connector return for query in function getTaxesExtQuery
     * @return array
     */
    protected function _getTaxesExtRelQuery($taxes, $taxesExt) {
        $taxRateIds = $this->duplicateFieldValueFromList($taxesExt['object']['tax_calculation'], 'tax_calculation_rate_id');
        $cusIds = $this->duplicateFieldValueFromList($taxesExt['object']['tax_calculation'], 'customer_tax_class_id');
        $proIds = $this->duplicateFieldValueFromList($taxesExt['object']['tax_calculation'], 'product_tax_class_id');
        $taxClassIds = array_merge($cusIds, $proIds);
        $tax_rate_id = $this->arrayToInCondition($taxRateIds);
        $tax_class_id = $this->arrayToInCondition($taxClassIds);
        $ext_rel_query = array(
            'tax_calculation_rate' => "SELECT * FROM _DBPRF_tax_calculation_rate WHERE tax_calculation_rate_id IN {$tax_rate_id}",
            'tax_class' => "SELECT * FROM _DBPRF_tax_class WHERE class_id IN {$tax_class_id}",
        );
        return $ext_rel_query;
    }

    /**
     * Get primary key of main tax table
     *
     * @param array $tax : One row of function getTaxesMain
     * @param array $taxesExt : Data of function getTaxesExt
     * @return int
     */
    public function getTaxId($tax, $taxesExt) {
        return $tax['tax_calculation_rule_id'];
    }

    /**
     * Convert source data to data for import
     *
     * @param array $tax : One row of function getTaxesMain
     * @param array $taxesExt : Data of function getTaxesExt
     * @return array
     */
    public function convertTax($tax, $taxesExt) {
        if (Custom::TAX_CONVERT) {
            return $this->_custom->convertTaxCustom($this, $tax, $taxesExt);
        }
        $tax_cus_mage = $tax_pro_mage = $tax_rate_ids = array();
        $taxRule = $this->getListFromListByField($taxesExt['object']['tax_calculation'], 'tax_calculation_rule_id', $tax['tax_calculation_rule_id']);
        $tax_cus_ids = $this->duplicateFieldValueFromList($taxRule, 'customer_tax_class_id');
        foreach ($tax_cus_ids as $tax_customer) {
            $cus_name = $this->getRowValueFromListByField($taxesExt['object']['tax_class'], 'class_id', $tax_customer, 'class_name');
            $cus_class = array(
                'class_name' => $cus_name
            );
            $cus_id = $this->_process->taxCustomer($cus_class);
            if ($cus_id['result'] == 'success') {
                $tax_cus_mage[] = $cus_id['mage_id'];
                $this->taxCustomerSuccess($tax_customer, $cus_id['mage_id']);
            }
        }
        $tax_pro_ids = $this->duplicateFieldValueFromList($taxRule, 'product_tax_class_id');
        foreach ($tax_pro_ids as $tax_product) {
            $pro_name = $this->getRowValueFromListByField($taxesExt['object']['tax_class'], 'class_id', $tax_product, 'class_name');
            $pro_class = array(
                'class_name' => $pro_name
            );
            $pro_id = $this->_process->taxProduct($pro_class);
            if ($pro_id['result'] == 'success') {
                $tax_pro_mage[] = $pro_id['mage_id'];
                $this->taxProductSuccess($tax_product, $pro_id['mage_id']);
            }
        }
        $taxRates = $this->duplicateFieldValueFromList($taxRule, 'tax_calculation_rate_id');
        foreach ($taxRates as $row) {
            $tax_rate = $this->getRowFromListByField($taxesExt['object']['tax_calculation_rate'], 'tax_calculation_rate_id', $row);
            $tax_rate_data = array();
            $tax_rate_data['code'] = $tax_rate['code'];
            $tax_rate_data['tax_country_id'] = $tax_rate['tax_country_id'];
            $tax_rate_data['tax_region_id'] = $tax_rate['tax_region_id'];
            if ($tax_rate['zip_is_range']) {
                $tax_rate_data['zip_is_range'] = $tax_rate['zip_is_range'];
                $tax_rate_data['zip_from'] = $tax_rate['zip_from'];
                $tax_rate_data['zip_to'] = $tax_rate['zip_to'];
            } else {
                $tax_rate_data['zip_is_range'] = 0;
            }
            $tax_rate_data['tax_postcode'] = $tax_rate['tax_postcode'];
            $tax_rate_data['rate'] = $tax_rate['rate'];
            $tax_rate_ipt = $this->_process->taxRate($tax_rate_data);
            if ($tax_rate_ipt['result'] == 'success') {
                $tax_rate_ids[] = $tax_rate_ipt['mage_id'];
            }
        }
        $tax_rule_data = array();
        $tax_rule_data['code'] = $tax['code'];
        $tax_rule_data['customer_tax_class_ids'] = $tax_cus_mage;
        $tax_rule_data['product_tax_class_ids'] = $tax_pro_mage;
        $tax_rule_data['tax_rate_ids'] = $tax_rate_ids;
        $tax_rule_data['priority'] = $tax['priority'];
        $tax_rule_data['position'] = $tax['position'];
        $tax_rule_data['calculate_subtotal'] = false;
        $custom = $this->_custom->convertTaxCustom($this, $tax, $taxesExt);
        if ($custom) {
            $tax_rule_data = array_merge($tax_rule_data, $custom);
        }
        return array(
            'result' => 'success',
            'data' => $tax_rule_data
        );
    }

    /**
     * Process before import manufacturers
     */
    public function prepareImportManufacturers() {
        parent::prepareImportManufacturers();
        $man_attr = $this->getManufacturerAttributeId($this->_notice['config']['attribute_set_id']);
        if ($man_attr['result'] == 'success') {
            $this->manAttrSuccess(1, $man_attr['mage_id']);
        }
    }

    /**
     * Query for get data for convert to manufacturer option
     *
     * @return string
     */
    protected function _getManufacturersMainQuery() {
        $id_src = $this->_notice['manufacturers']['id_src'];
        $limit = $this->_notice['setting']['manufacturers'];
        $query = "SELECT eao.* FROM _DBPRF_eav_attribute as ea 
                                                LEFT JOIN _DBPRF_eav_attribute_option as eao ON ea.attribute_id = eao.attribute_id
                                                WHERE attribute_code = 'manufacturer' AND eao.option_id > {$id_src} ORDER BY eao.option_id ASC LIMIT {$limit}";
        return $query;
    }

    /**
     * Get data relation use for import manufacturer
     *
     * @param array $manufacturers : Data of connector return for query function getManufacturersMainQuery
     * @return array
     */
    protected function _getManufacturersExtQuery($manufacturers) {
        $manuOptIds = $this->duplicateFieldValueFromList($manufacturers['object'], 'option_id');
        $option_id = $this->arrayToInCondition($manuOptIds);
        $ext_query = array(
            'eav_attribute_option_value' => "SELECT * FROM _DBPRF_eav_attribute_option_value WHERE option_id IN {$option_id}",
        );
        return $ext_query;
    }

    /**
     * Get data relation use for import manufacturer
     *
     * @param array $manufacturers : Data of connector return for query function getManufacturersMainQuery
     * @param array $manufacturersExt : Data of connector return for query function getManufacturersExtQuery
     * @return array
     */
    protected function _getManufacturersExtRelQuery($manufacturers, $manufacturersExt) {
        return array();
    }

    /**
     * Get primary key of source manufacturer
     *
     * @param array $manufacturer : One row of object in function getManufacturersMain
     * @param array $manufacturersExt : Data of function getManufacturersExt
     * @return int
     */
    public function getManufacturerId($manufacturer, $manufacturersExt) {
        return $manufacturer['option_id'];
    }

    /**
     * Convert source data to data import
     *
     * @param array $manufacturer : One row of object in function getManufacturersMain
     * @param array $manufacturersExt : Data of function getManufacturersExt
     * @return array
     */
    public function convertManufacturer($manufacturer, $manufacturersExt) {
        if (Custom::MANUFACTURER_CONVERT) {
            return $this->_custom->convertManufacturerCustom($this, $manufacturer, $manufacturersExt);
        }
        $man_attr_id = $this->getMageIdManAttr(1);
        if (!$man_attr_id) {
            return array(
                'result' => 'error',
                'msg' => $this->consoleError("Could not create manufacturer attribute!")
            );
        }
        $manu_names = $this->getListFromListByField($manufacturersExt['object']['eav_attribute_option_value'], 'option_id', $manufacturer['option_id']);
        $manu_store = array();
        foreach ($manu_names as $name) {
            $manu_store[$name['store_id']] = $name['value'];
        }
        $manufacturer_data = array(
            'attribute_id' => $man_attr_id
        );
        $manufacturer_data['value']['option'] = array(
            0 => $manu_store['0']
        );
        foreach ($this->_notice['config']['languages'] as $lang_id => $store_id) {
            $manufacturer_data['value']['option'][$store_id] = isset($manu_store[$lang_id]) ? $manu_store[$lang_id] : '';
        }
        $custom = $this->_custom->convertManufacturerCustom($this, $manufacturer, $manufacturersExt);
        if ($custom) {
            $manufacturer_data = array_merge($manufacturer_data, $custom);
        }
        return array(
            'result' => 'success',
            'data' => $manufacturer_data
        );
    }

    /**
     * Query for get data of main table use import category
     *
     * @return string
     */
    protected function _getCategoriesMainQuery() {
        $id_src = $this->_notice['categories']['id_src'];
        $limit = $this->_notice['setting']['categories'];
        $query = "SELECT * FROM _DBPRF_catalog_category_entity WHERE entity_id > {$id_src} AND level > 1 ORDER BY entity_id ASC LIMIT {$limit}";
        return $query;
    }

    /**
     * Query for get data relation use for import categories
     *
     * @param array $categories : Data of connector return for query function getCategoriesMainQuery
     * @return array
     */
    protected function _getCategoriesExtQuery($categories) {
        $categoryIds = $this->duplicateFieldValueFromList($categories['object'], 'entity_id');
        $cat_id_con = $this->arrayToInCondition($categoryIds);
        $ext_query = array(
            'catalog_category_entity_datetime' => "SELECT * FROM _DBPRF_catalog_category_entity_datetime WHERE entity_id IN {$cat_id_con}",
            'catalog_category_entity_decimal' => "SELECT * FROM _DBPRF_catalog_category_entity_decimal WHERE entity_id IN {$cat_id_con}",
            'catalog_category_entity_int' => "SELECT * FROM _DBPRF_catalog_category_entity_int WHERE entity_id IN {$cat_id_con}",
            'catalog_category_entity_text' => "SELECT * FROM _DBPRF_catalog_category_entity_text WHERE entity_id IN {$cat_id_con}",
            'catalog_category_entity_varchar' => "SELECT * FROM _DBPRF_catalog_category_entity_varchar WHERE entity_id IN {$cat_id_con}",
            'eav_attribute' => "SELECT * FROM _DBPRF_eav_attribute WHERE entity_type_id = {$this->_notice['extend']['catalog_category']}",
        );
        return $ext_query;
    }

    /**
     * Query for get data relation use for import categories
     *
     * @param array $categories : Data of connector return for query function getCategoriesMainQuery
     * @param array $categoriesExt : Data of connector return for query function getCategoriesExtQuery
     * @return array
     */
    protected function _getCategoriesExtRelQuery($categories, $categoriesExt) {
        return array();
    }

    /**
     * Get primary key of source category
     *
     * @param array $category : One row of object in function getCategoriesMain
     * @param array $categoriesExt : Data of function getCategoriesExt
     * @return int
     */
    public function getCategoryId($category, $categoriesExt) {
        return $category['entity_id'];
    }

    /**
     * Convert source data to data import
     *
     * @param array $category : One row of object in function getCategoriesMain
     * @param array $categoriesExt : Data of function getCategoriesExt
     * @return array
     */
    public function convertCategory($category, $categoriesExt) {
        if (Custom::CATEGORY_CONVERT) {
            return $this->_custom->convertCategoryCustom($this, $category, $categoriesExt);
        }
        $parent_id = $this->_getCategoryParentId($category['path']);
	$level = $this->_getCategoryLevel($category['path']);
        if ($level <= 2) {
            if (isset($this->_notice['config']['cats'][$parent_id])) {
                $cat_parent_id = $this->_notice['config']['cats'][$parent_id];
            } else {
                $cat_parent_id = $this->_notice['config']['root_category_id'];
            }
        } else {
            $cat_parent_id = $this->getMageIdCategory($parent_id);
            if (!$cat_parent_id) {
                $parent_ipt = $this->_importCategoryParent($parent_id);
                if ($parent_ipt['result'] == 'error') {
                    return $parent_ipt;
                } else if ($parent_ipt['result'] == 'warning') {
                    return array(
                        'result' => 'warning',
                        'msg' => $this->consoleWarning("Category Id = {$category['entity_id']} import failed. Error: Could not import parent category id = {$parent_id}")
                    );
                } else {
                    $cat_parent_id = $parent_ipt['mage_id'];
                }
            }
        }
        $attribute = array();
        foreach ($categoriesExt['object']['eav_attribute'] as $row) {
            $attribute[$row['attribute_code']] = $row['attribute_id'];
        }
        $cat_data = array();
        $varchar = $this->getListFromListByField($categoriesExt['object']['catalog_category_entity_varchar'], 'entity_id', $category['entity_id']);
        $text = $this->getListFromListByField($categoriesExt['object']['catalog_category_entity_text'], 'entity_id', $category['entity_id']);
        $int = $this->getListFromListByField($categoriesExt['object']['catalog_category_entity_int'], 'entity_id', $category['entity_id']);
        $decimal = $this->getListFromListByField($categoriesExt['object']['catalog_category_entity_decimal'], 'entity_id', $category['entity_id']);
        $datetime = $this->getListFromListByField($categoriesExt['object']['catalog_category_entity_datetime'], 'entity_id', $category['entity_id']);
        $names = $this->getListFromListByField($varchar, 'attribute_id', $attribute['name']);
        $cat_data['name'] = $this->getRowValueFromListByField($names, 'store_id', '0', 'value');
        $descriptions = $this->getListFromListByField($text, 'attribute_id', $attribute['description']);
        $cat_data['description'] = $this->getRowValueFromListByField($descriptions, 'store_id', '0', 'value');
        $is_actives = $this->getListFromListByField($int, 'attribute_id', $attribute['is_active']);
        $cat_data['is_active'] = $this->getRowValueFromListByField($is_actives, 'store_id', '0', 'value');
        $meta_titles = $this->getListFromListByField($varchar, 'attribute_id', $attribute['meta_title']);
        $cat_data['meta_title'] = $this->getRowValueFromListByField($meta_titles, 'store_id', '0', 'value');
        $meta_keywords = $this->getListFromListByField($text, 'attribute_id', $attribute['meta_keywords']);
        $cat_data['meta_keywords'] = $this->getRowValueFromListByField($meta_keywords, 'store_id', '0', 'value');
        $meta_descriptions = $this->getListFromListByField($text, 'attribute_id', $attribute['meta_description']);
        $cat_data['meta_description'] = $this->getRowValueFromListByField($meta_descriptions, 'store_id', '0', 'value');
	//$url_store = $this->getListFromListByField($varchar, 'attribute_id', $attribute['url_key']);
        //$cat_data['url_key'] = $this->getRowValueFromListByField($url_store, 'store_id', '0', 'value');
        $url_store = $this->getListFromListByField($varchar, 'attribute_id', $attribute['url_key']);
        $url_def = $this->getRowValueFromListByField($url_store, 'store_id', '0', 'value');
        $cat_data['url_key'] = $this->generateUrlKeyFromUrlKey($url_def);
        
        $images = $this->getListFromListByField($varchar, 'attribute_id', $attribute['image']);
        $image_path = $this->getRowValueFromListByField($images, 'store_id', '0', 'value');
        if ($image_path && $img_path = $this->downloadImage($this->getUrlSuffix($this->_notice['config']['image_category']), $image_path, 'catalog/category')) {
            $cat_data['image'] = $img_path;
        }
        $pCat = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($cat_parent_id);
        $cat_data['path'] = $pCat->getPath();
        $cat_data['is_anchor'] = $this->getRowValueFromListByField($int, 'attribute_id', $attribute['is_anchor'], 'value');
        $include_in_menus = $this->getListFromListByField($int, 'attribute_id', $attribute['include_in_menu']);
        $cat_data['include_in_menu'] = $this->getRowValueFromListByField($include_in_menus, 'store_id', '0', 'value');
        $display_modes = $this->getListFromListByField($varchar, 'attribute_id', $attribute['display_mode']);
        $cat_data['display_mode'] = $this->getRowValueFromListByField($display_modes, 'store_id', '0', 'value');
        $multi_store = array();
        foreach ($this->_notice['config']['languages'] as $lang_id => $store_id) {
            $store_data = array();
            $store_data['store_id'] = $store_id;
            $store_data['name'] = $this->getRowValueFromListByField($names, 'store_id', $lang_id, 'value');
            $store_data['description'] = $this->getRowValueFromListByField($descriptions, 'store_id', $lang_id, 'value');
            //$store_data['is_active'] = $this->getRowValueFromListByField($is_actives, 'store_id', $lang_id, 'value');
            $store_data['meta_title'] = $this->getRowValueFromListByField($meta_titles, 'store_id', $lang_id, 'value');
            $store_data['meta_keywords'] = $this->getRowValueFromListByField($meta_keywords, 'store_id', $lang_id, 'value');
            $store_data['meta_description'] = $this->getRowValueFromListByField($meta_descriptions, 'store_id', $lang_id, 'value');
            $store_data['image'] = $this->getRowValueFromListByField($images, 'store_id', $lang_id, 'value');
            //$store_data['include_in_menu'] = $this->getRowValueFromListByField($include_in_menus, 'store_id', $lang_id, 'value');
            $store_data['display_mode'] = $this->getRowValueFromListByField($display_modes, 'store_id', $lang_id, 'value');
            $multi_store[] = $store_data;
        }
        $cat_data['multi_store'] = $multi_store;
        if ($this->_seo) {
            $seo = $this->_seo->convertCategorySeo($this, $category, $categoriesExt);
            if ($seo) {
                $cat_data['seo_url'] = $seo;
            }
        }
        $custom = $this->_custom->convertCategoryCustom($this, $category, $categoriesExt);
        if ($custom) {
            $cat_data = array_merge($cat_data, $custom);
        }
        return array(
            'result' => 'success',
            'data' => $cat_data
        );
    }

    /**
     * Process before import products
     */
    public function prepareImportProducts() {
        parent::prepareImportProducts();
        $this->_notice['extend']['website_ids'] = $this->getWebsiteIdsByStoreIds($this->_notice['config']['languages']);
        /*$data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            "serialize" => true,
            "query" => serialize(array(
                "customer_group" => "SELECT * FROM _DBPRF_customer_group",
            ))
        ));
        if (!$data || $data['result'] != 'success') {
            return $this->errorConnector();
        }
        $obj = $data['object'];
        foreach ($obj['customer_group'] as $group_row) {
            $group_import = $this->_importCustomerGroup($group_row);
            if ($group_import['result'] == 'success') {
                $this->_taxCustomerGroupSuccess($group_row['customer_group_id'], $group_import['mage_id']);
            }
        }*/
    }

    /**
     * Query for get data of main table use for import product
     *
     * @return string
     */
    protected function _getProductsMainQuery() {
        $id_src = $this->_notice['products']['id_src'];
        $limit = $this->_notice['setting']['products'];
        $query = "SELECT cpe.* FROM _DBPRF_catalog_product_entity as cpe LEFT JOIN _DBPRF_catalog_product_relation as cpr ON cpe.entity_id = cpr.child_id WHERE cpr.child_id IS NULL AND cpe.entity_id > {$id_src} ORDER BY cpe.entity_id ASC LIMIT {$limit}";
        return $query;
    }

    /**
     * Get data relation use for import product
     *
     * @param array $products : Data of function getProductsMain
     * @return array : Response of connector
     */
    public function getProductsExt($products) {
        $productsExt = array(
            'result' => 'success'
        );
        $productIds = $this->duplicateFieldValueFromList($products['object'], 'entity_id');
        $pro_id_query = $this->arrayToInCondition($productIds);
        $ext_query = array(
            'catalog_product_relation' => "SELECT * FROM _DBPRF_catalog_product_relation WHERE parent_id IN {$pro_id_query}",
            'catalog_product_super_attribute' => "SELECT * FROM _DBPRF_catalog_product_super_attribute WHERE product_id IN {$pro_id_query}",
            'eav_attribute' => "SELECT * FROM _DBPRF_eav_attribute WHERE entity_type_id = {$this->_notice['extend']['catalog_product']}",
            'catalog_product_bundle_option' => "SELECT * FROM _DBPRF_catalog_product_bundle_option WHERE parent_id IN {$pro_id_query}",
            'catalog_product_bundle_selection' => "SELECT * FROM _DBPRF_catalog_product_bundle_selection WHERE parent_product_id IN {$pro_id_query}",
            'tag_relation' => "SELECT * FROM _DBPRF_tag_relation WHERE product_id IN {$pro_id_query}",
            'catalog_product_link' => "SELECT * FROM _DBPRF_catalog_product_link WHERE ( product_id IN {$pro_id_query} OR linked_product_id IN {$pro_id_query} ) AND link_type_id IN (1,4,5)",
        );
        if ($this->_seo) {
            $seo_ext_query = $this->_seo->getProductsExtQuery($this, $products);
            if ($seo_ext_query) {
                $ext_query = array_merge($ext_query, $seo_ext_query);
            }
        }
        $cus_ext_query = $this->_custom->getProductsExtQueryCustom($this, $products);
        if ($cus_ext_query) {
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        $productsExt = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'serialize' => true,
            'query' => serialize($ext_query)
        ));
        if (!$productsExt || $productsExt['result'] != 'success') {
            return $this->errorConnector(true);
        }
        $productChildIds = $this->duplicateFieldValueFromList($productsExt['object']['catalog_product_relation'], 'child_id');
        $pro_child_id = $this->arrayToInCondition($productChildIds);
        $productAllIds = array_merge($productIds, $productChildIds);
        $superAttrIds = $this->duplicateFieldValueFromList($productsExt['object']['catalog_product_super_attribute'], 'product_super_attribute_id');
        $pro_id_con = $this->arrayToInCondition($productAllIds);
        $super_attr_id = $this->arrayToInCondition($superAttrIds);
        $attrIds = $this->duplicateFieldValueFromList($productsExt['object']['eav_attribute'], 'attribute_id');
        $attr_id = $this->arrayToInCondition($attrIds);
        $bundleOptIds = $this->duplicateFieldValueFromList($productsExt['object']['catalog_product_bundle_option'], 'option_id');
        $bundle_opt_id = $this->arrayToInCondition($bundleOptIds);
        $tagIds = $this->duplicateFieldValueFromList($productsExt['object']['tag_relation'], 'tag_id');
        $tag_ids_query = $this->arrayToInCondition($tagIds);
        $ext_rel_query = array(
            'catalog_product_entity' => "SELECT * FROM _DBPRF_catalog_product_entity WHERE entity_id IN {$pro_child_id}",
            'catalog_product_entity_datetime' => "SELECT * FROM _DBPRF_catalog_product_entity_datetime WHERE entity_id IN {$pro_id_con}",
            'catalog_product_entity_decimal' => "SELECT * FROM _DBPRF_catalog_product_entity_decimal WHERE entity_id IN {$pro_id_con}",
            'catalog_product_entity_int' => "SELECT * FROM _DBPRF_catalog_product_entity_int WHERE entity_id IN {$pro_id_con}",
            'catalog_product_entity_text' => "SELECT * FROM _DBPRF_catalog_product_entity_text WHERE entity_id IN {$pro_id_con}",
            'catalog_product_entity_varchar' => "SELECT * FROM _DBPRF_catalog_product_entity_varchar WHERE entity_id IN {$pro_id_con}",
            'catalog_product_entity_gallery' => "SELECT * FROM _DBPRF_catalog_product_entity_gallery WHERE entity_id IN {$pro_id_con}",
            'catalog_product_entity_media_gallery' => "SELECT * FROM _DBPRF_catalog_product_entity_media_gallery WHERE entity_id IN {$pro_id_con}",
            'catalog_product_entity_tier_price' => "SELECT * FROM _DBPRF_catalog_product_entity_tier_price WHERE entity_id IN {$pro_id_con}",
            'catalog_product_option' => "SELECT * FROM _DBPRF_catalog_product_option WHERE product_id IN {$pro_id_con}",
            'catalog_category_product' => "SELECT * FROM _DBPRF_catalog_category_product WHERE product_id IN {$pro_id_con}",
            'catalog_product_super_attribute_label' => "SELECT * FROM _DBPRF_catalog_product_super_attribute_label WHERE product_super_attribute_id IN {$super_attr_id}",
            'catalog_product_super_attribute_pricing' => "SELECT * FROM _DBPRF_catalog_product_super_attribute_pricing WHERE product_super_attribute_id IN {$super_attr_id}",
            'catalog_eav_attribute' => "SELECT * FROM _DBPRF_catalog_eav_attribute WHERE attribute_id IN {$attr_id}",
            'catalog_product_bundle_option_value' => "SELECT * FROM _DBPRF_catalog_product_bundle_option_value WHERE option_id IN {$bundle_opt_id}",
            'cataloginventory_stock_item' => "SELECT * FROM _DBPRF_cataloginventory_stock_item WHERE product_id IN {$pro_id_con}",
            'tag' => "SELECT * FROM _DBPRF_tag WHERE tag_id IN {$tag_ids_query}",
            'eav_entity_attribute' => "SELECT * FROM _DBPRF_eav_entity_attribute WHERE attribute_id IN {$attr_id} AND entity_type_id = {$this->_notice['extend']['catalog_product']}",
            'catalog_product_website' => "SELECT * FROM _DBPRF_catalog_product_website WHERE product_id IN {$pro_id_con}",
            'core_store' => "SELECT * FROM _DBPRF_core_store WHERE code != 'admin'",
        );
        if ($this->_seo) {
            $seo_ext_rel_query = $this->_seo->getProductsExtRelQuery($this, $products, $productsExt);
            if ($seo_ext_rel_query) {
                $ext_rel_query = array_merge($ext_rel_query, $seo_ext_rel_query);
            }
        }
        $cus_ext_rel_query = $this->_custom->getProductsExtRelQueryCustom($this, $products, $productsExt);
        if ($cus_ext_rel_query) {
            $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
        }
        $productsExtRel = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'serialize' => true,
            'query' => serialize($ext_rel_query)
        ));
        if (!$productsExtRel || $productsExtRel['result'] != 'success') {
            return $this->errorConnector(true);
        }
        $productsExt = $this->_syncResultQuery($productsExt, $productsExtRel);
        /*
         * Product_Ext_Rel_Rel
         */
        $optionAttrIds = $this->duplicateFieldValueFromList($productsExt['object']['catalog_product_entity_int'], 'value');
        $option_attr_id = $this->arrayToInCondition($optionAttrIds);
        $galleryIds = $this->duplicateFieldValueFromList($productsExt['object']['catalog_product_entity_media_gallery'], 'value_id');
        $gallery_value_id = $this->arrayToInCondition($galleryIds);
        $optionCusIds = $this->duplicateFieldValueFromList($productsExt['object']['catalog_product_option'], 'option_id');
        $option_cus_id = $this->arrayToInCondition($optionCusIds);
        //Addition
        $multi = $this->getListFromListByField($productsExt['object']['eav_attribute'], 'frontend_input', 'multiselect');
        $multi_ids = $this->duplicateFieldValueFromList($multi, 'attribute_id');
        $all_option = array();
        if ($multi_ids) {
            $multi_opt = $this->getListFromListByListField($productsExt['object']['catalog_product_entity_varchar'], 'attribute_id', $multi_ids);
            foreach ($multi_opt as $row) {
                $new_options = explode(',', $row['value']);
                $all_option = array_merge($all_option, $new_options);
            }
        }
        $all_option_query = $this->arrayToInCondition($all_option);
        $ext_rel_rel_query = array(
            'eav_attribute_option_value' => "SELECT * FROM _DBPRF_eav_attribute_option_value WHERE option_id IN {$option_attr_id} OR option_id IN {$all_option_query}",
            'catalog_product_entity_media_gallery_value' => "SELECT * FROM _DBPRF_catalog_product_entity_media_gallery_value WHERE value_id IN {$gallery_value_id}",
            'catalog_product_option_title' => "SELECT * FROM _DBPRF_catalog_product_option_title WHERE option_id IN {$option_cus_id}",
            'catalog_product_option_price' => "SELECT * FROM _DBPRF_catalog_product_option_price WHERE option_id IN {$option_cus_id}",
            'catalog_product_option_type_value' => "SELECT a.*, b.*, c.price, c.price_type FROM _DBPRF_catalog_product_option_type_value as a 
                                                                                            LEFT JOIN _DBPRF_catalog_product_option_type_title as b ON a.option_type_id = b.option_type_id
                                                                                            LEFT JOIN _DBPRF_catalog_product_option_type_price as c ON b.option_type_id = c.option_type_id AND b.store_id = c.store_id
                                                                                            WHERE a.option_id IN {$option_cus_id}
                                                                                            ",
        );
        $productsExtRelRel = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'serialize' => true,
            'query' => serialize($ext_rel_rel_query)
        ));
        if (!$productsExtRelRel || $productsExtRelRel['result'] != 'success') {
            return $this->errorConnector(true);
        }
        $productsExt = $this->_syncResultQuery($productsExt, $productsExtRelRel);
        return $productsExt;
    }

    /**
     * Get primary key of source product main
     *
     * @param array $product : One row of object in function getProductsMain
     * @param array $productsExt : Data of function getProductsMain
     * @return int
     */
    public function getProductId($product, $productsExt) {
        return $product['entity_id'];
    }

    /**
     * Convert source data to data import
     *
     * @param array $product : One row of object in function getProductsMain
     * @param array $productsExt : Data of function getProductsMain
     * @return array
     */
    public function convertProduct($product, $productsExt) {
        if (Custom::PRODUCT_CONVERT) {
            return $this->_custom->convertProductCustom($this, $product, $productsExt);
        }
        $result = array();
        $result['type_id'] = $product['type_id'];
        if ($result['type_id'] == 'configurable') {
            $children = $this->getListFromListByField($productsExt['object']['catalog_product_relation'], 'parent_id', $product['entity_id']);
            $children = $this->duplicateFieldValueFromList($children, 'child_id');
            if ($children) {
                $config_data = $this->_importChildrenConfigurableProduct($product, $children, $productsExt);
                if (!$config_data) {
                    return array(
                        'result' => 'warning',
                        'msg' => $this->consoleWarning("Product ID = {$product['entity_id']} import failed. Error: Product's attribute ccould not create!"),
                    );
                }
                $result = array_merge($config_data, $result);
            }
        } elseif ($result['type_id'] == 'bundle') {
            $children = $this->getListFromListByField($productsExt['object']['catalog_product_relation'], 'parent_id', $product['entity_id']);
            $children = $this->duplicateFieldValueFromList($children, 'child_id');
            if ($children) {
                $bundle_data = $this->_importChildrenBundleProduct($product['entity_id'], $children, $productsExt);
                if (!$bundle_data) {
                    return array(
                        'result' => 'warning',
                        'msg' => $this->consoleWarning("Product ID = {$product['entity_id']} import failed. Error: Product's child could not create!"),
                    );
                }
                $result = array_merge($bundle_data, $result);
            }
        }
        $result = array_merge($this->_convertProduct($product, $productsExt), $result);
        return array(
            'result' => 'success',
            'data' => $result
        );
    }

    /**
     * Process after one product import successful
     *
     * @param int $product_mage_id : Id of product save successful to magento
     * @param array $data : Data of function convertProduct
     * @param array $product : One row of object in function getProductsMain
     * @param array $productsExt : Data of function getProductsExt
     * @return boolean
     */
    public function afterSaveProduct($product_mage_id, $data, $product, $productsExt) {
        if (parent::afterSaveProduct($product_mage_id, $data, $product, $productsExt)) {
            return;
        }
        $optionAll = $this->getListFromListByField($productsExt['object']['catalog_product_option'], 'product_id', $product['entity_id']);
        if ($optionAll) {
            $opt_data = array();
            foreach ($optionAll as $option) {
                $title = $this->getListFromListByField($productsExt['object']['catalog_product_option_title'], 'option_id', $option['option_id']);
                $optionIpt = array(
                    'previous_group' => $this->_objectManager->create('Magento\Catalog\Model\Product\Option')->getGroupByType($option['type']),
                    'type' => $option['type'],
                    'is_require' => intval($option['is_require']),
                    'title' => $this->getRowValueFromListByField($title, 'store_id', '0', 'title'),
                    'sort_order' => $option['sort_order']
                );
                $values = array();
                $price = $this->getRowFromListByField($productsExt['object']['catalog_product_option_price'], 'option_id', $option['option_id']);
                $types = $this->getListFromListByField($productsExt['object']['catalog_product_option_type_value'], 'option_id', $option['option_id']);
                $types_default = $this->getListFromListByField($types, 'store_id', '0');
                if ($price) {
                    $optionIpt['price'] = $price['price'];
                    $optionIpt['price_type'] = $price['price_type'];
                    $optionIpt['sku'] = ($option['sku']) ? $option['sku'] : null;
                    $optionIpt['max_characters'] = $option['max_characters'] ? $option['max_characters'] : null;
                    $optionIpt['file_extension'] = $option['file_extension'] ? $option['file_extension'] : null;
                    $optionIpt['image_size_x'] = $option['image_size_x'] ? $option['image_size_x'] : null;
                    $optionIpt['image_size_y'] = $option['image_size_y'] ? $option['image_size_y'] : null;
                } elseif ($types_default) {
                    foreach ($types_default as $row) {
                        $value = array(
                            'option_type_id' => -1,
                            'title' => $row['title'],
                            'price' => $row['price'],
                            'price_type' => $row['price_type'],
                            'sku' => ($row['sku']) ? $row['sku'] : '',
                            'sort_order' => $row['sort_order']
                        );
                        $values[] = $value;
                    }
                }
                $optionIpt['values'] = ($values) ? $values : '';
                $opt_data[] = $optionIpt;
            }
            $this->importProductOption($product_mage_id, $opt_data);
        }
        if ($product['type_id'] == 'grouped') {
            $group = array();
            $i = 1;
            $child_products = $this->getListFromListByField($productsExt['object']['catalog_product_relation'], 'parent_id', $product['entity_id']);
            foreach ($child_products as $child) {
                $check_imported = $this->getMageIdProduct($child['child_id']);
                if ($check_imported) {
                    $group[$check_imported] = array(
                        'id' => $check_imported,
                        'position' => $i,
                        'qty' => 1
                    );
                } else {
                    $child_info = $this->getRowFromListByField($productsExt['object']['catalog_product_entity'], 'entity_id', $child['child_id']);
                    $product_baby = array(
                        'type_id' => $child_info['type_id']
                    );
                    $product_baby = array_merge($this->_convertProduct($child_info, $productsExt), $product_baby);
                    $pro_import = $this->_process->product($product_baby);
                    if ($pro_import['result'] !== 'success') {
                        return false;
                    }
                    $this->productSuccess($child['child_id'], $pro_import['mage_id']);
                    $group[$pro_import['mage_id']] = array(
                        'id' => $pro_import['mage_id'],
                        'position' => $i,
                        'qty' => 1
                    );
                }
                $i++;
            }
            $products_links = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product_mage_id);
            $products_links = $this->setProductGroupLinks($products_links, $group);
            //$products_links->setData("grouped_link_data", $group);
            $products_links->save();
        }
        // Related product
        $linkProducts = $this->getListFromListByField($productsExt['object']['catalog_product_link'], 'product_id', $product['entity_id']);
        if ($linkProducts) {
            $relateProducts = $this->getListFromListByField($linkProducts, 'link_type_id', '1');
            $upsellProducts = $this->getListFromListByField($linkProducts, 'link_type_id', '4');
            $crosssellProducts = $this->getListFromListByField($linkProducts, 'link_type_id', '5');
            if ($relateProducts) {
                $relate_products = $this->duplicateFieldValueFromList($relateProducts, 'linked_product_id');
                $this->setProductRelation($product_mage_id, $relate_products, 1);
            }
            if ($upsellProducts) {
                $upsell_products = $this->duplicateFieldValueFromList($upsellProducts, 'linked_product_id');
                $this->setProductRelation($product_mage_id, $upsell_products, 4);
            }
            if ($crosssellProducts) {
                $crosssell_product = $this->duplicateFieldValueFromList($crosssellProducts, 'linked_product_id');
                $this->setProductRelation($product_mage_id, $crosssell_product, 5);
            }
        }
        //Revert
        $linkRProducts = $this->getListFromListByField($productsExt['object']['catalog_product_link'], 'linked_product_id', $product['entity_id']);
        if ($linkRProducts) {
            $relateProducts = $this->getListFromListByField($linkRProducts, 'link_type_id', '1');
            $upsellProducts = $this->getListFromListByField($linkRProducts, 'link_type_id', '4');
            $crosssellProducts = $this->getListFromListByField($linkRProducts, 'link_type_id', '5');
            if ($relateProducts) {
                $relate_products = $this->duplicateFieldValueFromList($relateProducts, 'product_id');
                $this->setProductRelation($relate_products, $product_mage_id, 1);
            }
            if ($upsellProducts) {
                $upsell_products = $this->duplicateFieldValueFromList($upsellProducts, 'product_id');
                $this->setProductRelation($upsell_products, $product_mage_id, 4);
            }
            if ($crosssellProducts) {
                $crosssell_product = $this->duplicateFieldValueFromList($crosssellProducts, 'product_id');
                $this->setProductRelation($crosssell_product, $product_mage_id, 5);
            }
        }
    }

    /**
     * Query for get data of main table use for import customer
     *
     * @return string
     */
    protected function _getCustomersMainQuery() {
        $id_src = $this->_notice['customers']['id_src'];
        $limit = $this->_notice['setting']['customers'];
        $query = "SELECT * FROM _DBPRF_customer_entity WHERE entity_id > {$id_src} ORDER BY entity_id ASC LIMIT {$limit}";
        return $query;
    }

    /**
     * Query for get data relation use for import customer
     *
     * @param array $customers : Data of connector return for query function getCustomersMainQuery
     * @return array
     */
    protected function _getCustomersExtQuery($customers) {
        $customerIds = $this->duplicateFieldValueFromList($customers['object'], 'entity_id');
        $customer_ids_query = $this->arrayToInCondition($customerIds);
        $groupIds = $this->duplicateFieldValueFromList($customers['object'], 'group_id');
        $group_cus_id = $this->arrayToInCondition($groupIds);
        $websiteIds = $this->duplicateFieldValueFromList($customers['object'], 'website_id');
        $web_ids = $this->arrayToInCondition($websiteIds);
        $ext_query = array(
            'core_store' => "SELECT * FROM _DBPRF_core_store WHERE code != 'admin' AND website_id IN {$web_ids}",
            'eav_attribute' => "SELECT * FROM _DBPRF_eav_attribute WHERE entity_type_id = {$this->_notice['extend']['customer']} OR entity_type_id = {$this->_notice['extend']['customer_address']}",
            'customer_entity_datetime' => "SELECT * FROM _DBPRF_customer_entity_datetime WHERE entity_id IN {$customer_ids_query}",
            'customer_entity_decimal' => "SELECT * FROM _DBPRF_customer_entity_decimal WHERE entity_id IN {$customer_ids_query}",
            'customer_entity_int' => "SELECT * FROM _DBPRF_customer_entity_int WHERE entity_id IN {$customer_ids_query}",
            'customer_entity_text' => "SELECT * FROM _DBPRF_customer_entity_text WHERE entity_id IN {$customer_ids_query}",
            'customer_entity_varchar' => "SELECT * FROM _DBPRF_customer_entity_varchar WHERE entity_id IN {$customer_ids_query}",
            'customer_address_entity' => "SELECT * FROM _DBPRF_customer_address_entity WHERE parent_id IN {$customer_ids_query}",
            'newsletter_subscriber' => "SELECT * FROM _DBPRF_newsletter_subscriber WHERE customer_id IN {$customer_ids_query}",
            'customer_group' => "SELECT * FROM _DBPRF_customer_group WHERE customer_group_id IN {$group_cus_id}",
        );
        return $ext_query;
    }

    /**
     * Query for get data relation use for import customer
     *
     * @param array $customers : Data of connector return for query function getCustomersMainQuery
     * @param array $customersExt : Data of connector return for query function getCustomersExtQuery
     * @return array
     */
    protected function _getCustomersExtRelQuery($customers, $customersExt) {
        $addressIds = $this->duplicateFieldValueFromList($customersExt['object']['customer_address_entity'], 'entity_id');
        $address_id = $this->arrayToInCondition($addressIds);
        $ext_rel_query = array(
            'customer_address_entity_datetime' => "SELECT * FROM _DBPRF_customer_address_entity_datetime WHERE entity_id IN {$address_id}",
            'customer_address_entity_decimal' => "SELECT * FROM _DBPRF_customer_address_entity_decimal WHERE entity_id IN {$address_id}",
            'customer_address_entity_int' => "SELECT * FROM _DBPRF_customer_address_entity_int WHERE entity_id IN {$address_id}",
            'customer_address_entity_text' => "SELECT * FROM _DBPRF_customer_address_entity_text WHERE entity_id IN {$address_id}",
            'customer_address_entity_varchar' => "SELECT * FROM _DBPRF_customer_address_entity_varchar WHERE entity_id IN {$address_id}",
        );
        return $ext_rel_query;
    }

    /**
     * Get primary key of source customer main
     *
     * @param array $customer : One row of object in function getCustomersMain
     * @param array $customersExt : Data of function getCustomersExt
     * @return int
     */
    public function getCustomerId($customer, $customersExt) {
        return $customer['entity_id'];
    }

    /**
     * Convert source data to data import
     *
     * @param array $customer : One row of object in function getCustomersMain
     * @param array $customersExt : Data of function getCustomersExt
     * @return array
     */
    public function convertCustomer($customer, $customersExt) {
        if (Custom::CUSTOMER_CONVERT) {
            return $this->_custom->convertCustomerCustom($this, $customer, $customersExt);
        }
        $cus_data = array();
        $attribute = array();
        foreach ($customersExt['object']['eav_attribute'] as $row) {
            if ($row['entity_type_id'] == $this->_notice['extend']['customer']) {
                $attribute[$row['attribute_code']] = $row['attribute_id'];
            }
        }
        $varchar = $this->getListFromListByField($customersExt['object']['customer_entity_varchar'], 'entity_id', $customer['entity_id']);
        $text = $this->getListFromListByField($customersExt['object']['customer_entity_text'], 'entity_id', $customer['entity_id']);
        $int = $this->getListFromListByField($customersExt['object']['customer_entity_int'], 'entity_id', $customer['entity_id']);
        $decimal = $this->getListFromListByField($customersExt['object']['customer_entity_decimal'], 'entity_id', $customer['entity_id']);
        $datetime = $this->getListFromListByField($customersExt['object']['customer_entity_datetime'], 'entity_id', $customer['entity_id']);
        if ($this->_notice['config']['add_option']['pre_cus']) {
            $cus_data['id'] = $customer['entity_id'];
        }
        $store_id = $this->getRowValueFromListByField($customersExt['object']['core_store'], 'website_id', $customer['website_id'], 'store_id');
        $cus_data['website_id'] = $store_id && isset($this->_notice['config']['languages'][$store_id]) ? $this->getWebsiteIdByStoreId($this->_notice['config']['languages'][$store_id]) : $this->_notice['config']['website_id'];
        $cus_data['email'] = $customer['email'];
        $cus_data['is_active'] = $customer['is_active'];
        $cus_data['created_at'] = $customer['created_at'];
        $cus_data['updated_at'] = $customer['updated_at'];
        //$group_id = $this->getRowFromListByField($customersExt['object']['customer_group'], 'customer_group_id', $customer['group_id']);
        //$group_import = $this->_importCustomerGroup($group_id);
        if (isset($this->_notice['config']['customer_group'][$customer['group_id']])) {
            //$this->_taxCustomerGroupSuccess($group_id['customer_group_id'], $group_import['mage_id']);
            $cus_data['group_id'] = $this->_notice['config']['customer_group'][$customer['group_id']];
        } else {
            $cus_data['group_id'] = 1;
        }
        //$subscribed = $this->getRowValueFromListByField($customersExt['object']['newsletter_subscriber'], 'customer_id', $customer['entity_id'], 'subscriber_status');
        //$cus_data['is_subscribed'] = ($subscribed == '1') ? 1 : 0;
        $cus_data['prefix'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['prefix'], 'value');
        $cus_data['firstname'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['firstname'], 'value');
        $cus_data['middlename'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['middlename'], 'value');
        $cus_data['lastname'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['lastname'], 'value');
        $cus_data['suffix'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['suffix'], 'value');
        $cus_data['dob'] = $this->getRowValueFromListByField($datetime, 'attribute_id', $attribute['dob'], 'value');
        $cus_data['taxvat'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['taxvat'], 'value');
        $cus_data['gender'] = $this->getRowValueFromListByField($int, 'attribute_id', $attribute['gender'], 'value');
        $cus_data['password_hash'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['password_hash'], 'value');
        $custom = $this->_custom->convertCustomerCustom($this, $customer, $customersExt);
        if ($custom) {
            $cus_data = array_merge($cus_data, $custom);
        }
        return array(
            'result' => 'success',
            'data' => $cus_data
        );
    }

    /**
     * Process after one customer import successful
     *
     * @param int $customer_mage_id : Id of customer import to magento
     * @param array $data : Data of function convertCustomer
     * @param array $customer : One row of object function getCustomersMain
     * @param array $customersExt : Data of function getCustomersExt
     * @return boolean
     */
    public function afterSaveCustomer($customer_mage_id, $data, $customer, $customersExt) {
        if (parent::afterSaveCustomer($customer_mage_id, $data, $customer, $customersExt)) {
            return;
        }
        $subscribed = $this->getRowValueFromListByField($customersExt['object']['newsletter_subscriber'], 'customer_id', $customer['entity_id'], 'subscriber_status');
        if ($subscribed == 1) {
            $this->subscribeCustomerById($customer_mage_id);
        }
        $attribute_cus = array();
        foreach ($customersExt['object']['eav_attribute'] as $row) {
            if ($row['entity_type_id'] == $this->_notice['extend']['customer']) {
                $attribute_cus[$row['attribute_code']] = $row['attribute_id'];
            }
        }
        $var_char = $this->getListFromListByField($customersExt['object']['customer_entity_varchar'], 'entity_id', $customer['entity_id']);
        $password = $this->getRowValueFromListByField($var_char, 'attribute_id', $attribute_cus['password_hash'], 'value');
        $this->_importCustomerRawPass($customer_mage_id, $password);
        $cusAdd = $this->getListFromListByField($customersExt['object']['customer_address_entity'], 'parent_id', $customer['entity_id']);
        if ($cusAdd) {
            $attribute = array();
            foreach ($customersExt['object']['eav_attribute'] as $row) {
                if ($row['entity_type_id'] == $this->_notice['extend']['customer_address']) {
                    $attribute[$row['attribute_code']] = $row['attribute_id'];
                }
            }
//            $attribute_cus = array();
//            foreach ($customersExt['object']['eav_attribute'] as $row) {
//                if ($row['entity_type_id'] == $this->_notice['extend']['customer']) {
//                    $attribute_cus[$row['attribute_code']] = $row['attribute_id'];
//                }
//            }
            $int = $this->getListFromListByField($customersExt['object']['customer_entity_int'], 'entity_id', $customer['entity_id']);
            $def_billing = $this->getRowValueFromListByField($int, 'attribute_id', $attribute_cus['default_billing'], 'value');
            $def_shipping = $this->getRowValueFromListByField($int, 'attribute_id', $attribute_cus['default_shipping'], 'value');
            foreach ($cusAdd as $row) {
                $address = array();
                $varchar = $this->getListFromListByField($customersExt['object']['customer_address_entity_varchar'], 'entity_id', $row['entity_id']);
                $text = $this->getListFromListByField($customersExt['object']['customer_address_entity_text'], 'entity_id', $row['entity_id']);
                $int = $this->getListFromListByField($customersExt['object']['customer_address_entity_int'], 'entity_id', $row['entity_id']);
                $decimal = $this->getListFromListByField($customersExt['object']['customer_address_entity_decimal'], 'entity_id', $row['entity_id']);
                $datetime = $this->getListFromListByField($customersExt['object']['customer_address_entity_datetime'], 'entity_id', $row['entity_id']);
                $address['prefix'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['prefix'], 'value');
                $address['firstname'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['firstname'], 'value');
                $address['middlename'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['middlename'], 'value');
                $address['lastname'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['lastname'], 'value');
                $address['suffix'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['suffix'], 'value');
                $address['country_id'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['country_id'], 'value');
                $address['street'] = $this->getRowValueFromListByField($text, 'attribute_id', $attribute['street'], 'value');
                $address['postcode'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['postcode'], 'value');
                $address['city'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['city'], 'value');
                $address['region'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['region'], 'value');
                $address['region_id'] = $this->getRegionId($address['region'], $address['country_id']);
                $address['telephone'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['telephone'], 'value');
                $address['company'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['company'], 'value');
                $address['fax'] = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['fax'], 'value');
                $address_ipt = $this->_process->address($address, $customer_mage_id);
                if ($address_ipt['result'] == 'success') {
                    $cus = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customer_mage_id);
                    if ($row['entity_id'] == $def_billing) {
                        $cus->setDefaultBilling($address_ipt['mage_id']);
                    }
                    if ($row['entity_id'] == $def_shipping) {
                        $cus->setDefaultShipping($address_ipt['mage_id']);
                    }
                    $cus->save();
                }
            }
        }
    }

    /**
     * Query for get data use for import order
     *
     * @return array : Response of connector
     */
    protected function _getOrdersMainQuery() {
        $id_src = $this->_notice['orders']['id_src'];
        $limit = $this->_notice['setting']['orders'];
        $query = "SELECT * FROM _DBPRF_sales_flat_order WHERE entity_id > {$id_src} ORDER BY entity_id ASC LIMIT {$limit}";
        return $query;
    }

    /**
     * Query for get data relation use for import order
     *
     * @param array $orders : Data of connector return for query function getOrdersMainQuery
     * @return array
     */
    protected function _getOrdersExtQuery($orders) {
        $orderIds = $this->duplicateFieldValueFromList($orders['object'], 'entity_id');
        $order_id_query = $this->arrayToInCondition($orderIds);
        $ext_query = array(
            'sales_flat_order_item' => "SELECT * FROM _DBPRF_sales_flat_order_item WHERE order_id IN {$order_id_query}",
            'sales_flat_order_address' => "SELECT * FROM _DBPRF_sales_flat_order_address WHERE parent_id IN {$order_id_query}",
            'sales_flat_order_status_history' => "SELECT * FROM _DBPRF_sales_flat_order_status_history WHERE parent_id IN {$order_id_query} ORDER BY entity_id ASC",
            'sales_flat_order_payment' => "SELECT * FROM _DBPRF_sales_flat_order_payment WHERE parent_id IN {$order_id_query}",
            'sales_flat_shipment_track' => "SELECT * FROM _DBPRF_sales_flat_shipment_track WHERE order_id IN {$order_id_query}",
            'sales_flat_invoice' => "SELECT * FROM _DBPRF_sales_flat_invoice WHERE order_id IN {$order_id_query}"
        );
        return $ext_query;
    }

    /**
     * Query for get data relation use for import order
     *
     * @param array $orders : Data of connector return for query function getOrdersMainQuery
     * @param array $ordersExt : Data of connector return for query function getOrdersExtQuery
     * @return array
     */
    protected function _getOrdersExtRelQuery($orders, $ordersExt) {
        $invoiceIds = $this->duplicateFieldValueFromList($ordersExt['object']['sales_flat_invoice'], 'entity_id');
        $invoice_ids_query = $this->arrayToInCondition($invoiceIds);
        $ext_rel_query = array(
            'sales_flat_invoice_comment' => "SELECT * FROM _DBPRF_sales_flat_invoice_comment WHERE parent_id IN {$invoice_ids_query}",
            'sales_flat_invoice_grid' => "SELECT * FROM _DBPRF_sales_flat_invoice_grid WHERE entity_id IN {$invoice_ids_query}",
            'sales_flat_invoice_item' => "SELECT * FROM _DBPRF_sales_flat_invoice_item WHERE parent_id IN {$invoice_ids_query} ORDER BY order_item_id ASC"
        );
        return $ext_rel_query;
    }

    /**
     * Get primary key of source order main
     *
     * @param array $order : One row of object in function getOrdersMain
     * @param array $ordersExt : Data of function getOrdersExt
     * @return int
     */
    public function getOrderId($order, $ordersExt) {
        return $order['entity_id'];
    }

    /**
     * Convert source data to data import
     *
     * @param array $order : One row of object in function getOrdersMain
     * @param array $ordersExt : Data of function getOrdersExt
     * @return array
     */
    public function convertOrder($order, $ordersExt) {
        if (Custom::ORDER_CONVERT) {
            return $this->_custom->convertOrderCustom($this, $order, $ordersExt);
        }
        $data = array();
        $address_order = $this->getListFromListByField($ordersExt['object']['sales_flat_order_address'], 'parent_id', $order['entity_id']);
        //Billing address
        $billing = $this->getRowFromListByField($address_order, 'address_type', 'billing');

        $address_billing['firstname'] = $billing['firstname'];
        $address_billing['middlename'] = $billing['middlename'];
        $address_billing['lastname'] = $billing['lastname'];
        $address_billing['company'] = $billing['company'];
        $address_billing['email'] = $billing['email'];
        $address_billing['street'] = $billing['street'];
        $address_billing['city'] = $billing['city'];
        $address_billing['postcode'] = $billing['postcode'];
        $address_billing['telephone'] = $billing['telephone'];
        $address_billing['country_id'] = $billing['country_id'];
        $address_billing['region'] = $billing['region'];
        $address_billing['fax'] = $billing['fax'];
        $address_billing['region_id'] = $this->getRegionId($address_billing['region'], $address_billing['country_id']);

        //Shipping address
        $shipping = $this->getRowFromListByField($address_order, 'address_type', 'shipping');
        $address_shipping['firstname'] = $shipping['firstname'];
        $address_shipping['middlename'] = $shipping['middlename'];
        $address_shipping['lastname'] = $shipping['lastname'];
        $address_shipping['company'] = $shipping['company'];
        $address_shipping['email'] = $shipping['email'];
        $address_shipping['street'] = $shipping['street'];
        $address_shipping['city'] = $shipping['city'];
        $address_shipping['postcode'] = $shipping['postcode'];
        $address_shipping['telephone'] = $shipping['telephone'];
        $address_shipping['country_id'] = $shipping['country_id'];
        $address_shipping['region'] = $shipping['region'];
        $address_shipping['fax'] = $shipping['fax'];
        $address_shipping['region_id'] = $this->getRegionId($address_shipping['region'], $address_shipping['country_id']);

        //Row product item
        $carts = array();
        $orderProducts = $this->getListFromListByField($ordersExt['object']['sales_flat_order_item'], 'order_id', $order['entity_id']);
        $i = 0;
        foreach ($orderProducts as $key => $row) {
            $cart = array();
            if (!$row['parent_item_id']) {
                $i = $key;
            }
            $product_id = $this->getMageIdProduct($row['product_id']);
            if ($product_id) {
                $cart['product_id'] = $product_id;
            }
            $cart['product_type'] = $row['product_type'];
            $cart['name'] = $row['name'];
            $cart['sku'] = $row['sku'];
            $cart['price'] = $row['price'];
            $cart['original_price'] = $row['original_price'];
            $cart['tax_amount'] = $row['tax_amount'];
            $cart['tax_percent'] = $row['tax_percent'];
            $cart['discount_amount'] = $row['discount_amount'];
            $cart['qty_ordered'] = $row['qty_ordered'];
            $cart['qty_canceled'] = $row['qty_canceled'];
            $cart['qty_invoiced'] = $row['qty_invoiced'];
            $cart['qty_refunded'] = $row['qty_refunded'];
            $cart['qty_shipped'] = $row['qty_shipped'];
            $cart['row_total'] = $row['row_total'];
            $cart['product_options'] = $row['product_options'];
//            $options = array();
//            if ($row['product_options']) {
//                $options = unserialize($row['product_options']);
//            }
//            if ($options) {
//                $opt_only = $attr_only = $bundle_opt = array();
//                if (isset($options['options'])) {
//                    $opt_only = array('options' => $options['options']);
//                }
//                if (isset($options['attributes_info'])) {
//                    $attr_only = array('attributes_info' => $options['attributes_info']);
//                }
//                if (isset($options['bundle_options'])) {
//                    $bundle_opt = array('bundle_options' => $options['bundle_options']);
//                }
//                $product_opt = serialize(array_merge($opt_only, $attr_only, $bundle_opt));
//                $cart['product_options'] = $product_opt;
//            }
            if (!$row['parent_item_id']) {
                $carts[$key] = $cart;
            } else {
                $carts[$i]['children_item'][] = $cart;
            }
        }
        $order_data = array();
        $store_id = ($order['store_id']) ? $this->_notice['config']['languages'][$order['store_id']] : $this->_notice['config']['languages'][$this->_notice['config']['default_lang']];
        $order_data['store_id'] = $store_id;
        $customer_id = $this->getMageIdCustomer($order['customer_id']);
        if ($customer_id) {
            $order_data['customer_id'] = $customer_id;
            $order_data['customer_is_guest'] = false;
        } else {
            $order_data['customer_is_guest'] = true;
        }
        $order_data['customer_email'] = $order['customer_email'];
        $order_data['customer_firstname'] = $order['customer_firstname'];
        $order_data['customer_middlename'] = $order['customer_middlename'];
        $order_data['customer_lastname'] = $order['customer_lastname'];
        if (isset($this->_notice['config']['customer_group'][$order['customer_group_id']])) {
            $order_data['customer_group_id'] = $this->_notice['config']['customer_group'][$order['customer_group_id']];
        } else {
            $order_data['customer_group_id'] = 1;
        }
        $order_data['status'] = isset($this->_notice['config']['order_status'][$order['status']]) ? $this->_notice['config']['order_status'][$order['status']] : 'canceled';
        $order_data['state'] = $this->getOrderStateByStatus($order_data['status']);
        $order_data['subtotal'] = $order['subtotal'];
        $order_data['subtotal_canceled'] = $order['subtotal_canceled'];
        $order_data['subtotal_invoiced'] = $order['subtotal_invoiced'];
        $order_data['subtotal_refunded'] = $order['subtotal_refunded'];
        $order_data['base_subtotal'] = $order['base_subtotal'];
        $order_data['base_subtotal_canceled'] = $order['base_subtotal_canceled'];
        $order_data['base_subtotal_invoiced'] = $order['base_subtotal_invoiced'];
        $order_data['base_subtotal_refunded'] = $order['base_subtotal_refunded'];
        $order_data['shipping_amount'] = $order['shipping_amount'];
        $order_data['shipping_canceled'] = $order['shipping_canceled'];
        $order_data['shipping_invoiced'] = $order['shipping_invoiced'];
        $order_data['shipping_refunded'] = $order['shipping_refunded'];
        $order_data['shipping_tax_amount'] = $order['shipping_tax_amount'];
        $order_data['shipping_tax_refunded'] = $order['shipping_tax_refunded'];
        $order_data['base_shipping_amount'] = $order['base_shipping_amount'];
        $order_data['base_shipping_canceled'] = $order['base_shipping_canceled'];
        $order_data['base_shipping_invoiced'] = $order['base_shipping_invoiced'];
        $order_data['base_shipping_refunded'] = $order['base_shipping_refunded'];
        $order_data['base_shipping_tax_amount'] = $order['base_shipping_tax_amount'];
        $order_data['base_shipping_tax_refunded'] = $order['base_shipping_tax_refunded'];
        $order_data['shipping_description'] = $order['shipping_description'];
        $order_data['tax_amount'] = $order['tax_amount'];
        $order_data['tax_canceled'] = $order['tax_canceled'];
        $order_data['tax_invoiced'] = $order['tax_invoiced'];
        $order_data['tax_refunded'] = $order['tax_refunded'];
        $order_data['base_tax_amount'] = $order['base_tax_amount'];
        $order_data['base_tax_canceled'] = $order['base_tax_canceled'];
        $order_data['base_tax_invoiced'] = $order['base_tax_invoiced'];
        $order_data['base_tax_refunded'] = $order['base_tax_refunded'];
        $order_data['discount_amount'] = $order['discount_amount'];
        $order_data['discount_canceled'] = $order['discount_canceled'];
        $order_data['discount_invoiced'] = $order['discount_invoiced'];
        $order_data['discount_refunded'] = $order['discount_refunded'];
        $order_data['base_discount_amount'] = $order['base_discount_amount'];
        $order_data['base_discount_canceled'] = $order['base_discount_canceled'];
        $order_data['base_discount_invoiced'] = $order['base_discount_invoiced'];
        $order_data['base_discount_refunded'] = $order['base_discount_refunded'];
        $order_data['grand_total'] = $order['grand_total'];
        $order_data['base_grand_total'] = $order['base_grand_total'];
        $order_data['base_total_invoiced_cost'] = $order['base_total_invoiced_cost'];
        $order_data['base_total_offline_refunded'] = $order['base_total_offline_refunded'];
        $order_data['base_total_online_refunded'] = $order['base_total_online_refunded'];
        $order_data['base_total_canceled'] = $order['base_total_canceled'];
        $order_data['base_total_invoiced'] = $order['base_total_invoiced'];
        $order_data['base_total_refunded'] = $order['base_total_refunded'];
        $order_data['total_paid'] = $order['total_paid'];
        $order_data['total_refunded'] = $order['total_refunded'];
        $order_data['total_offline_refunded'] = $order['total_offline_refunded'];
        $order_data['total_online_refunded'] = $order['total_online_refunded'];
        $order_data['base_total_paid'] = $order['base_total_paid'];
        $order_data['base_to_global_rate'] = $order['base_to_global_rate'];
        $order_data['base_to_order_rate'] = $order['base_to_order_rate'];
        $order_data['store_to_base_rate'] = $order['store_to_base_rate'];
        $order_data['store_to_order_rate'] = $order['store_to_order_rate'];
        $order_data['base_currency_code'] = $order['base_currency_code'];
        $order_data['global_currency_code'] = $order['global_currency_code'];
        $order_data['store_currency_code'] = $order['store_currency_code'];
        $order_data['order_currency_code'] = $order['order_currency_code'];
        $order_data['created_at'] = $order['created_at'];
        $order_data['updated_at'] = $order['updated_at'];
        $data['address_billing'] = $address_billing;
        $data['address_shipping'] = $address_shipping;
        $data['order'] = $order_data;
        $data['carts'] = $carts;
        $data['order_src_id'] = $order['increment_id'];
        $data['payment'] = $this->getRowValueFromListByField($ordersExt['object']['sales_flat_order_payment'], 'parent_id', $order['entity_id'], 'method');
        $custom = $this->_custom->convertOrderCustom($this, $order, $ordersExt);
        if ($custom) {
            $data = array_merge($data, $custom);
        }
        return array(
            'result' => 'success',
            'data' => $data
        );
    }

    /**
     * Process after one order save successful
     *
     * @param int $order_mage_id : Id of order import to magento
     * @param array $data : Data of function convertOrder
     * @param array $order : One row of object in function getOrdersMain
     * @param array $ordersExt : Data of function getOrdersExt
     * @return boolean
     */
    public function afterSaveOrder($order_mage_id, $data, $order, $ordersExt) {
        if (parent::afterSaveOrder($order_mage_id, $data, $order, $ordersExt)) {
            return;
        }
        $orderHistory = $this->getListFromListByField($ordersExt['object']['sales_flat_order_status_history'], 'parent_id', $order['entity_id']);
        foreach ($orderHistory as $key => $row) {
            $order_status_data = array();
            $order_status_data['status'] = isset($this->_notice['config']['order_status'][$row['status']]) ? $this->_notice['config']['order_status'][$row['status']] : 'canceled';
            if ($order_status_data['status']) {
                $order_status_data['state'] = $this->getOrderStateByStatus($order_status_data['status']);
            }
            $order_status_data['comment'] = ($row['comment']) ? $row['comment'] : '';
            if ($key == 0) {
                $shipmentTrack = $this->getRowFromListByField($ordersExt['object']['sales_flat_shipment_track'], 'order_id', $order['entity_id']);
                if ($shipmentTrack) {
                    $track_number = $shipmentTrack['number'];
                    $order_status_data['comment'] .= "<b>Shipment title: </b>" . $shipmentTrack['title'] . "<br /><b>Shipment track number: </b>" . $track_number;
                }
            }
            $order_status_data['is_customer_notified'] = ($row['is_customer_notified']) ? 1 : 0;
            $order_status_data['created_at'] = $row['created_at'];
            $order_status_data['updated_at'] = $row['created_at'];
            $this->_process->ordersComment($order_mage_id, $order_status_data);
        }
        //Invoice
        $orderInvoice = $this->getRowFromListByField($ordersExt['object']['sales_flat_invoice'], 'order_id', $order['entity_id']);
        if ($orderInvoice) {
            $invoiceData = array();
            $orderMage = $this->_objectManager->create('Magento\Sales\Model\Order')->load($order_mage_id);
            $bill_id = $orderMage->getBillingAddressId();
            $ship_id = $orderMage->getShippingAddressId();
            $store_id = ($orderInvoice['store_id']) ? $this->_notice['config']['languages'][$orderInvoice['store_id']] : $this->_notice['config']['languages'][$this->_notice['config']['default_lang']];
            $invoice_data = array(
                'store_id' => $store_id,
                'base_grand_total' => $orderInvoice['base_grand_total'],
                'shipping_tax_amount' => $orderInvoice['shipping_tax_amount'],
                'tax_amount' => $orderInvoice['tax_amount'],
                'base_tax_amount' => $orderInvoice['base_tax_amount'],
                'store_to_order_rate' => $orderInvoice['store_to_order_rate'],
                'base_shipping_tax_amount' => $orderInvoice['base_shipping_tax_amount'],
                'base_discount_amount' => $orderInvoice['base_discount_amount'],
                'base_to_order_rate' => $orderInvoice['base_to_order_rate'],
                'grand_total' => $orderInvoice['grand_total'],
                'shipping_amount' => $orderInvoice['shipping_amount'],
                'subtotal_incl_tax' => $orderInvoice['subtotal_incl_tax'],
                'base_subtotal_incl_tax' => $orderInvoice['base_subtotal_incl_tax'],
                'store_to_base_rate' => $orderInvoice['store_to_base_rate'],
                'base_shipping_amount' => $orderInvoice['base_shipping_amount'],
                'total_qty' => $orderInvoice['total_qty'],
                'base_to_global_rate' => $orderInvoice['base_to_global_rate'],
                'subtotal' => $orderInvoice['subtotal'],
                'base_subtotal' => $orderInvoice['base_subtotal'],
                'discount_amount' => $orderInvoice['discount_amount'],
                'order_id' => $order_mage_id,
                'state' => $orderInvoice['state'],
                'store_currency_code' => $orderInvoice['store_currency_code'],
                'order_currency_code' => $orderInvoice['order_currency_code'],
                'base_currency_code' => $orderInvoice['base_currency_code'],
                'global_currency_code' => $orderInvoice['global_currency_code'],
                'created_at' => $orderInvoice['created_at'],
                'updated_at' => $orderInvoice['updated_at'],
                'shipping_incl_tax' => $orderInvoice['shipping_incl_tax'],
                'base_shipping_incl_tax' => $orderInvoice['base_shipping_incl_tax'],
                'base_total_refunded' => $orderInvoice['base_total_refunded'],
                'discount_description' => $orderInvoice['discount_description'],
                'billing_address_id' => $bill_id? $bill_id : null,
                'shipping_address_id' => $ship_id ? $ship_id : null
            );
            $invoiceItems = $this->getListFromListByField($ordersExt['object']['sales_flat_invoice_item'], 'parent_id', $orderInvoice['entity_id']);
            $items = array();
            foreach ($invoiceItems as $row) {
                $item = array(
                    'base_price' => $row['base_price'],
                    'tax_amount' => $row['tax_amount'],
                    'base_row_total' => $row['base_row_total'],
                    'discount_amount' => $row['discount_amount'],
                    'row_total' => $row['row_total'],
                    'base_discount_amount' => $row['base_discount_amount'],
                    'price_incl_tax' => $row['price_incl_tax'],
                    'base_tax_amount' => $row['base_tax_amount'],
                    'base_price_incl_tax' => $row['base_price_incl_tax'],
                    'qty' => $row['qty'],
                    'price' => $row['price'],
                    'base_row_total_incl_tax' => $row['base_row_total_incl_tax'],
                    'row_total_incl_tax' => $row['row_total_incl_tax'],
                    'additional_data' => $row['additional_data'],
                    'description' => $row['description'],
                    'sku' => $row['sku'],
                    'name' => $row['name']
                );
                $product_id = $this->getMageIdProduct($row['product_id']);
                if ($product_id) {
                    $item['product_id'] = $product_id;
                }
                $items[] = $item;
            }
            $invoiceComments = $this->getListFromListByField($ordersExt['object']['sales_flat_invoice_comment'], 'parent_id', $orderInvoice['entity_id']);
            $comments = array();
            foreach ($invoiceComments as $row) {
                $comment = array(
                    'is_customer_notified' => $row['is_customer_notified'],
                    'is_visible_on_front' => $row['is_visible_on_front'],
                    'comment' => $row['comment'],
                    'created_at' => $row['created_at']
                );
                $comments[] = $comment;
            }
            $invoiceData['invoice'] = $invoice_data;
            $invoiceData['item'] = $items;
            $invoiceData['comment'] = $comments;
            $this->_process->ordersInvoice($order_mage_id, $invoiceData);
        }
    }

    /**
     * Query for get main data use for import review
     *
     * @return string
     */
    protected function _getReviewsMainQuery() {
        $id_src = $this->_notice['reviews']['id_src'];
        $limit = $this->_notice['setting']['reviews'];
        $query = "SELECT * FROM _DBPRF_review WHERE review_id > {$id_src} ORDER BY review_id ASC LIMIT {$limit}";
        return $query;
    }

    /**
     * Query for get relation data use for import reviews
     *
     * @param array $reviews : Data of connector return for query function getReviewsMainQuery
     * @return array
     */
    protected function _getReviewsExtQuery($reviews) {
        $reviewIds = $this->duplicateFieldValueFromList($reviews['object'], 'review_id');
        $review_id_query = $this->arrayToInCondition($reviewIds);
        $ext_query = array(
            'review_detail' => "SELECT * FROM _DBPRF_review_detail WHERE review_id IN {$review_id_query}",
            'rating_option_vote' => "SELECT * FROM _DBPRF_rating_option_vote WHERE review_id IN {$review_id_query}",
        );
        return $ext_query;
    }

    /**
     * Query for get relation data use for import reviews
     *
     * @param array $reviews : Data of connector return for query function getReviewsMainQuery
     * @param array $reviewsExt : Data of connector return for query function getReviewsExtQuery
     * @return array
     */
    protected function _getReviewsExtRelQuery($reviews, $reviewsExt) {
        return array();
    }

    /**
     * Get primary key of source review main
     *
     * @param array $review : One row of object in function getReviewsMain
     * @param array $reviewsExt : Data of function getReviewsExt
     * @return int
     */
    public function getReviewId($review, $reviewsExt) {
        return $review['review_id'];
    }

    /**
     * Convert source data to data import
     *
     * @param array $review : One row of object in function getReviewsMain
     * @param array $reviewsExt : Data of function getReviewsExt
     * @return array
     */
    public function convertReview($review, $reviewsExt) {
        if (Custom::REVIEW_CONVERT) {
            return $this->_custom->convertReviewCustom($this, $review, $reviewsExt);
        }
        $product_mage_id = $this->getMageIdProduct($review['entity_pk_value']);
        if (!$product_mage_id) {
            return array(
                'result' => 'warning',
                'msg' => $this->consoleWarning("Review Id = {$review['review_id']} import failed. Error: Product Id = {$review['entity_pk_value']} not imported!")
            );
        }
        $store_id = $this->_notice['config']['languages'][$this->_notice['config']['default_lang']];
        $data = array();
        $review_detail = $this->getRowFromListByField($reviewsExt['object']['review_detail'], 'review_id', $review['review_id']);
        $data['entity_pk_value'] = $product_mage_id;
        $data['status_id'] = $review['status_id'];
        $data['title'] = $review_detail['title'];
        $data['detail'] = $review_detail['detail'];
        $data['entity_id'] = 1;
        $data['stores'] = array($store_id);
        if ($review_detail['customer_id']) {
            $data['customer_id'] = ($this->getMageIdCustomer($review_detail['customer_id'])) ? $this->getMageIdCustomer($review_detail['customer_id']) : null;
        }
        $data['nickname'] = $review_detail['nickname'];
        $rating_votes = $this->getListFromListByField($reviewsExt['object']['rating_option_vote'], 'review_id', $review['review_id']);
        $rating = array();
        if(is_array($rating_votes) && count($rating_votes)){
            foreach ($rating_votes as $row) {
                $rating[$row['rating_id']] = $row['option_id'];
            }
        }
        $data['rating'] = $rating;
        $data['created_at'] = $review['created_at'];
        $data['review_id_import'] = $review['review_id'];
        $custom = $this->_custom->convertReviewCustom($this, $review, $reviewsExt);
        if ($custom) {
            $data = array_merge($data, $custom);
        }
        return array(
            'result' => 'success',
            'data' => $data
        );
    }

############################################################ Extend function ##################################

    /**
     * Import parent category if not exists by id
     */
    protected function _importCategoryParent($parent_id) {
        $categories = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => "SELECT * FROM _DBPRF_catalog_category_entity WHERE entity_id = {$parent_id}"
        ));
        if (!$categories || $categories['result'] != 'success') {
            return $this->errorConnector(true);
        }
        $categoriesExt = $this->getCategoriesExt($categories);
        if ($categoriesExt['result'] != 'success') {
            return $categoriesExt;
        }
        if (!$categories['object']) {
            return array(
                'result' => 'warning',
            );
        }
        $category = $categories['object'][0];
        $convert = $this->convertCategory($category, $categoriesExt);
        if ($convert['result'] != 'success') {
            return array(
                'result' => 'warning',
            );
        }
        $data = $convert['data'];
        $category_ipt = $this->_process->category($data);
        if ($category_ipt['result'] == 'success') {
            $this->categorySuccess($parent_id, $category_ipt['mage_id']);
            $this->afterSaveCategory($category_ipt['mage_id'], $data, $category, $categoriesExt);
        } else {
            $category_ipt['result'] = 'warning';
        }
        return $category_ipt;
    }

    protected function _importChildrenConfigurableProduct($parent, $children, $productsExt) {
        $result = false;
        $attr_int = $dataChildes = $attrMage = array();
        $entity_type_id = $this->_objectManager->create('Magento\Eav\Model\Entity')->setType(\Magento\Catalog\Model\Product::ENTITY)->getTypeId();
        //$attribute_set_id = $this->_notice['config']['attribute_set_id'];
        $attributeIds = $this->getListFromListByField($productsExt['object']['catalog_product_super_attribute'], 'product_id', $parent['entity_id']);
        $attributeIds = $this->duplicateFieldValueFromList($attributeIds, 'attribute_id');
        $used_attribute = array();
        foreach ($attributeIds as $row) {
            $attributeSets = $this->getListFromListByField($productsExt['object']['eav_entity_attribute'], 'attribute_id', $row);
            $attribute_set_src_ids = $this->duplicateFieldValueFromList($attributeSets, 'attribute_set_id');
            $attribute_set_id_desc = array($parent['attribute_set_id']);
            foreach ($attribute_set_src_ids as $attr_set) {
                if (isset($this->_notice['config']['attributes'][$attr_set])) {
                    $attribute_set_id_desc[] = $this->_notice['config']['attributes'][$attr_set];
                }
            }
            $attribute_set_id = array_unique($attribute_set_id_desc);
            $attribute = $this->getRowFromListByField($productsExt['object']['eav_attribute'], 'attribute_id', $row);
            $info = $this->getRowFromListByField($productsExt['object']['catalog_eav_attribute'], 'attribute_id', $row);
            foreach ($children as $child) {
                $child_opt_vals = $this->getListFromListByField($productsExt['object']['catalog_product_entity_int'], 'entity_id', $child);
                $value = $this->getRowValueFromListByField($child_opt_vals, 'attribute_id', $row, 'value');
                $option_store = $this->getListFromListByField($productsExt['object']['eav_attribute_option_value'], 'option_id', $value);
                $option = array();
                $option_mage = array();
                foreach ($option_store as $opt) {
                    if ($opt['store_id'] == '0') {
                        $option['0'] = $opt['value'];
                        continue;
                    }
                    $option[$this->_notice['config']['languages'][$opt['store_id']]] = $opt['value'];
                }
                $option_mage['option_0'] = $option;
                $attr_import = $this->_makeAttributeImport($attribute, $option_mage, $attribute_set_id, $info, $entity_type_id, true);
                $attr_after = $this->_process->attribute($attr_import['config'], $attr_import['edit']);
                if (!$attr_after) {
                    return false;
                }
                $attr_int[$child][$attr_after['attribute_id']] = $attr_after['option_ids']['option_0'];
                $attrMage[$attr_after['attribute_id']]['attribute_label'] = $attribute['frontend_label'];
                $attrMage[$attr_after['attribute_id']]['attribute_code'] = $attr_after['attribute_code'];
                $attrMage[$attr_after['attribute_id']]['values'][$attr_after['option_ids']['option_0']] = $attr_after;
            }
            $used_attribute[] = $attribute['attribute_id'];
        }
        foreach ($children as $row) {
            $check_imported = $this->getMageIdProduct($row);
            if ($check_imported) {
                $product_id = $check_imported;
            } else {
                $child_info = $this->getRowFromListByField($productsExt['object']['catalog_product_entity'], 'entity_id', $row);
                $product = array(
                    'type_id' => $child_info['type_id']
                );
                $product = array_merge($this->_convertProduct($child_info, $productsExt, $used_attribute), $product);
                $pro_import = $this->_process->product($product);
                if ($pro_import['result'] !== 'success') {
                    var_dump($row);
                    var_dump($product);
                    return false;
                }
                $this->productSuccess($row, $pro_import['mage_id']);
                $product_id = $pro_import['mage_id'];
            }
            //$this->afterSaveProduct($pro_import['mage_id'], $product, $child_info, $productsExt);
            $optionChild = $attr_int[$row];
            $dataChildes[] = $product_id;
            foreach ($optionChild as $attr => $opt_val) {
                $this->setProAttrSelect($attr, $product_id, $opt_val);
            }
        }
        if ($dataChildes && $attrMage)
            $result = $this->_createConfigProductData($dataChildes, $attrMage);
        return $result;
    }

    protected function _makeAttributeImport($attribute, $option, $attribute_set, $info, $entity_type_id, $is_global = false) {
        if ($info['apply_to']) {
            $apply = explode(',', $info['apply_to']);
        } else {
            $apply = null;
        }
        $config = array(
            'entity_type_id' => $entity_type_id,
            'attribute_code' => substr($attribute['attribute_code'], 0, 30),
            'attribute_set_id' => $attribute_set,
            'frontend_input' => $attribute['frontend_input'],
            'frontend_label' => array($attribute['frontend_label']),
            'apply_to' => $info['apply_to'],
            'is_visible_on_front' => $info['is_visible_on_front'],
            'is_global' => $is_global ? 1 : (int) $info['is_global'],
            'option' => array(
                'value' => ($option) ? $option : array()
            )
        );
        $edit = array(
            'apply_to' => $info['apply_to'],
            'is_global' => $is_global ? 1 : (int) $info['is_global'],
        );
        $result['config'] = $config;
        $result['edit'] = $edit;
        return $result;
    }

    protected function _createConfigProductData($dataChildes, $attrMage) {
        $attribute_config = array();
        //$result['associated_product_ids'] = $dataChildes;
        $i = 0;
        foreach ($attrMage as $key => $attribute) {
            $dad = array(
                'label' => $attribute['attribute_label'],
                'attribute_id' => $key,
                'code' => $attribute['attribute_code'],
                'position' => $i,
            );
            $values = array();
            foreach ($attribute['values'] as $option) {
                $child = array(
                    'include' => 1,
                    'value_index' => $option['option_ids']['option_0'],
                );
                $values[] = $child;
            }
            $dad['values'] = $values;
            $attribute_config[$key] = $dad;
            $i++;
        }
        $extensionAttributes = $this->_objectManager->create('Magento\Catalog\Model\Product')->getExtensionAttributes();
        $extensionAttributes->setConfigurableProductLinks(array_filter($dataChildes));
        
        $configurableOptions = $this->_objectManager->create('Magento\ConfigurableProduct\Helper\Product\Options\Factory')->create($attribute_config);
        $extensionAttributes->setConfigurableProductOptions($configurableOptions);
        
        $result['extension_attributes'] = $extensionAttributes;
        
        //$result['configurable_attributes_data'] = $attribute_config;
        $result['can_save_configurable_attributes'] = 1;
        $result['affect_configurable_product_attributes'] = 1;
        return $result;
    }

    protected function _importChildrenBundleProduct($parent_id, $children, $productsExt) {
        $optionData = $selectionData = array();
        $bundleOpts = $this->getListFromListByField($productsExt['object']['catalog_product_bundle_option'], 'parent_id', $parent_id);
        if ($bundleOpts) {
            foreach ($bundleOpts as $row) {
                $title = $this->getRowValueFromListByField($productsExt['object']['catalog_product_bundle_option_value'], 'option_id', $row['option_id'], 'title');
                $option = array(
                    'required' => $row['required'],
                    'option_id' => '',
                    'position' => $row['position'],
                    'type' => $row['type'],
                    'title' => $title,
                    'default_title' => $title,
                    'delete' => '',
                );
                $optionData[] = $option;
                $selections = $this->getListFromListByField($productsExt['object']['catalog_product_bundle_selection'], 'option_id', $row['option_id']);
                $group_select = array();
                foreach ($selections as $value) {
                    $check_imported = $this->getMageIdProduct($value['product_id']);
                    if ($check_imported) {
                        $product_id = $check_imported;
                    } else {
                        $child_product = $this->getRowFromListByField($productsExt['object']['catalog_product_entity'], 'entity_id', $value['product_id']);
                        $product = array(
                            'type_id' => $child_product['type_id']
                        );
                        $product = array_merge($this->_convertProduct($child_product, $productsExt), $product);
                        $pro_import = $this->_process->product($product);
                        if ($pro_import['result'] !== 'success') {
                            return false;
                        }
                        $this->productSuccess($value['product_id'], $pro_import['mage_id']);
                        $product_id = $pro_import['mage_id'];
                    }
                    //$this->afterSaveProduct($pro_import['mage_id'], $product, $child_product, $productsExt);
                    $selection = array(
                        'product_id' => $product_id,
                        'selection_qty' => $value['selection_qty'],
                        'selection_can_change_qty' => $value['selection_can_change_qty'],
                        'position' => $value['position'],
                        'is_default' => $value['is_default'],
                        'selection_id' => '',
                        'selection_price_type' => $value['selection_price_type'],
                        'selection_price_value' => $value['selection_price_value'],
                        'option_id' => '',
                        'delete' => '',
                    );
                    $group_select[] = $selection;
                }
                $selectionData[] = $group_select;
            }
        }
        $result = array(
            'can_save_custom_options' => true,
            'bundle_options_data' => $optionData,
            'bundle_selections_data' => $selectionData,
            'can_save_bundle_selections' => true,
            'affect_bundle_product_selections' => true,
        );
        return $result;
    }

    /**
     * Convert source data to data import
     *
     * @param array $product : One row of object in function getProductsMain
     * @param array $productsExt : Data of function getProductsMain
     * @param array $used_attribute : Id of attributes was imported to child product if exist
     * @return array
     */
    protected function _convertProduct($product, $productsExt, $used_attribute = array()) {
        $pro_data = array();
        $pro_data_add = array();
        $categories = array();
        $proCats = $this->getListFromListByField($productsExt['object']['catalog_category_product'], 'product_id', $product['entity_id']);
        if ($proCats) {
            foreach ($proCats as $pro_cat) {
                $cat_id = $this->getMageIdCategory($pro_cat['category_id']);
                if ($cat_id) {
                    $categories[] = $cat_id;
                }
            }
        }
        $pro_data['category_ids'] = $categories;
        $pro_data['type_id'] = $product['type_id'];
        $webIds = array();
        $websites = $this->getListFromListByField($productsExt['object']['catalog_product_website'], 'product_id', $product['entity_id']);
        foreach ($websites as $web) {
            $store_id = $this->getRowValueFromListByField($productsExt['object']['core_store'], 'website_id', $web['website_id'], 'store_id');
            $webIds[] = $store_id && isset($this->_notice['config']['languages'][$store_id]) ? $this->getWebsiteIdByStoreId($this->_notice['config']['languages'][$store_id]) : $this->_notice['config']['website_id'];
        }
        $webIds = array_values(array_unique($webIds));
        $pro_data['website_ids'] = $webIds;
        $pro_data['store_ids'] = array_values($this->_notice['config']['languages']);
        $pro_data['attribute_set_id'] = isset($this->_notice['config']['attributes'][$product['attribute_set_id']]) ? $this->_notice['config']['attributes'][$product['attribute_set_id']] : $this->_notice['config']['attribute_set_id'];
        $attribute = array();
        foreach ($productsExt['object']['eav_attribute'] as $row) {
            $attribute[$row['attribute_code']] = $row['attribute_id'];
            $attribute_type[$row['attribute_code']] = $row['backend_type'];
            $attribute_input[$row['attribute_code']] = $row['frontend_input'];
            $attribute_defined[$row['attribute_code']] = $row['is_user_defined'];
        }
        $varchar = $this->getListFromListByField($productsExt['object']['catalog_product_entity_varchar'], 'entity_id', $product['entity_id']);
        $text = $this->getListFromListByField($productsExt['object']['catalog_product_entity_text'], 'entity_id', $product['entity_id']);
        $int = $this->getListFromListByField($productsExt['object']['catalog_product_entity_int'], 'entity_id', $product['entity_id']);
        $decimal = $this->getListFromListByField($productsExt['object']['catalog_product_entity_decimal'], 'entity_id', $product['entity_id']);
        $datetime = $this->getListFromListByField($productsExt['object']['catalog_product_entity_datetime'], 'entity_id', $product['entity_id']);
        $names = $this->getListFromListByField($varchar, 'attribute_id', $attribute['name']);
        if ($pro_data['type_id'] == 'simple') {
            $pro_data['product_has_weight'] = 1;
        }
        if ($pro_data['type_id'] == 'bundle') {
            $pro_data['store_id'] = 1;
            $pro_data['price_type'] = $this->getRowValueFromListByField($int, 'attribute_id', $attribute['price_type'], 'value');
            $pro_data['sku_type'] = $this->getRowValueFromListByField($int, 'attribute_id', $attribute['sku_type'], 'value');
            $pro_data['weight_type'] = $this->getRowValueFromListByField($int, 'attribute_id', $attribute['weight_type'], 'value');
            $pro_data['price_view'] = $this->getRowValueFromListByField($int, 'attribute_id', $attribute['price_view'], 'value');
        }
        $pro_data['name'] = $this->getRowValueFromListByField($names, 'store_id', '0', 'value');
        $descriptions = $this->getListFromListByField($text, 'attribute_id', $attribute['description']);
        $pro_data['description'] = $this->getRowValueFromListByField($descriptions, 'store_id', '0', 'value');
        $short_descriptions = $this->getListFromListByField($text, 'attribute_id', $attribute['short_description']);
        $pro_data['short_description'] = $this->getRowValueFromListByField($short_descriptions, 'store_id', '0', 'value');
        $pro_data['sku'] = $this->createProductSku($product['sku'], $this->_notice['config']['languages']);
        $pro_data['price'] = $this->getRowValueFromListByField($decimal, 'attribute_id', $attribute['price'], 'value');
        if ($product['type_id'] != 'configurable') {
            $pro_data['special_price'] = $this->getRowValueFromListByField($decimal, 'attribute_id', $attribute['special_price'], 'value');
            $pro_data['special_from_date'] = $this->getRowValueFromListByField($datetime, 'attribute_id', $attribute['special_from_date'], 'value');
            $pro_data['special_to_date'] = $this->getRowValueFromListByField($datetime, 'attribute_id', $attribute['special_to_date'], 'value');
        }
        $pro_data['weight'] = $this->getRowValueFromListByField($decimal, 'attribute_id', $attribute['weight'], 'value');
        if (isset($attribute['manufacturer'])) {
            $manu_id = $this->getRowValueFromListByField($int, 'attribute_id', $attribute['manufacturer'], 'value');
            if ($manufacture_mage_id = $this->getMageIdManufacturer($manu_id)) {
                $pro_data['manufacturer'] = $manufacture_mage_id;
            }
        }
        $meta_titles = $this->getListFromListByField($varchar, 'attribute_id', $attribute['meta_title']);
        $pro_data['meta_title'] = $this->getRowValueFromListByField($meta_titles, 'store_id', '0', 'value');
        $meta_keywords = $this->getListFromListByField($text, 'attribute_id', $attribute['meta_keyword']);
        $pro_data['meta_keyword'] = $this->getRowValueFromListByField($meta_keywords, 'store_id', '0', 'value');
        $meta_descriptions = $this->getListFromListByField($varchar, 'attribute_id', $attribute['meta_description']);
        $pro_data['meta_description'] = $this->getRowValueFromListByField($meta_descriptions, 'store_id', '0', 'value');
        $image_path = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['image'], 'value');
        $image_label = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['image_label'], 'value');
        if ($image_path_new = $this->downloadImage($this->getUrlSuffix($this->_notice['config']['image_product']), $image_path, 'catalog/product', false, true)) {
            $pro_data['image_import_path'] = array('path' => $image_path_new, 'label' => $image_label);
        }
        $gallery = $this->getListFromListByField($productsExt['object']['catalog_product_entity_media_gallery'], 'entity_id', $product['entity_id']);
        if ($gallery) {
            foreach ($gallery as $img) {
                if ($gallery_path = $this->downloadImage($this->getUrlSuffix($this->_notice['config']['image_product']), $img['value'], 'catalog/product', false, true)) {
                    $gal_label = $this->getRowValueFromListByField($productsExt['object']['catalog_product_entity_media_gallery_value'], 'value_id', $img['value_id'], 'label');
                    $pro_data['image_gallery'][] = array('path' => $gallery_path, 'label' => $gal_label);
                }
            }
        }
        $tier_prices = $this->getListFromListByField($productsExt['object']['catalog_product_entity_tier_price'], 'entity_id', $product['entity_id']);
        if ($tier_prices) {
            foreach ($tier_prices as $tier) {
                if ($tier['all_groups'] == '1') {
                    $cus_group = \Magento\Customer\Model\Group::CUST_GROUP_ALL;
                } elseif ($tier['all_groups'] == '0' && isset($this->_notice['config']['customer_group'][$tier['customer_group_id']])) {
                    $cus_group = $this->_notice['config']['customer_group'][$tier['customer_group_id']];
                } else {
                    $cus_group = \Magento\Customer\Model\Group::CUST_GROUP_ALL;
                }
                $value = array(
                    'website_id' => 0,
                    'cust_group' => $cus_group,
                    'price_qty' => $tier['qty'],
                    'price' => $tier['value']
                );
                $tier_price_group[] = $value;
            }
            $pro_data['tier_price'] = $tier_price_group;
        }
        $pro_data['status'] = ($this->getRowValueFromListByField($int, 'attribute_id', $attribute['status'], 'value') == '1') ? 1 : 2;
        $pro_data['created_at'] = $product['created_at'];
        $pro_data['updated_at'] = $product['updated_at'];
        $pro_data['visibility'] = intval($this->getRowValueFromListByField($int, 'attribute_id', $attribute['visibility'], 'value'));
        $tax_class_id = $this->getRowValueFromListByField($int, 'attribute_id', $attribute['tax_class_id'], 'value');
        if ($tax_class_id && $tax_pro_id = $this->getMageIdTaxProduct($tax_class_id)) {
            $pro_data['tax_class_id'] = $tax_pro_id;
        } else {
            $pro_data['tax_class_id'] = 0;
        }
        $stock = $this->getRowFromListByField($productsExt['object']['cataloginventory_stock_item'], 'product_id', $product['entity_id']);
        $stock_qty = floatval($stock['qty']);
        $pro_data['stock_data'] = array(
            'is_in_stock' => $stock['is_in_stock'],
            'manage_stock' => $stock['manage_stock'],
            'use_config_manage_stock' => $stock['use_config_manage_stock'],
            'qty' => $stock_qty
        );
        $pro_data['news_from_date'] = $this->getRowValueFromListByField($datetime, 'attribute_id', $attribute['news_from_date'], 'value');
        $pro_data['news_to_date'] = $this->getRowValueFromListByField($datetime, 'attribute_id', $attribute['news_to_date'], 'value');
        //$url_store = $this->getListFromListByField($varchar, 'attribute_id', $attribute['url_key']);
        //$pro_data['url_key'] = $this->getRowValueFromListByField($url_store, 'store_id', '0', 'value');
        $url_store = $this->getListFromListByField($varchar, 'attribute_id', $attribute['url_key']);
        $url_def = $this->getRowValueFromListByField($url_store, 'store_id', '0', 'value');
        $pro_data['url_key'] = $this->generateUrlKeyFromUrlKey($url_def);

        //Attribute remain
        $attribute_remain = array_diff_key($attribute, $pro_data);
        //$attribute_set_id = $this->_notice['config']['attribute_set_id'];
        $entity_type_id = $this->_objectManager->create('Magento\Eav\Model\Entity')->setType(\Magento\Catalog\Model\Product::ENTITY)->getTypeId();
        foreach ($attribute_remain as $key => $value) {
            if ($attribute_type[$key] == 'static' || !$attribute_defined[$key]) {
                continue;
            }
            if ($used_attribute) {
                if (in_array($value, $used_attribute)) {
                    continue;
                }
            }
            if ($option_value = $this->getListFromListByField(${$attribute_type[$key]}, 'attribute_id', $value)) {
                $attributeSets = $this->getListFromListByField($productsExt['object']['eav_entity_attribute'], 'attribute_id', $value);
                $attribute_set_src_ids = $this->duplicateFieldValueFromList($attributeSets, 'attribute_set_id');
                $attribute_set_id_desc = array($pro_data['attribute_set_id']);
                foreach ($attribute_set_src_ids as $attr_set) {
                    if (isset($this->_notice['config']['attributes'][$attr_set])) {
                        $attribute_set_id_desc[] = $this->_notice['config']['attributes'][$attr_set];
                    }
                }
                $attribute_set_id = array_unique($attribute_set_id_desc);
                $attribute_add = $this->getRowFromListByField($productsExt['object']['eav_attribute'], 'attribute_id', $value);
                $info = $this->getRowFromListByField($productsExt['object']['catalog_eav_attribute'], 'attribute_id', $value);
                $option_mage = array();
                if ($attribute_input[$key] == 'select') {
                    $option_store = $this->getListFromListByField($productsExt['object']['eav_attribute_option_value'], 'option_id', $option_value[0]['value']);
                    if ($option_store) {
                        $option = array();
                        foreach ($option_store as $opt) {
                            if ($opt['store_id'] == '0') {
                                $option['0'] = $opt['value'];
                                continue;
                            }
                            if (isset($this->_notice['config']['languages'][$opt['store_id']])) {
                                $option[$this->_notice['config']['languages'][$opt['store_id']]] = $opt['value'];
                            }
                        }
                        if ($option) {
                            if (!isset($option['0'])) {
                                $option['0'] = reset($option);
                            }
                            $option_mage['option_0'] = $option;
                        }
                    }
                } elseif ($attribute_input[$key] == 'multiselect') {
                    if (!$option_value[0]['value']) continue;
                    $multi_opt = explode(',', $option_value[0]['value']);
                    foreach ($multi_opt as $k => $v) {
                        $option = array();
                        $option_multi_store = $this->getListFromListByField($productsExt['object']['eav_attribute_option_value'], 'option_id', $v);
                        foreach ($option_multi_store as $single) {
                            if ($single['store_id'] == '0') {
                                $option['0'] = $single['value'];
                                continue;
                            }
                            if (isset($this->_notice['config']['languages'][$single['store_id']])) {
                                $option[$this->_notice['config']['languages'][$single['store_id']]] = $single['value'];
                            }
                        }
                        if ($option) {
                            if (!isset($option['0'])) {
                                $option['0'] = reset($option);
                            }
                            $option_mage['option_'.$k] = $option;
                        }
                    }
                }
                
                $attr_import = $this->_makeAttributeImport($attribute_add, $option_mage, $attribute_set_id, $info, $entity_type_id);
                $attr_after = $this->_process->attribute($attr_import['config'], $attr_import['edit']);
                if (!$attr_after) { continue;
//                    return array(
//                        'result' => "warning",
//                        'msg' => $this->consoleWarning("Product Id = {$product['entity_id']} import failed. Error: Product attribute {$attribute_add['attribute_code']} could not create!")
//                    );
                }
                if ($attribute_input[$key] == 'select') {
                    $pro_data_add[$attr_after['attribute_id']]['value'] = isset($attr_after['option_ids']['option_0']) ? $attr_after['option_ids']['option_0'] : '';
                } elseif ($attribute_input[$key] == 'multiselect') {
                    $all_val = array();
                    foreach ($option_mage as $a => $b) {
                        $all_val[] = isset($attr_after['option_ids'][$a]) ? $attr_after['option_ids'][$a] : '';
                    }
                    $pro_data_add[$attr_after['attribute_id']]['value'] = implode(',', $all_val);
                } else {
                    $pro_data_add[$attr_after['attribute_id']]['value'] = $option_value[0]['value'];
                }
                $pro_data_add[$attr_after['attribute_id']]['backend_type'] = $attr_after['backend_type'];
            }
        }
        //Tag
        /*$proTags = $this->getListFromListByField($productsExt['object']['tag_relation'], 'product_id', $product['entity_id']);
        if ($proTags) {
            foreach ($proTags as $tag) {
                $pro_tag = $this->getRowFromListByField($productsExt['object']['tag'], 'tag_id', $tag['tag_id']);
                if ($pro_tag) {
                    $pro_data['tags'][] = $pro_tag['name'];
                }
            }
        }*/

        //Multi
        $multi_store = array();
        foreach ($this->_notice['config']['languages'] as $lang_id => $store_id) {
            $store_data = array();
            $store_data['name'] = $this->getRowValueFromListByField($names, 'store_id', $lang_id, 'value');
            $store_data['description'] = $this->getRowValueFromListByField($descriptions, 'store_id', $lang_id, 'value');
            $store_data['short_description'] = $this->getRowValueFromListByField($short_descriptions, 'store_id', $lang_id, 'value');
            $store_data['meta_title'] = $this->getRowValueFromListByField($meta_titles, 'store_id', $lang_id, 'value');
            $store_data['meta_keyword'] = $this->getRowValueFromListByField($meta_keywords, 'store_id', $lang_id, 'value');
            $store_data['meta_description'] = $this->getRowValueFromListByField($meta_descriptions, 'store_id', $lang_id, 'value');
            $store_data['url_key'] = $this->getRowValueFromListByField($url_store, 'store_id', $lang_id, 'value');
            $store_data['store_id'] = $store_id;
            $multi_store[] = $store_data;
        }
        $pro_data['multi_store'] = $multi_store;
        if ($this->_seo) {
            $seo = $this->_seo->convertProductSeo($this, $product, $productsExt);
            if ($seo) {
                $pro_data['seo_url'] = $seo;
            }
        }
        $custom = $this->_custom->convertProductCustom($this, $product, $productsExt);
        if ($custom) {
            $pro_data = array_merge($pro_data, $custom);
        }
        if ($pro_data_add) {
            $pro_data['add_data'] = $pro_data_add;
        }
        return $pro_data;
    }

    protected function _taxCustomerGroupSuccess($id_import, $mage_id, $value = false) {
        return $this->_insertLeCaMgImport(self::TYPE_TAX_CUSTOMER_GROUP, $id_import, $mage_id, 1, $value);
    }

    protected function _getMageIdTaxCustomerGroup($id_import) {
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_TAX_CUSTOMER_GROUP,
            'id_import' => $id_import
        ));
        if (!$result) {
            return false;
        }
        return $result['mage_id'];
    }
    
    protected function _getDefaultLanguage($stores) {
        if (isset($stores['0']['sort_order'])) {
            $sort_order = $stores['0']['sort_order'];
        } else {
            return 1;
        }
        foreach ($stores as $store) {
            if ($store['sort_order'] < $sort_order) {
                $sort_order = $store['sort_order'];
            }
        }
        $default_lang = 1;
        foreach ($stores as $store) {
            if ($store['sort_order'] == $sort_order) {
                $default_lang = $store['store_id'];
                break;
            }
        }
        return $default_lang;
    }
    
    protected function _getCategoryParentId($path) {
        $array = explode("/", $path);
	$array = array_unique($array);
        $array = array_values(array_reverse($array));
        return $array[1];
    }
    
    protected function _getCategoryLevel($path) {
        $array = explode("/", $path);
	$array = array_unique($array);
        return count($array) - 1;
    }

    /**
     * TODO: CRON
     */

    public function getAllTaxes()
    {
        if(!$this->_notice['config']['import']['taxes']){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        $query = "SELECT * FROM _DBPRF_tax_calculation_rule ORDER BY tax_calculation_rule_id ASC";
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query,
        ));
        if(!$data || $data['result'] != 'success'){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        return $data;
    }

    public function getAllManufacturers()
    {
        if(!$this->_notice['config']['import']['manufacturers']){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        $query = "SELECT eao.* FROM _DBPRF_eav_attribute as ea
                                                LEFT JOIN _DBPRF_eav_attribute_option as eao ON ea.attribute_id = eao.attribute_id
                                                WHERE attribute_code = 'manufacturer' ORDER BY eao.option_id ASC";
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query,
        ));
        if(!$data || $data['result'] != 'success'){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        return $data;
    }

    public function getAllCategories()
    {
        if(!$this->_notice['config']['import']['categories']){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        $query = "SELECT * FROM _DBPRF_catalog_category_entity WHERE level > 1 ORDER BY entity_id ASC";
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query,
        ));
        if(!$data || $data['result'] != 'success'){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        return $data;
    }

    public function getAllProducts()
    {
        if(!$this->_notice['config']['import']['products']){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        $query = "SELECT cpe.* FROM _DBPRF_catalog_product_entity as cpe LEFT JOIN _DBPRF_catalog_product_relation as cpr ON cpe.entity_id = cpr.child_id WHERE cpr.child_id IS NULL ORDER BY cpe.entity_id ASC";
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query,
        ));
        if(!$data || $data['result'] != 'success'){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        return $data;
    }

    public function getAllCustomers()
    {
        if(!$this->_notice['config']['import']['customers']){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        $query = "SELECT * FROM _DBPRF_customer_entity ORDER BY entity_id ASC";
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query,
        ));
        if(!$data || $data['result'] != 'success'){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        return $data;
    }

    public function getAllOrders()
    {
        if(!$this->_notice['config']['import']['orders']){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        $query = "SELECT * FROM _DBPRF_sales_flat_order ORDER BY entity_id ASC";
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query,
        ));
        if(!$data || $data['result'] != 'success'){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        return $data;
    }

    public function getAllReviews()
    {
        if(!$this->_notice['config']['import']['reviews']){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        $query = "SELECT * FROM _DBPRF_review ORDER BY review_id ASC";
        $data = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query,
        ));
        if(!$data || $data['result'] != 'success'){
            return array(
                'result' => 'success',
                'object' => array()
            );
        }
        return $data;
    }

}
