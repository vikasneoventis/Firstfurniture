<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

namespace LitExtension\CartMigration\Model;

//use LitExtension\CartMigration\Model\Custom;
use Magento\Framework\UrlInterface;

class Cart// extends \Magento\Framework\Model\AbstractModel
{

    const DEMO_MODE = false;
    const TABLE_IMPORTS     = 'lecm_import';
    const TABLE_USER        = 'lecm_user';
    const TABLE_RECENT      = 'lecm_recent';
    const TABLE_UPDATE      = 'lecm_update';
    const TYPE_TAX          = 'tax';
    const TYPE_TAX_CUSTOMER = 'tax_customer';
    const TYPE_TAX_PRODUCT  = 'tax_product';
    const TYPE_TAX_RATE     = 'tax_rate';
    const TYPE_MANUFACTURER = 'manufacturer';
    const TYPE_MAN_ATTR     = 'man_attr';
    const TYPE_CATEGORY     = 'category';
    const TYPE_PRODUCT      = 'product';
    const TYPE_ATTR         = 'attribute';
    const TYPE_ATTR_OPTION  = 'attribute_option';
    const TYPE_CUSTOMER     = 'customer';
    const TYPE_ORDER        = 'order';
    const TYPE_REVIEW       = 'review';
    const TYPE_PAGE         = 'page';
    const TYPE_BLOCK        = 'block';
    const TYPE_TRANSACTION  = 'transaction';
    const TYPE_RULE         = 'rule';
    const TYPE_CARTRULE      = 'cartrule';
    const MANUFACTURER_CODE = 'manufacturer';

    protected $_resource = null;
    protected $_objectManager = null;
    protected $_scopeConfig = null;
    protected $_db = null;
    protected $_logger = null;
    protected $_notice = null;
    protected $_cart_url = null;
    protected $_cart_token = null;
    protected $_custom = null;
    protected $_process = null;
    protected $_seo = null;

    protected $_demo_limit = array(
        'taxes' => 10,
        'manufacturers' => 10,
        'categories' => 10,
        'products' => 10,
        'customers' => 10,
        'orders' => 10,
        'reviews' => 0
    );
    
    public function __construct(
        //\Magento\Framework\Model\Context $context,
        //\Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \LitExtension\Core\Logger\Logger $logger
    ) {
        $this->_resource = $resource;
        $this->_db = $this->_resource->getConnection();
        $this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
        $this->_logger = $logger;
        $this->_process = $this->_objectManager->create('LitExtension\CartMigration\Model\Process');
        /*parent::__construct(
            $context,
            $registry
        );*/
    }
    
    protected function _construct()
    {
        //$this->_init('LitExtension\CartMigration\Model\ResourceModel\Cart');
    }

    /**
     * TODO: Router
     */

    /**
     * Router to model process migration
     *
     * @param string $cart_type
     * @param string $cart_version
     * @return string
     */
    public function getCart($cart_type, $cart_version = '') {
        $cart = $this->_getCart($cart_type, $cart_version);
        return $this->_pathToName($cart);
    }
    
    protected function _pathToName($path, $charset = '_') {
        $name = explode($charset, $path);
        $upper = array_map('ucfirst', $name);
        $class = implode("\\", $upper);
        return $class;
    }
    
    protected function _getCart($cart_type, $cart_version = ''){
        if(!$cart_type){
            return 'cart';
        }
        if($cart_type == 'oscommerce'){
            return 'cart_oscommerce';
        }
        if($cart_type == 'zencart'){
            return 'cart_zencart';
        }
        if($cart_type == 'virtuemart'){
            if($this->_convertVersion($cart_version, 2) < 200){
                return 'cart_virtuemartv1';
            } else {
                return 'cart_virtuemartv2';
            }
        }
        if($cart_type == 'woocommerce'){
            if($this->_convertVersion($cart_version, 2) < 200){
                return 'cart_woocommercev1';
            } else {
                return 'cart_woocommercev2';
            }
        }
        if($cart_type == 'xtcommerce'){
            if($this->_convertVersion($cart_version, 2) < 400){
                return 'cart_xtcommercev3';
            } else {
                return 'cart_xtcommercev4';
            }
        }
        if($cart_type == 'opencart'){
            if($this->_convertVersion($cart_version, 2) > 149){
                return 'cart_opencartv15';
            } else {
                return 'cart_opencartv14';
            }
        }
        if($cart_type == 'xcart'){
            $xc_ver =  $this->_convertVersion($cart_version, 2);
            if($xc_ver < 440){
                return 'cart_xcartv43';
            }elseif($xc_ver < 450){
                return 'cart_xcartv44';
            }elseif($xc_ver < 500) {
                return 'cart_xcartv46';
            }else{
                return 'cart_xcartv5';
            }
        }
        if($cart_type == 'wpecommerce'){
            $wp_ver = $this->_convertVersion($cart_version, 2);
            if($wp_ver < 370){
                return 'cart_wpecommercev36';
            }elseif($wp_ver < 380){
                return 'cart_wpecommercev37';
            }else{
                return 'cart_wpecommercev38';
            }
        }
        if($cart_type == 'prestashop'){
            $pv = $this->_convertVersion($cart_version, 2);
            if($pv > 149){
                return 'cart_prestashopv16';
            } else if(139 < $pv && $pv < 150) {
                return 'cart_prestashopv14';
            } else {
                return 'cart_prestashopv13';
            }
        }
        if($cart_type == 'loaded'){
            if($this->_convertVersion($cart_version, 2) > 699){
                return 'cart_loadedcommercev7';
            } else {
		    if (strpos($cart_version, 'b2b')) {
                    return 'cart_loadedcommercev6b2b';
                }
                return 'cart_loadedcommercev6';
            }
        }
        if($cart_type == 'cscart'){
            if($this->_convertVersion($cart_version, 2) > 299){
                return 'cart_cscart4';
            } else {
                return 'cart_cscart2';
            }
        }
        if($cart_type == 'magento'){
            if($this->_convertVersionMagento($cart_version, 2) > 199){
                return 'cart_magento2';
            } elseif($this->_convertVersionMagento($cart_version, 2) > 149){
                return 'cart_magento19';
            } elseif($this->_convertVersionMagento($cart_version, 2) > 140) {
                return 'cart_magento14';
            } else {
                return 'cart_magento13';
            }
        }
        if($cart_type == 'interspire'){
           return 'cart_interspirev6';
        }
        if($cart_type == 'cubecart') {
            if($this->_convertVersion($cart_version, 2) > 499){
                return 'cart_cubecartv6';
            } elseif ($this->_convertVersion($cart_version, 2) > 399) {
                return 'cart_cubecartv4';
            } else {
                return 'cart_cubecartv3';
            }
        }
        if($cart_type == 'oxideshop'){
            if($this->_convertVersion($cart_version, 2) > 469){
                    return 'cart_oxideshop49';
            } else {
                    return 'cart_oxideshop44';
            }
        }

        if($cart_type == 'marketpress'){
            return 'cart_marketpressv2';
        }
        if($cart_type == 'ubercart'){
            if($this->_convertVersion($cart_version, 2) < 300){
                return 'cart_ubercartv2';
            } else {
                return 'cart_ubercartv3';
            }
        }
        if($cart_type == 'drupalcommerce'){
            return 'cart_drupalcart1x';
        }
        if($cart_type == 'pinnaclecart'){
            return 'cart_pinnaclecart';
        }
        return 'cart';
    }

    /**
     * Convert version from string to int
     *
     * @param string $v : String of version split by dot
     * @param int $num : number of result return
     * @return int
     */
    protected  function _convertVersion($v, $num) {
        $digits = @explode(".", $v);
        $version = 0;
        if (is_array($digits)) {
            foreach ($digits as $k => $v) {
                if($k <= $num){
                    $version += (substr($v, 0, 1) * pow(10, max(0, ($num - $k))));
                }
            }
        }
        return $version;
    }

    /**
     * TODO: Notice
     */

    /**
     * Set notice use for migration in model
     */
    public function setNotice($notice, $custom = true){
        $this->_notice = $notice;
        $this->_cart_url = $notice['config']['cart_url'];
        $this->_cart_token = $notice['config']['cart_token'];
        if($custom){
            $this->_custom = $this->_objectManager->create('LitExtension\CartMigration\Model\Custom');
        }
    }

    /**
     * Get notice of migration after config or process
     */
    public function getNotice(){
        return $this->_notice;
    }

    /**
     * Default construct of notice migration use for pass php notice warning
     */
    public function getDefaultNotice(){
        return array(
            'config' => array(
                'cart_type' => '',
                'cart_url' => '',
                'cart_token' => '',
                'cart_version' => '',
                'table_prefix' => '',
                'charset' => '',
                'image_category' => '',
                'image_product' => '',
                'image_manufacturer' => '',
                'cats' => array(),
                'category_data' => array(),
                'root_category_id' => '',
                'attributes' => array(),
                'attribute_data' => array(),
                'attribute_set_id' => '',
                'languages' => array(),
                'languages_data' => array(),
                'currencies' => array(),
                'currencies_data' => array(),
                'order_status' => array(),
                'order_status_data' => array(),
                'countries' => array(),
                'countries_data' => array(),
                'customer_group' => array(),
                'customer_group_data' => array(),
                'default_lang' => '',
                'default_currency' => '',
                'website_id' => '',
                'limit' => '',
                'config_support' => array(
                    'category_map' => true,
                    'attribute_map' => true,
                    'language_map' => true,
                    'order_status_map' => true,
                    'currency_map' => true,
                    'country_map' => false,
                    'customer_group_map' => true
                ),
                'import_support' => array(
                    'taxes' => true,
                    'manufacturers' => true,
                    'categories' => true,
                    'products' => true,
                    'customers' => true,
                    'orders' => true,
                    'reviews' => true,
                    'pages' => false,
                    'blocks' => false,
                    'widgets' => false,
                    'polls' => false,
                    'transactions' => false,
                    'newsletters' => false,
                    'users' => false,
                    'rules' => false,
                    'cartrules' => false
                ),
                'import' => array(
                    'taxes' => false,
                    'manufacturers' => false,
                    'categories' => false,
                    'products' => false,
                    'customers' => false,
                    'orders' => false,
                    'reviews' => false,
                    'pages' => false,
                    'blocks' => false,
                    'widgets' => false,
                    'polls' => false,
                    'transactions' => false,
                    'newsletters' => false,
                    'users' => false,
                    'rules' => false,
                    'cartrules' => false
                ),
                'add_option' => array(
                    'add_new' => false,
                    'clear_data' => false,
                    'img_des' => false,
                    'pre_cus' => false,
                    'pre_ord' => false,
                    'stock' => false,
                    'seo_url' => false,
                    'seo_plugin' => ''
                ),
            ),
            'clear_info' => array(
                'result' => 'process',
                'function' => '_clearProducts',
                'msg' => '',
                'limit' => 20
            ),
            'taxes' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'new' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false,
            ),
            'manufacturers' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'new' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'categories' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'new' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'products' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'new' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'customers' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'new' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'orders' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'new' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'reviews' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'new' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'pages' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'new' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'blocks' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'new' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'widgets' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'new' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'polls' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'new' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'transactions' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'new' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'newsletters' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'new' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'users' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'new' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'rules' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'new' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'cartrules' => array(
                'total' => 0,
                'imported' => 0,
                'error' => 0,
                'new' => 0,
                'id_src' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'setting' => $this->_scopeConfig->getValue('lecm/general'),
            'is_running' => false,
            'fn_resume' => 'clearStore',
            'msg_start' => '',
            'extend' => array(),
			'curl' => array(
                'useragent' => true
            )
        );
    }

    /**
     * Save notice to database with admin id
     *
     * @param int $user_id
     * @param array $notice
     * @return boolean
     */
    public function saveUserNotice($user_id, $notice){
        if(!$user_id){
            return false;
        }
        try{
            $user = $this->_objectManager->create('LitExtension\CartMigration\Model\User')->loadByUserId($user_id);
            if($user && $user->getId()){
                $user->setNotice($notice);
                $user->save();
            } else {
                $newUser = $this->_objectManager->create('LitExtension\CartMigration\Model\User');
                $newUser->setUserId($user_id)
                    ->setNotice($notice);
                $newUser->save();
            }
            return true;
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * Save notice to database with domain migration use for add option add new
     *
     * @param array $notice
     * @return boolean
     */
    public function saveRecentNotice($notice){
        try{
            $recent = $this->_objectManager->create('LitExtension\CartMigration\Model\Recent')->loadByDomain($this->_cart_url);
            if($recent && $recent->getId()){
                $recent->setNotice($notice);
                $recent->save();
            } else {
                $newRecent =  $this->_objectManager->create('LitExtension\CartMigration\Model\Recent');
                $newRecent->setDomain($this->_cart_url)
                    ->setNotice($notice);
                $newRecent->save();
            }
            return true;
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * Get notice of migration in database with admin id
     * @param int $user_id
     * @return array
     */
    public function getUserNotice($user_id){
        if(!$user_id){
            return false;
        }
        $notice = false;
        try{
            $user = $this->_objectManager->create('LitExtension\CartMigration\Model\User')->loadByUserId($user_id);
            if($user && $user->getId()){
                $notice = $user->getNotice();
            }
        }catch (\Exception $e){}
        return $notice;
    }

    /**
     * Get notice of migration in database of last migration as same domain
     *
     * @return array
     */
    public function getRecentNotice(){
        if(!$this->_cart_url){
            return false;
        }
        $notice = false;
        try{
            $recent = $this->_objectManager->create('LitExtension\CartMigration\Model\Recent')->loadByDomain($this->_cart_url);
            if($recent && $recent->getId()){
                $notice = $recent->getNotice();
            }
        }catch (\Exception $e){}
        return $notice;
    }

    /**
     * Delete notice of migration with admin id
     *
     * @param int $user_id
     * @return boolean
     */
    public function deleteUserNotice($user_id){
        if(!$user_id){
            return true;
        }
        $result = true;
        try{
            $user = $this->_objectManager->create('LitExtension\CartMigration\Model\User')->loadByUserId($user_id);
            if($user && $user->getId()){
                $user->delete();
            }
        }catch (\Exception $e){
            $result = false;
        }
        return $result;
    }

    /**
     * TODO: Import
     */

    public function checkRecent()
    {
        return $this;
    }

    /**
     * Check connector and config some value of connect response
     *
     * @return array
     */
    public function checkConnector(){
        $response = $this->defaultResponse();
        if(strpos($this->_cart_url, 'magento_connector/connector.php')){
            $this->_cart_url = str_replace('magento_connector/connector.php', '', $this->_cart_url);
            $this->_notice['config']['cart_url'] = $this->_cart_url;
        }
        if (!self::DEMO_MODE) {
            $license = trim($this->_scopeConfig->getValue('lecm/general/license'));
            if (!$license) {
                return array(
                    'result' => 'error',
                    'msg' => 'Please enter License Key (in Configuration)'
                );
            }
            $check_license = $this->_getDataImport(
                    chr(104) . chr(116) . chr(116) . chr(112) . chr(58) . chr(47) . chr(47) . chr(108) . chr(105) . chr(116) . chr(101) . chr(120) . chr(116) . chr(101) . chr(110) . chr(115) . chr(105) . chr(111) . chr(110) . chr(46) . chr(99) . chr(111) . chr(109) . chr(47) . chr(108) . chr(105) . chr(99) . chr(101) . chr(110) . chr(115) . chr(101) . chr(46) . chr(112) . chr(104) . chr(112), array(
                'user' => chr(108) . chr(105) . chr(116) . chr(101) . chr(120),
                'pass' => chr(97) . chr(65) . chr(49) . chr(50) . chr(51) . chr(52) . chr(53) . chr(54),
                'action' => chr(99) . chr(104) . chr(101) . chr(99) . chr(107),
                'license' => $license,
                'cart_type' => $this->_notice['config']['cart_type'],
                'url' => $this->_cart_url,
                'target_type' => base64_encode('magento2'),
                    ), false
            );
            if (!$check_license) {
                return array(
                    'result' => 'error',
                    'msg' => 'Could not get your license info, please check network connection.'
                );
            }
            if ($check_license['result'] != 'success') {
                return $check_license;
            }
        }
        $check = $this->_getDataImport($this->_getUrlConnector('check'));
		if(!$check){
			$this->_notice['curl']['useragent'] = false;
			$check = $this->_getDataImport($this->_getUrlConnector('check'));
		}		
        if(!$check){
            $response['result'] = 'warning';
            $response['elm'] = '#error-url';
            $response['msg'] = "Cannot reach connector! It should be uploaded at: " . $this->_cart_url . "/magento_connector/connector.php";
            return $response;
        }
        if($check['result'] != 'success'){
            $response['result'] = 'warning';
            $response['elm'] = '#error-token';
            return $response;
        }
        $obj = $check['object'];
        if(!$this->_checkCartSync($obj['cms'], $this->_notice['config']['cart_type'])){
            $response['result'] = 'warning';
            $response['elm'] = '#error-cart';
            return $response;
        }
        $connect = $obj['connect'];
        if(!$connect || $connect['result'] != 'success'){
            $response['result'] = 'warning';
            $response['elm'] = '#error-url';
            return $response;
        }
        $this->_notice['config']['cart_version'] = $obj['version'];
        $this->_notice['config']['table_prefix'] = $obj['table_prefix'];
        $this->_notice['config']['charset'] = $obj['charset'];
        $this->_notice['config']['image_product'] = $obj['image_product'];
        $this->_notice['config']['image_category'] = $obj['image_category'];
        $this->_notice['config']['image_manufacturer'] = $obj['image_manufacturer'];
        $this->_notice['extend']['cookie_key'] = $obj['cookie_key'];
        $response['result'] = 'success';
        return $response;
    }

    /**
     * Save config of use in config step to notice
     */
    public function displayConfirm($params){
        $configs = array('cats', 'attributes', 'languages', 'currencies', 'order_status', 'countries', 'customer_group');
        foreach($configs as $config){
            $this->_notice['config'][$config] = isset($params[$config])? $params[$config] : array();
        }
        $imports = array('taxes', 'manufacturers', 'categories', 'products', 'customers', 'orders', 'reviews', 'pages', 'blocks', 'widgets', 'polls', 'transactions', 'newsletters', 'users', 'rules', 'cartrules');
        foreach ($imports as $import) {
            if (isset($params[$import]) && $params[$import]) {
                $this->_notice['config']['import'][$import] = true;
            } else {
                $this->_notice['config']['import'][$import] = false;
            }
        }
        $addOption = array('add_new', 'clear_data', 'img_des', 'pre_cus', 'pre_ord', 'stock', 'seo_url');
        foreach ($addOption as $add_opt) {
            if (isset($params[$add_opt]) && $params[$add_opt]) {
                $this->_notice['config']['add_option'][$add_opt] = true;
            } else {
                $this->_notice['config']['add_option'][$add_opt] = false;
            }
        }
        if(isset($params['seo_plugin']) && $params['seo_plugin']){
            $this->_notice['config']['add_option']['seo_plugin'] = $params['seo_plugin'];
        }
        $this->_notice['config']['languages'] = $this->filterArrayValueFalse($this->_notice['config']['languages']);
        $categories = array_values($this->_notice['config']['cats']);
        $this->_notice['config']['root_category_id'] = $categories[0];
        $attributes = array_values($this->_notice['config']['attributes']);
        $this->_notice['config']['attribute_set_id'] = $attributes[0];
        if(isset($this->_notice['config']['languages']) && $this->_notice['config']['languages']){
            $store_default = isset($this->_notice['config']['languages'][$this->_notice['config']['default_lang']]) ? $this->_notice['config']['languages'][$this->_notice['config']['default_lang']] : false;
            if($store_default){
                $this->_notice['config']['website_id'] = $this->getWebsiteIdByStoreId($store_default);
            } else {
                $this->_notice['config']['website_id'] = 0;
            }
        } else {
            $this->_notice['config']['website_id'] = 0;
        }
        return ;
    }

    /**
     * Clear data of store
     */
    public function clearStore(){
        if(!$this->_notice['config']['add_option']['clear_data']){
            if(!$this->_notice['config']['add_option']['add_new']){
                $del = $this->_deleteLeCaMgImport($this->_notice['config']['cart_url']);
                if(!$del){
                    return $this->errorDatabase(true);
                }
            }
            return array(
                'result' => 'no-clear'
            );
        }
        $clear = $this->_process->clearStore($this);
        $this->_notice['clear_info']['result'] = $clear['result'];
        $this->_notice['clear_info']['function'] = isset($clear['function']) ? $clear['function'] : '';
        if($clear['result'] == 'success'){
            $entity = array();
            foreach($this->_notice['config']['import'] as $type => $value){
                if($value){
                    $entity[] = ucfirst(($type));
                }
            }
            $msg = "Current " . implode(', ', $entity) . " cleared!";
            $clear['msg'] = $this->consoleSuccess($msg);
            $clear['msg'] .= $this->getMsgStartImport('taxes');
            if(!$this->_notice['config']['add_option']['add_new']){
                $del = $this->_deleteLeCaMgImport($this->_notice['config']['cart_url']);
                if(!$del){
                    return $this->errorDatabase(true);
                }
            }
        }
        return $clear;
    }

    /**
     * Config currency
     */
    public function configCurrency(){
        if (!$this->_notice['config']['import']['products'] && !$this->_notice['config']['import']['orders']) {
            return false;
        }
        return true;
    }

    /**
     * Process before import taxes
     */
    public function prepareImportTaxes(){
        $this->_custom->prepareImportTaxesCustom($this);
    }

    /**
     * Get data of table convert to tax rule
     *
     * @return array : Response of connector
     */
    public function getTaxesMain(){
        $query = $this->_getTaxesMainQuery();
        $taxes = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query
        ));
        if(!$taxes || $taxes['result'] != 'success'){
            return $this->errorConnector(true);
        }
        return $taxes;
    }

    /**
     * Get data relation use for import tax rule
     *
     * @param array $taxes : Data of function getTaxesMain
     * @return array : Response of connector
     */
    public function getTaxesExt($taxes){
        $taxesExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getTaxesExtQuery($taxes);
        $cus_ext_query = $this->_custom->getTaxesExtQueryCustom($this, $taxes);
        if($cus_ext_query){
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if($ext_query){
            $taxesExt = $this->_getDataImport($this->_getUrlConnector('query'), array(
                'serialize' => true,
                "query" => serialize($ext_query)
            ));
            if(!$taxesExt || $taxesExt['result'] != 'success'){
                return $this->errorConnector(true);
            }
            $ext_rel_query = $this->_getTaxesExtRelQuery($taxes, $taxesExt);
            $cus_ext_rel_query = $this->_custom->getTaxesExtRelQueryCustom($this, $taxes, $taxesExt);
            if($cus_ext_rel_query){
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if($ext_rel_query){
                $taxesExtRel = $this->_getDataImport($this->_getUrlConnector('query'), array(
                    'serialize' => true,
                    "query" => serialize($ext_rel_query)
                ));
                if(!$taxesExtRel || $taxesExtRel['result'] != 'success'){
                    return $this->errorConnector(true);
                }
                $taxesExt = $this->_syncResultQuery($taxesExt, $taxesExtRel);
            }
        }
        return $taxesExt;
    }

    /**
     * Check tax has imported
     *
     * @param array $tax : One row of function getTaxesMain
     * @param array $taxesExt : Data of function getTaxesExt
     * @return boolean
     */
    public function checkTaxImport($tax, $taxesExt){
        $id_src = $this->getTaxId($tax, $taxesExt);
        return $this->getMageIdTax($id_src);
    }

    /**
     * Import tax with data convert of function convertTax
     *
     * @param array $data : Data of function convertTax
     * @param array $tax : One row of function getTaxesMain
     * @param array $taxesExt : Data of function getTaxesExt
     * @return array
     */
    public function importTax($data, $tax, $taxesExt){
        if(Custom::TAX_IMPORT){
            return $this->_custom->importTaxCustom($this, $data, $tax, $taxesExt);
        }
        $id_src = $this->getTaxId($tax, $taxesExt);
        $taxIpt = $this->_process->taxRule($data);
        if($taxIpt['result'] == 'success'){
            $id_desc = $taxIpt['mage_id'];
            $this->taxSuccess($id_src, $id_desc);
        } else {
            $taxIpt['result'] = 'warning';
            $msg = "Tax Id = {$id_src} import failed. Error: " . $taxIpt['msg'];
            $taxIpt['msg'] = $this->consoleWarning($msg);
        }
        return $taxIpt;
    }

    /**
     * Process after import success one row of tax main
     *
     * @param int $tax_mage_id : Id of tax import to magento
     * @param array $data : Data of function convertTax
     * @param array $tax : One row of function getTaxesMain
     * @param array $taxesExt : Data of function getTaxesExt
     * @return boolean
     */
    public function afterSaveTax($tax_mage_id, $data, $tax, $taxesExt){
        $this->_custom->afterSaveTaxCustom($this, $tax_mage_id, $data, $tax, $taxesExt);
        return Custom::TAX_AFTER_SAVE;
    }

    /**
     * Add more addition for tax
     */
    public function additionTax($data, $tax, $taxesExt){
        if(Custom::TAX_ADDITION){
            return $this->_custom->additionTaxCustom($this, $data, $tax, $taxesExt);
        }
        return array(
            'result' => "success"
        );
    }

    /**
     * Process before import manufacturers
     */
    public function prepareImportManufacturers(){
        $this->_custom->prepareImportManufacturersCustom($this);
    }

    /**
     * Get data for convert to manufacturer option
     *
     * @return array : Response of connector
     */
    public function getManufacturersMain(){
        $query = $this->_getManufacturersMainQuery();
        $manufacturers = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query
        ));
        if(!$manufacturers || $manufacturers['result'] != 'success'){
            return $this->errorConnector(true);
        }
        return $manufacturers;
    }

    /**
     * Get data relation use for import manufacturer
     *
     * @param array $manufacturers : Data of function getManufacturersMain
     * @return array : Response of connector
     */
    public function getManufacturersExt($manufacturers){
        $manufacturersExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getManufacturersExtQuery($manufacturers);
        $cus_ext_query = $this->_custom->getManufacturersExtQueryCustom($this, $manufacturers);
        if($cus_ext_query){
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if($ext_query){
            $manufacturersExt = $this->_getDataImport($this->_getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if(!$manufacturersExt || $manufacturersExt['result'] != 'success'){
                return $this->errorConnector(true);
            }
            $ext_rel_query = $this->_getManufacturersExtRelQuery($manufacturers, $manufacturersExt);
            $cus_ext_rel_query = $this->_custom->getManufacturersExtRelQueryCustom($this, $manufacturers, $manufacturersExt);
            if($cus_ext_rel_query){
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if($ext_rel_query){
                $manufacturersExtRel = $this->_getDataImport($this->_getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if(!$manufacturersExtRel || $manufacturersExtRel['result'] != 'success'){
                    return $this->errorConnector(true);
                }
                $manufacturersExt = $this->_syncResultQuery($manufacturersExt, $manufacturersExtRel);
            }
        }
        return $manufacturersExt;
    }

    /**
     * Check manufacturer has been imported
     *
     * @param array $manufacturer : One row of object in function getManufacturersMain
     * @param array $manufacturersExt : Data of function getManufacturersExt
     * @return boolean
     */
    public function checkManufacturerImport($manufacturer, $manufacturersExt){
        $id_src = $this->getManufacturerId($manufacturer, $manufacturersExt);
        return $this->getMageIdManufacturer($id_src);
    }

    /**
     * Import manufacturer with data of function convertManufacturer
     *
     * @param array $data : Data of function convertManufacturer
     * @param array $manufacturer : One row of object in function getManufacturersMain
     * @param array $manufacturersExt : Data of function getManufacturersExt
     * @return array
     */
    public function importManufacturer($data, $manufacturer, $manufacturersExt){
        if(Custom::MANUFACTURER_IMPORT){
            return $this->_custom->importManufacturerCustom($this, $data, $manufacturer, $manufacturersExt);
        }
        $id_src = $this->getManufacturerId($manufacturer, $manufacturersExt);
        $manufacturerIpt = $this->_process->manufacturer($data);
        if($manufacturerIpt['result'] == 'success'){
            $id_desc = $manufacturerIpt['mage_id'];
            $this->manufacturerSuccess($id_src, $id_desc);
        } else {
            $manufacturerIpt['result'] = 'warning';
            $msg = "Manufacturer Id = {$id_src} import failed. Error: " . $manufacturerIpt['msg'];
            $manufacturerIpt['msg'] = $this->consoleWarning($msg);
        }
        return $manufacturerIpt;
    }

    /**
     * Process after one manufacturer import successful
     *
     * @param int $manufacturer_mage_id : Id of manufacturer import success to magento
     * @param array $data : Data of function convertManufacturer
     * @param array $manufacturer : One row of object in function getManufacturersMain
     * @param array $manufacturersExt : Data of function getManufacturersExt
     * @return boolean
     */
    public function afterSaveManufacturer($manufacturer_mage_id, $data, $manufacturer, $manufacturersExt){
        $this->_custom->afterSaveManufacturerCustom($this, $manufacturer_mage_id, $data, $manufacturer, $manufacturersExt);
        return Custom::MANUFACTURER_AFTER_SAVE;
    }

    /**
     * Add more addition for manufacturer
     */
    public function additionManufacturer($data, $manufacturer, $manufacturersExt){
        if(Custom::MANUFACTURER_ADDITION){
            return $this->_custom->additionManufacturerCustom($this, $data, $manufacturer, $manufacturersExt);
        }
        return array(
            'result' => "success"
        );
    }

    /**
     * Process before import categories
     */
    public function prepareImportCategories(){
        $this->_custom->prepareImportCategoriesCustom($this);
        $this->_process->stopIndexes();
    }

    /**
     * Get data of main table use import category
     *
     * @return array : Response of connector
     */
    public function getCategoriesMain(){
        $query = $this->_getCategoriesMainQuery();
        $categories = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query
        ));
        if(!$categories || $categories['result'] != 'success'){
            return $this->errorConnector(true);
        }
        if($this->_notice['config']['add_option']['seo_url'] && $this->_notice['config']['add_option']['seo_plugin']){
            $seo_model = 'LitExtension\CartMigration\Model\\' . $this->_pathToName($this->_notice['config']['add_option']['seo_plugin']);
            $this->_seo = $this->_objectManager->create($seo_model);
        }
        return $categories;
    }

    /**
     * Get data relation use for import categories
     *
     * @param array $categories : Data of function getCategoriesMain
     * @return array : Response of connector
     */
    public function getCategoriesExt($categories){
        $categoriesExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getCategoriesExtQuery($categories);
        if($this->_seo){
            $seo_ext_query = $this->_seo->getCategoriesExtQuery($this, $categories);
            if($seo_ext_query){
                $ext_query = array_merge($ext_query, $seo_ext_query);
            }
        }
        $cus_ext_query = $this->_custom->getCategoriesExtQueryCustom($this, $categories);
        if($cus_ext_query){
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if($ext_query){
            $categoriesExt = $this->_getDataImport($this->_getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if(!$categoriesExt || $categoriesExt['result'] != 'success'){
                return $this->errorConnector(true);
            }
            $ext_rel_query = $this->_getCategoriesExtRelQuery($categories, $categoriesExt);
            if($this->_seo){
                $seo_ext_rel_query = $this->_seo->getCategoriesExtRelQuery($this, $categories, $categoriesExt);
                if($seo_ext_rel_query){
                    $ext_rel_query = array_merge($ext_rel_query, $seo_ext_rel_query);
                }
            }
            $cus_ext_rel_query = $this->_custom->getCategoriesExtRelQueryCustom($this, $categories, $categoriesExt);
            if($cus_ext_rel_query){
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if($ext_rel_query){
                $categoriesExtRel = $this->_getDataImport($this->_getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if(!$categoriesExtRel || $categoriesExtRel['result'] != 'success'){
                    return $this->errorConnector(true);
                }
                $categoriesExt = $this->_syncResultQuery($categoriesExt, $categoriesExtRel);
            }
        }
        return $categoriesExt;
    }

    /**
     * Check category has been imported
     *
     * @param array $category : One row of object in function getCategoriesMain
     * @param array $categoriesExt : Data of function getCategoriesExt
     * @return boolean
     */
    public function checkCategoryImport($category, $categoriesExt){
        $id_src = $this->getCategoryId($category, $categoriesExt);
        return $this->getMageIdCategory($id_src);
    }

    /**
     * Import category with data convert in function convertCategory
     *
     * @param array $data : Data of function convertCategory
     * @param array $category : One row of object in function getCategoriesMain
     * @param array $categoriesExt : Data of function getCategoriesExt
     * @return array
     */
    public function importCategory($data, $category, $categoriesExt){
        if(Custom::CATEGORY_IMPORT){
            return $this->_custom->importCategoryCustom($this, $data, $category, $categoriesExt);
        }
        $id_src = $this->getCategoryId($category, $categoriesExt);
        if(!isset($data['url_key'])) {
            $data['url_key'] = $this->generateCategoryUrlKey($data['name']);
        }
        if (!$data['url_key']) {
            return array(
                'result' => 'warning',
                'msg' => $this->consoleWarning("Category Id = {$id_src} import failed. Error: URL key is empty!")
            );
        }
        $categoryIpt = $this->_process->category($data);
        if($categoryIpt['result'] == 'success'){
            $id_desc = $categoryIpt['mage_id'];
            $this->categorySuccess($id_src, $id_desc);
        } else {
            $categoryIpt['result'] = 'warning';
            $msg = "Category Id = {$id_src} import failed. Error: " . $categoryIpt['msg'];
            $categoryIpt['msg'] = $this->consoleWarning($msg);
        }
        return $categoryIpt;
    }

    /**
     * Process after one category import successful
     *
     * @param int $category_mage_id : Id of category import successful to magento
     * @param array $data : Data of function convertCategory
     * @param array $category : One row of object in function getCategoriesMain
     * @param array $categoriesExt : Data of function getCategoriesExt
     * @return boolean
     */
    public function afterSaveCategory($category_mage_id, $data, $category, $categoriesExt){
        $this->_custom->afterSaveCategoryCustom($this, $category_mage_id, $data, $category, $categoriesExt);
        return Custom::CATEGORY_AFTER_SAVE;
    }

    /**
     * Add more addition for category
     */
    public function additionCategory($data, $category, $categoriesExt){
        if(Custom::CATEGORY_ADDITION){
            return $this->_custom->additionCategoryCustom($this, $data, $category, $categoriesExt);
        }
        return array(
            'result' => "success"
        );
    }

    /**
     * Process before import products
     */
    public function prepareImportProducts(){
        $this->_custom->prepareImportProductsCustom($this);
        $this->_process->stopIndexes();
    }

    /**
     * Get data of main table use for import product
     *
     * @return array : Response of connector
     */
    public function getProductsMain(){
        $query = $this->_getProductsMainQuery();
        $products = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query
        ));
        if(!$products || $products['result'] != 'success'){
            return $this->errorConnector(true);
        }
        if($this->_notice['config']['add_option']['seo_url'] && $this->_notice['config']['add_option']['seo_plugin']){
            $seo_model = 'LitExtension\CartMigration\Model\\' . $this->_pathToName($this->_notice['config']['add_option']['seo_plugin']);
            $this->_seo = $this->_objectManager->create($seo_model);
        }
        return $products;
    }

    /**
     * Get data relation use for import product
     *
     * @param array $products : Data of function getProductsMain
     * @return array : Response of connector
     */
    public function getProductsExt($products){
        $productsExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getProductsExtQuery($products);
        if($this->_seo){
            $seo_ext_query = $this->_seo->getProductsExtQuery($this, $products);
            if($seo_ext_query){
                $ext_query = array_merge($ext_query, $seo_ext_query);
            }
        }
        $cus_ext_query = $this->_custom->getProductsExtQueryCustom($this, $products);
        if($cus_ext_query){
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if($ext_query){
            $productsExt = $this->_getDataImport($this->_getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if(!$productsExt || $productsExt['result'] != 'success'){
                return $this->errorConnector(true);
            }
            $ext_rel_query = $this->_getProductsExtRelQuery($products, $productsExt);
            if($this->_seo){
                $seo_ext_rel_query = $this->_seo->getProductsExtRelQuery($this, $products, $productsExt);
                if($seo_ext_rel_query){
                    $ext_rel_query = array_merge($ext_rel_query, $seo_ext_rel_query);
                }
            }
            $cus_ext_rel_query = $this->_custom->getProductsExtRelQueryCustom($this, $products, $productsExt);
            if($cus_ext_rel_query){
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if($ext_rel_query){
                $productsExtRel = $this->_getDataImport($this->_getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if(!$productsExtRel || $productsExtRel['result'] != 'success'){
                    return $this->errorConnector(true);
                }
                $productsExt = $this->_syncResultQuery($productsExt, $productsExtRel);
            }
        }
        return $productsExt;
    }

    /**
     * Check product has been imported
     *
     * @param array $product : One row of object in function getProductsMain
     * @param array $productsExt : Data of function getProductsExt
     * @return boolean
     */
    public function checkProductImport($product, $productsExt){
        $id_src = $this->getProductId($product, $productsExt);
        return $this->getMageIdProduct($id_src);
    }

    /**
     * Import product with data convert in function convertProduct
     *
     * @param array $data : Data of function convertProduct
     * @param array $product : One row of object in function getProductsMain
     * @param array $productsExt : Data of function getProductsMain
     * @return array
     */
    public function importProduct($data, $product, $productsExt){
        if(Custom::PRODUCT_IMPORT){
            return $this->_custom->importProductCustom($this, $data, $product, $productsExt);
        }
        $product_tag = array();
        if (isset($data['tags'])) {
            $product_tag = $data['tags'];
            unset($data['tags']);
        }
        if(!isset($data['url_key'])) {
            $data['url_key'] = $this->generateProductUrlKey($data['name']);
        }
        $id_src = $this->getProductId($product, $productsExt);
        if (!$data['url_key']) {
            return array(
                'result' => 'warning',
                'msg' => $this->consoleWarning("Product Id = {$id_src} import failed. Error: URL key is empty!")
            );
        }
        $productIpt = $this->_process->product($data);
        if($productIpt['result'] == 'success'){
            $id_desc = $productIpt['mage_id'];
            $this->productSuccess($id_src, $id_desc);
            if ($product_tag) {
                $this->addProductTags($product_tag, $this->_notice['config']['languages'][$this->_notice['config']['default_lang']], $id_desc);
            }
        } else {
            $productIpt['result'] = 'warning';
            $msg = "Product Id = {$id_src} import failed. Error: " . $productIpt['msg'];
            $productIpt['msg'] = $this->consoleWarning($msg);
        }
        return $productIpt;
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
    public function afterSaveProduct($product_mage_id, $data, $product, $productsExt){
        $this->_custom->afterSaveProductCustom($this, $product_mage_id, $data, $product, $productsExt);
        return Custom::PRODUCT_AFTER_SAVE;
    }

    /**
     * Add more addition for product
     */
    public function additionProduct($data, $product, $productsExt){
        if(Custom::PRODUCT_ADDITION){
            return $this->_custom->additionProductCustom($this, $data, $product, $productsExt);
        }
        return array(
            'result' => "success"
        );
    }

    /**
     * Process before import import customers
     */
    public function prepareImportCustomers(){
        $this->_custom->prepareImportCustomersCustom($this);
    }

    /**
     * Get data of main table use for import customer
     *
     * @return array : Response of connector
     */
    public function getCustomersMain(){
        $query = $this->_getCustomersMainQuery();
        $customers = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query
        ));
        if(!$customers || $customers['result'] != 'success'){
            return $this->errorConnector(true);
        }
        return $customers;
    }

    /**
     * Get data relation use for import customer
     *
     * @param array $customers : Data of function getCustomersMain
     * @return array : Response of connector
     */
    public function getCustomersExt($customers){
        $customersExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getCustomersExtQuery($customers);
        $cus_ext_query = $this->_custom->getCustomersExtQueryCustom($this, $customers);
        if($cus_ext_query){
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if($ext_query){
            $customersExt = $this->_getDataImport($this->_getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if(!$customersExt || $customersExt['result'] != 'success'){
                return $this->errorConnector(true);
            }
            $ext_rel_query = $this->_getCustomersExtRelQuery($customers, $customersExt);
            $cus_ext_rel_query = $this->_custom->getCustomerExtRelQueryCustom($this, $customers, $customersExt);
            if($cus_ext_rel_query){
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if($ext_rel_query){
                $customersExtRel = $this->_getDataImport($this->_getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if(!$customersExtRel || $customersExtRel['result'] != 'success'){
                    return $this->errorConnector(true);
                }
                $customersExt = $this->_syncResultQuery($customersExt, $customersExtRel);
            }
        }
        return $customersExt;
    }

    /**
     * Check customer has been imported
     *
     * @param array $customer : One row of object in function getCustomersMain
     * @param array $customersExt : Data of function getCustomersExt
     * @return boolean
     */
    public function checkCustomerImport($customer, $customersExt){
        $id_src = $this->getCustomerId($customer, $customersExt);
        return $this->getMageIdCustomer($id_src);
    }

    /**
     * Import customer with data convert in function convertCustomer
     *
     * @param array $data : Data of function convertCustomer
     * @param array $customer : One row of object in function getCustomersMain
     * @param array $customersExt : Data of function getCustomersExt
     * @return array
     */
    public function importCustomer($data, $customer, $customersExt){
        if(Custom::CUSTOMER_IMPORT){
            return $this->_custom->importCustomerCustom($this, $data, $customer, $customersExt);
        }
        $id_src = $this->getCustomerId($customer, $customersExt);
        if(!isset($data['created_at']) || !$data['created_at']){
            $data['created_at'] = date("Y-m-d H:i:s");
        }
        $customerIpt = $this->_process->customer($data);
        if($customerIpt['result'] == 'success'){
            $id_desc = $customerIpt['mage_id'];
            $this->customerSuccess($id_src, $id_desc);
        } else {
            $customerIpt['result'] = 'warning';
            $msg = "Customer Id = {$id_src} import failed. Error: " . $customerIpt['msg'];
            $customerIpt['msg'] = $this->consoleWarning($msg);
        }
        return $customerIpt;
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
    public function afterSaveCustomer($customer_mage_id, $data, $customer, $customersExt){
        $this->_custom->afterSaveCustomerCustom($this, $customer_mage_id, $data, $customer, $customersExt);
        return Custom::CUSTOMER_AFTER_SAVE;
    }

    /**
     * Add more addition for customer
     */
    public function additionCustomer($data, $customer, $customersExt){
        if(Custom::CUSTOMER_ADDITION){
            return $this->_custom->additionCustomerCustom($this, $data, $customer, $customersExt);
        }
        return array(
            'result' => "success"
        );
    }

    /**
     * Process before import orders
     */
    public function prepareImportOrders(){
        $this->_custom->prepareImportOrdersCustom($this);
    }

    /**
     * Get data use for import order
     *
     * @return array : Response of connector
     */
    public function getOrdersMain(){
        $query = $this->_getOrdersMainQuery();
        $orders = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query
        ));
        if(!$orders || $orders['result'] != 'success'){
            return $this->errorConnector(true);
        }
        return $orders;
    }

    /**
     * Get data relation use for import order
     *
     * @param array $orders : Data of function getOrdersMain
     * @return array : Response of connector
     */
    public function getOrdersExt($orders){
        $ordersExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getOrdersExtQuery($orders);
        $cus_ext_query = $this->_custom->getOrdersExtQueryCustom($this, $orders);
        if($cus_ext_query){
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if($ext_query){
            $ordersExt = $this->_getDataImport($this->_getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if(!$ordersExt || $ordersExt['result'] != 'success'){
                return $this->errorConnector(true);
            }
            $ext_rel_query = $this->_getOrdersExtRelQuery($orders, $ordersExt);
            $cus_ext_rel_query = $this->_custom->getOrdersExtRelQueryCustom($this, $orders, $ordersExt);
            if($cus_ext_rel_query){
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if($ext_rel_query){
                $ordersExtRel = $this->_getDataImport($this->_getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if(!$ordersExtRel || $ordersExtRel['result'] != 'success'){
                    return $this->errorConnector(true);
                }
                $ordersExt = $this->_syncResultQuery($ordersExt, $ordersExtRel);
            }
        }
        return $ordersExt;
    }

    /**
     * Check order has been imported
     *
     * @param array $order : One row of object in function getOrdersMain
     * @param array $ordersExt : Data of function getOrdersExt
     * @return boolean
     */
    public function checkOrderImport($order, $ordersExt){
        $id_src = $this->getOrderId($order, $ordersExt);
        return $this->getMageIdOrder($id_src);
    }

    /**
     * Import order with data convert in function convertOrder
     *
     * @param array $data : Data of function convertOrder
     * @param array $order : One row of object in function getOrdersMain
     * @param array $ordersExt : Data of function getOrdersExt
     * @return boolean
     */
    public function importOrder($data, $order, $ordersExt){
        if(Custom::ORDER_IMPORT){
            return $this->_custom->importOrderCustom($this, $data, $order, $ordersExt);
        }
        $id_src = $this->getOrderId($order, $ordersExt);
        $orderIpt = $this->_process->order($data, $this->_notice['config']['add_option']['pre_ord']);
        if($orderIpt['result'] == 'success'){
            $id_desc = $orderIpt['mage_id'];
            $this->orderSuccess($id_src, $id_desc);
        } else {
            $orderIpt['result'] = 'warning';
            $msg = "Order Id = {$id_src} import failed. Error: " . $orderIpt['msg'];
            $orderIpt['msg'] = $this->consoleWarning($msg);
        }
        return $orderIpt;
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
    public function afterSaveOrder($order_mage_id, $data, $order, $ordersExt){
        $this->_custom->afterSaveOrderCustom($this, $order_mage_id, $data, $order, $ordersExt);
        return Custom::ORDER_AFTER_SAVE;
    }

    /**
     * Add more addition for order
     */
    public function additionOrder($data, $order, $ordersExt){
        if(Custom::ORDER_ADDITION){
            return $this->_custom->additionOrderCustom($this, $data, $order, $ordersExt);
        }
        return array(
            'result' => "success"
        );
    }

    /**
     * Process before import reviews
     */
    public function prepareImportReviews(){
        $this->_custom->prepareImportReviewsCustom($this);
        $this->_notice['extend']['rating'] = $this->getRatingOptions();
    }

    /**
     * Get main data use for import review
     *
     * @return array : Response of connector
     */
    public function getReviewsMain(){
        $query = $this->_getReviewsMainQuery();
        $reviews = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query
        ));
        if(!$reviews || $reviews['result'] != 'success'){
            return $this->errorConnector(true);
        }
        return $reviews;
    }

    /**
     * Get relation data use for import reviews
     *
     * @param array $reviews : Data of function getReviewsMain
     * @return array : Response of connector
     */
    public function getReviewsExt($reviews){
        $reviewsExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getReviewsExtQuery($reviews);
        $cus_ext_query = $this->_custom->getReviewsExtQueryCustom($this, $reviews);
        if($cus_ext_query){
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if($ext_query){
            $reviewsExt = $this->_getDataImport($this->_getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if(!$reviewsExt || $reviewsExt['result'] != 'success'){
                return $this->errorConnector(true);
            }
            $ext_rel_query = $this->_getReviewsExtRelQuery($reviews, $reviewsExt);
            $cus_ext_rel_query = $this->_custom->getReviewsExtRelQueryCustom($this, $reviews, $reviewsExt);
            if($cus_ext_rel_query){
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if($ext_rel_query){
                $reviewsExtRel = $this->_getDataImport($this->_getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if(!$reviewsExtRel || $reviewsExtRel['result'] != 'success'){
                    return $this->errorConnector(true);
                }
                $reviewsExt = $this->_syncResultQuery($reviewsExt, $reviewsExtRel);
            }
        }
        return $reviewsExt;
    }

    /**
     * Check review has been imported
     *
     * @param array $review : One row of object in function getReviewsMain
     * @param array $reviewsExt : Data of function getReviewsExt
     * @return boolean
     */
    public function checkReviewImport($review, $reviewsExt){
        $id_src = $this->getReviewId($review, $reviewsExt);
        return $this->getMageIdReview($id_src);
    }

    /**
     * Import review with data convert in function convertReview
     *
     * @param array $data : Data of function convertReview
     * @param array $review : One row of object in function getReviewsMain
     * @param array $reviewsExt : Data of function getReviewsExt
     * @return array
     */
    public function importReview($data, $review, $reviewsExt){
        if(Custom::REVIEW_IMPORT){
            return $this->_custom->importReviewCustom($this, $data, $review, $reviewsExt);
        }
        $id_src = $this->getReviewId($review, $reviewsExt);
        $reviewIpt = $this->_process->review($data, $this->_notice['extend']['rating']);
        if($reviewIpt['result'] == 'success'){
            $id_desc = $reviewIpt['mage_id'];
            $this->reviewSuccess($id_src, $id_desc);
        } else {
            $reviewIpt['result'] = 'warning';
            $msg = "Review Id = {$id_src} import failed. Error: " . $reviewIpt['msg'];
            $reviewIpt['msg'] = $this->consoleWarning($msg);
        }
        return $reviewIpt;
    }

    /**
     * Process after one review save successful
     *
     * @param int $review_mage_id : Id of review import to magento
     * @param array $data : Data of function convertReview
     * @param array $review : One row of object in function getReviewsMain
     * @param array $reviewsExt : Data of function getReviewsExt
     * @return boolean
     */
    public function afterSaveReview($review_mage_id, $data, $review, $reviewsExt){
        $this->_custom->afterSaveReviewCustom($this, $review_mage_id, $data, $review, $reviewsExt);
        return Custom::REVIEW_AFTER_SAVE;
    }

    /**
     * Add more addition for review
     */
    public function additionReview($data, $review, $reviewsExt){
        if(Custom::REVIEW_ADDITION){
            return $this->_custom->additionReviewCustom($this, $data, $review, $reviewsExt);
        }
        return array(
            'result' => "success"
        );
    }

    public function prepareImportPages()
    {
        return;
    }

    public function getPagesMain()
    {
        $query = $this->_getPagesMainQuery();
        $pages = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query
        ));
        if(!$pages || $pages['result'] != 'success'){
            return $this->errorConnector(true);
        }
        return $pages;
    }

    public function getPagesExt($pages)
    {
        $pagesExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getPagesExtQuery($pages);
        $cus_ext_query = $this->_custom->getPagesExtQueryCustom($this, $pages);
        if($cus_ext_query){
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if($ext_query){
            $pagesExt = $this->_getDataImport($this->_getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if(!$pagesExt || $pagesExt['result'] != 'success'){
                return $this->errorConnector(true);
            }
            $ext_rel_query = $this->_getPagesExtRelQuery($pages, $pagesExt);
            $cus_ext_rel_query = $this->_custom->getPagesExtRelQueryCustom($this, $pages, $pagesExt);
            if($cus_ext_rel_query){
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if($ext_rel_query){
                $pagesExtRel = $this->_getDataImport($this->_getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if(!$pagesExtRel || $pagesExtRel['result'] != 'success'){
                    return $this->errorConnector(true);
                }
                $pagesExt = $this->_syncResultQuery($pagesExt, $pagesExtRel);
            }
        }
        return $pagesExt;
    }

    public function getPageId($pages, $pagesExt)
    {
        return false;
    }

    public function checkPageImport($pages, $pagesExt)
    {
        $id_src = $this->getPageId($pages, $pagesExt);
        return $this->getMageIdPage($id_src);
    }

    public function convertPage($pages, $pagesExt)
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'data' => array()
        );
    }

    public function importPage($convert, $pages, $pagesExt)
    {
        if(Custom::PAGE_IMPORT){
            return $this->_custom->importPageCustom($this, $convert, $pages, $pagesExt);
        }
        $id_src = $this->getPageId($pages, $pagesExt);
        $pageIpt = $this->insertPage($convert);
        if($pageIpt['result'] == 'success'){
            $id_desc = $pageIpt['mage_id'];
            $this->pageSuccess($id_src, $id_desc);
        } else {
            $pageIpt['result'] = 'warning';
            $msg = "Page Id = {$id_src} import failed. Error: " . $pageIpt['msg'];
            $pageIpt['msg'] = $this->consoleWarning($msg);
        }
        return $pageIpt;
    }
    
    public function insertPage($data) {
        $response = $stores = array();
        if(isset($data['stores'])) {
            $stores = $data['stores'];
            unset($data['stores']);
        }
        foreach ($data as $key => $value) {
            if (is_numeric($key)) unset($data[$key]);
        }
        $table = $this->getTableName('cms_page');
        $table_store = $this->getTableName('cms_page_store');
        try {
            $this->insertTable($table, $data);
            $id_desc = $this->_db->lastInsertId($table);
            $this->insertTable($table_store, array('page_id' => $id_desc, 'store_id' => '0'));
            foreach ($stores as $store) {
                if ($store) {
                    $this->insertTable($table_store, array('page_id' => $id_desc, 'store_id' => $store));
                }
            }
            $response['result'] = 'success';
            $response['mage_id'] = $id_desc;
        } catch (\Exception $e) {
            $response['result'] = 'error';
            $response['msg'] = $e->getMessage();
        }
        return $response;
    }

    public function afterSavePage($page_id, $convert, $pages, $pagesExt)
    {
        return ;
    }

    public function additionPage($convert, $pages, $pagesExt)
    {
        return;
    }

    public function prepareImportBlocks()
    {
        return;
    }

    public function getBlocksMain()
    {
        $query = $this->_getBlocksMainQuery();
        $blocks = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query
        ));
        if(!$blocks || $blocks['result'] != 'success'){
            return $this->errorConnector(true);
        }
        return $blocks;
    }

    public function getBlocksExt($blocks)
    {
        $blocksExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getBlocksExtQuery($blocks);
        $cus_ext_query = $this->_custom->getBlocksExtQueryCustom($this, $blocks);
        if($cus_ext_query){
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if($ext_query){
            $blocksExt = $this->_getDataImport($this->_getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if(!$blocksExt || $blocksExt['result'] != 'success'){
                return $this->errorConnector(true);
            }
            $ext_rel_query = $this->_getBlocksExtRelQuery($blocks, $blocksExt);
            $cus_ext_rel_query = $this->_custom->getBlocksExtRelQueryCustom($this, $blocks, $blocksExt);
            if($cus_ext_rel_query){
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if($ext_rel_query){
                $blocksExtRel = $this->_getDataImport($this->_getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if(!$blocksExtRel || $blocksExtRel['result'] != 'success'){
                    return $this->errorConnector(true);
                }
                $blocksExt = $this->_syncResultQuery($blocksExt, $blocksExtRel);
            }
        }
        return $blocksExt;
    }

    public function getBlockId($blocks, $blocksExt)
    {
        return false;
    }

    public function checkBlockImport($blocks, $blocksExt)
    {
        $id_src = $this->getBlockId($blocks, $blocksExt);
        return $this->getMageIdBlock($id_src);
    }

    public function convertBlock($blocks, $blocksExt)
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'data' => array()
        );
    }

    public function importBlock($convert, $blocks, $blocksExt)
    {
        if(Custom::BLOCK_IMPORT){
            return $this->_custom->importBlockCustom($this, $convert, $blocks, $blocksExt);
        }
        $id_src = $this->getBlockId($blocks, $blocksExt);
        $blockIpt = $this->insertBlock($convert);
        if($blockIpt['result'] == 'success'){
            $id_desc = $blockIpt['mage_id'];
            $this->blockSuccess($id_src, $id_desc);
        } else {
            $blockIpt['result'] = 'warning';
            $msg = "Block Id = {$id_src} import failed. Error: " . $blockIpt['msg'];
            $blockIpt['msg'] = $this->consoleWarning($msg);
        }
        return $blockIpt;
    }
    
    public function insertBlock($data) {
        $response = $stores = array();
        if(isset($data['stores'])) {
            $stores = $data['stores'];
            unset($data['stores']);
        }
        foreach ($data as $key => $value) {
            if (is_numeric($key)) unset($data[$key]);
        }
        $table = $this->getTableName('cms_block');
        $table_store = $this->getTableName('cms_block_store');
        try {
            $this->insertTable($table, $data);
            $id_desc = $this->_db->lastInsertId($table);
            $this->insertTable($table_store, array('block_id' => $id_desc, 'store_id' => '0'));
            foreach ($stores as $store) {
                if ($store) {
                    $this->insertTable($table_store, array('block_id' => $id_desc, 'store_id' => $store));
                }
            }
            $response['result'] = 'success';
            $response['mage_id'] = $id_desc;
        } catch (\Exception $e) {
            $response['result'] = 'error';
            $response['msg'] = $e->getMessage();
        }
        return $response;
    }

    public function afterSaveBlock($block_id, $convert, $blocks, $blocksExt)
    {
        return ;
    }

    public function additionBlock($convert, $blocks, $blocksExt)
    {
        return;
    }

    public function prepareImportWidgets()
    {
        return;
    }

    public function getWidgetsMain()
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'object' => array()
        );
    }

    public function getWidgetsExt($widgets)
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'object' => array()
        );
    }

    public function getWidgetId($widgets, $widgetsExt)
    {
        return false;
    }

    public function checkWidgetImport($widgets, $widgetsExt)
    {
        return true;
    }

    public function convertWidget($widgets, $widgetsExt)
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'data' => array()
        );
    }

    public function importWidget($convert, $widgets, $widgetsExt)
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'mage_id' => 0
        );
    }

    public function afterSaveWidget($widget_id, $convert, $widgets, $widgetsExt)
    {
        return ;
    }

    public function additionWidget($convert, $widgets, $widgetsExt)
    {
        return;
    }

    public function prepareImportPolls()
    {
        return;
    }

    public function getPollsMain()
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'object' => array()
        );
    }

    public function getPollsExt($polls)
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'object' => array()
        );
    }

    public function getPollId($polls, $pollsExt)
    {
        return false;
    }

    public function checkPollImport($polls, $pollsExt)
    {
        return true;
    }

    public function convertPoll($polls, $pollsExt)
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'data' => array()
        );
    }

    public function importPoll($convert, $polls, $pollsExt)
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'mage_id' => 0
        );
    }

    public function afterSavePoll($poll_id, $convert, $polls, $pollsExt)
    {
        return ;
    }

    public function additionPoll($convert, $polls, $pollsExt)
    {
        return;
    }

    public function prepareImportTransactions()
    {
        return;
    }

    public function getTransactionsMain()
    {
        $query = $this->_getTransactionsMainQuery();
        $transactions = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query
        ));
        if(!$transactions || $transactions['result'] != 'success'){
            return $this->errorConnector(true);
        }
        return $transactions;
    }

    public function getTransactionsExt($transactions)
    {
        $transactionsExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getTransactionsExtQuery($transactions);
        $cus_ext_query = $this->_custom->getTransactionsExtQueryCustom($this, $transactions);
        if($cus_ext_query){
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if($ext_query){
            $transactionsExt = $this->_getDataImport($this->_getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if(!$transactionsExt || $transactionsExt['result'] != 'success'){
                return $this->errorConnector(true);
            }
            $ext_rel_query = $this->_getTransactionsExtRelQuery($transactions, $transactionsExt);
            $cus_ext_rel_query = $this->_custom->getTransactionsExtRelQueryCustom($this, $transactions, $transactionsExt);
            if($cus_ext_rel_query){
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if($ext_rel_query){
                $transactionsExtRel = $this->_getDataImport($this->_getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if(!$transactionsExtRel || $transactionsExtRel['result'] != 'success'){
                    return $this->errorConnector(true);
                }
                $transactionsExt = $this->_syncResultQuery($transactionsExt, $transactionsExtRel);
            }
        }
        return $transactionsExt;
    }

    public function getTransactionId($transactions, $transactionsExt)
    {
        return false;
    }

    public function checkTransactionImport($transactions, $transactionsExt)
    {
        $id_src = $this->getTransactionId($transactions, $transactionsExt);
        return $this->getMageIdTransaction($id_src);
    }

    public function convertTransaction($transactions, $transactionsExt)
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'data' => array()
        );
    }

    public function importTransaction($convert, $transactions, $transactionsExt)
    {
        if(Custom::TRANSACTION_IMPORT){
            return $this->_custom->importTransactionCustom($this, $convert, $transactions, $transactionsExt);
        }
        $id_src = $this->getTransactionId($transactions, $transactionsExt);
        $transactionIpt = $this->insertTransaction($convert);
        if($transactionIpt['result'] == 'success'){
            $id_desc = $transactionIpt['mage_id'];
            $this->transactionSuccess($id_src, $id_desc);
        } else {
            $transactionIpt['result'] = 'warning';
            $msg = "Transaction Id = {$id_src} import failed. Error: " . $transactionIpt['msg'];
            $transactionIpt['msg'] = $this->consoleWarning($msg);
        }
        return $transactionIpt;
    }
    
    public function insertTransaction($data) {
        $response = array();
        foreach ($data as $key => $value) {
            if (is_numeric($key)) unset($data[$key]);
        }
        $table = $this->getTableName('email_template');
        try {
            $this->insertTable($table, $data);
            $id_desc = $this->_db->lastInsertId($table);
            $response['result'] = 'success';
            $response['mage_id'] = $id_desc;
        } catch (\Exception $e) {
            $response['result'] = 'error';
            $response['msg'] = $e->getMessage();
        }
        return $response;
    }

    public function afterSaveTransaction($transaction_id, $convert, $transactions, $transactionsExt)
    {
        return ;
    }

    public function additionTransaction($convert, $transactions, $transactionsExt)
    {
        return;
    }

    public function prepareImportNewsletters()
    {
        return;
    }

    public function getNewslettersMain()
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'object' => array()
        );
    }

    public function getNewslettersExt($newsletters)
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'object' => array()
        );
    }

    public function getNewsletterId($newsletters, $newslettersExt)
    {
        return false;
    }

    public function checkNewsletterImport($newsletters, $newslettersExt)
    {
        return true;
    }

    public function convertNewsletter($newsletters, $newslettersExt)
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'data' => array()
        );
    }

    public function importNewsletter($convert, $newsletters, $newslettersExt)
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'mage_id' => 0
        );
    }

    public function afterSaveNewsletter($newsletter_id, $convert, $newsletters, $newslettersExt)
    {
        return ;
    }

    public function additionNewsletter($convert, $newsletters, $newslettersExt)
    {
        return;
    }

    public function prepareImportUsers()
    {
        return;
    }

    public function getUsersMain()
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'object' => array()
        );
    }

    public function getUsersExt($users)
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'object' => array()
        );
    }

    public function getUserId($users, $usersExt)
    {
        return false;
    }

    public function checkUserImport($users, $usersExt)
    {
        return true;
    }

    public function convertUser($users, $usersExt)
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'data' => array()
        );
    }

    public function importUser($convert, $users, $usersExt)
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'mage_id' => 0
        );
    }

    public function afterSaveUser($user_id, $convert, $users, $usersExt)
    {
        return ;
    }

    public function additionUser($convert, $users, $usersExt)
    {
        return;
    }

    public function prepareImportRules()
    {
        return;
    }

    public function getRulesMain()
    {
        $query = $this->_getRulesMainQuery();
        $rules = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query
        ));
        if(!$rules || $rules['result'] != 'success'){
            return $this->errorConnector(true);
        }
        return $rules;
    }

    public function getRulesExt($rules)
    {
        $rulesExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getRulesExtQuery($rules);
        $cus_ext_query = $this->_custom->getRulesExtQueryCustom($this, $rules);
        if($cus_ext_query){
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if($ext_query){
            $rulesExt = $this->_getDataImport($this->_getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if(!$rulesExt || $rulesExt['result'] != 'success'){
                return $this->errorConnector(true);
            }
            $ext_rel_query = $this->_getRulesExtRelQuery($rules, $rulesExt);
            $cus_ext_rel_query = $this->_custom->getRulesExtRelQueryCustom($this, $rules, $rulesExt);
            if($cus_ext_rel_query){
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if($ext_rel_query){
                $rulesExtRel = $this->_getDataImport($this->_getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if(!$rulesExtRel || $rulesExtRel['result'] != 'success'){
                    return $this->errorConnector(true);
                }
                $rulesExt = $this->_syncResultQuery($rulesExt, $rulesExtRel);
            }
        }
        return $rulesExt;
    }

    public function getRuleId($rules, $rulesExt)
    {
        return false;
    }

    public function checkRuleImport($rules, $rulesExt)
    {
        $id_src = $this->getRuleId($rules, $rulesExt);
        return $this->getMageIdRule($id_src);
    }

    public function convertRule($rules, $rulesExt)
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'data' => array()
        );
    }

    public function importRule($convert, $rules, $rulesExt)
    {
        if(Custom::RULE_IMPORT){
            return $this->_custom->importRuleCustom($this, $convert, $rules, $rulesExt);
        }
        $id_src = $this->getRuleId($rules, $rulesExt);
        $ruleIpt = $this->insertRule($convert);
        if($ruleIpt['result'] == 'success'){
            $id_desc = $ruleIpt['mage_id'];
            $this->ruleSuccess($id_src, $id_desc);
        } else {
            $ruleIpt['result'] = 'warning';
            $msg = "Rule Id = {$id_src} import failed. Error: " . $ruleIpt['msg'];
            $ruleIpt['msg'] = $this->consoleWarning($msg);
        }
        return $ruleIpt;
    }
    
    public function insertRule($data) {
        $response = $salesrule = $salesrule_coupon = $salesrule_customer_group = $salesrule_label = $salesrule_product_attribute = array();
        if(isset($data['salesrule'])) {
            $salesrule = $data['salesrule'];
        }
        if(isset($data['salesrule_coupon'])) {
            $salesrule_coupon = $data['salesrule_coupon'];
        }
        if(isset($data['salesrule_customer_group'])) {
            $salesrule_customer_group = $data['salesrule_customer_group'];
        }
        if(isset($data['salesrule_label'])) {
            $salesrule_label = $data['salesrule_label'];
        }
        if(isset($data['salesrule_product_attribute'])) {
            $salesrule_product_attribute = $data['salesrule_product_attribute'];
        }
        $table = $this->getTableName('salesrule');
        $table_coupon = $this->getTableName('salesrule_coupon');
        $table_cus_group = $this->getTableName('salesrule_customer_group');
        $table_website = $this->getTableName('salesrule_website');
        $table_label = $this->getTableName('salesrule_label');
        $table_prd_attr = $this->getTableName('salesrule_product_attribute');
        try {
            $salesrule = $this->cleanArrayKeys($salesrule);
            $this->insertTable($table, $salesrule);
            $id_desc = $this->_db->lastInsertId($table);
            foreach ($salesrule_coupon as $coupon) {
                $coupon = $this->cleanArrayKeys($coupon);
                $coupon['rule_id'] = $id_desc;
                $this->insertTable($table_coupon, $coupon);
            }
            foreach ($salesrule_customer_group as $group) {
                $this->insertTable($table_cus_group, array('rule_id' => $id_desc, 'customer_group_id' => $group));
            }
            foreach ($salesrule_label as $label) {
                $label = $this->cleanArrayKeys($label);
                $label['rule_id'] = $id_desc;
                $this->insertTable($table_label, $label);
            }
            $this->insertTable($table_website, array('rule_id' => $id_desc, 'website_id' => $this->_notice['config']['website_id']));
            foreach ($salesrule_product_attribute as $attr) {
                $attr = $this->cleanArrayKeys($attr);
                $attr['rule_id'] = $id_desc;
                $this->insertTable($table_prd_attr, $attr);
            }
            $response['result'] = 'success';
            $response['mage_id'] = $id_desc;
        } catch (\Exception $e) {
            $response['result'] = 'error';
            $response['msg'] = $e->getMessage();
        }
        return $response;
    }

    public function afterSaveRule($rule_id, $convert, $rules, $rulesExt)
    {
        return ;
    }

    public function additionRule($convert, $rules, $rulesExt)
    {
        return;
    }
    
    public function prepareImportCartrules()
    {
        return;
    }
    
    public function getCartrulesMain()
    {
        $query = $this->_getCartrulesMainQuery();
        $rules = $this->_getDataImport($this->_getUrlConnector('query'), array(
            'query' => $query
        ));
        if(!$rules || $rules['result'] != 'success'){
            return $this->errorConnector(true);
        }
        return $rules;
    }

    public function getCartrulesExt($rules)
    {
        $rulesExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getCartrulesExtQuery($rules);
        $cus_ext_query = $this->_custom->getCartrulesExtQueryCustom($this, $rules);
        if($cus_ext_query){
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if($ext_query){
            $rulesExt = $this->_getDataImport($this->_getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if(!$rulesExt || $rulesExt['result'] != 'success'){
                return $this->errorConnector(true);
            }
            $ext_rel_query = $this->_getCartrulesExtRelQuery($rules, $rulesExt);
            $cus_ext_rel_query = $this->_custom->getCartrulesExtRelQueryCustom($this, $rules, $rulesExt);
            if($cus_ext_rel_query){
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if($ext_rel_query){
                $rulesExtRel = $this->_getDataImport($this->_getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if(!$rulesExtRel || $rulesExtRel['result'] != 'success'){
                    return $this->errorConnector(true);
                }
                $rulesExt = $this->_syncResultQuery($rulesExt, $rulesExtRel);
            }
        }
        return $rulesExt;
    }

    public function getCartruleId($rules, $rulesExt)
    {
        return false;
    }

    public function checkCartruleImport($rules, $rulesExt)
    {
        $id_src = $this->getCartruleId($rules, $rulesExt);
        return $this->getMageIdCartrule($id_src);
    }

    public function convertCartrule($rules, $rulesExt)
    {
        return array(
            'result' => 'success',
            'msg' => '',
            'data' => array()
        );
    }

    public function importCartrule($convert, $rules, $rulesExt)
    {
        if(Custom::CARTRULE_IMPORT){
            return $this->_custom->importCartruleCustom($this, $convert, $rules, $rulesExt);
        }
        $id_src = $this->getCartruleId($rules, $rulesExt);
        $ruleIpt = $this->insertCartrule($convert);
        if($ruleIpt['result'] == 'success'){
            $id_desc = $ruleIpt['mage_id'];
            $this->cartruleSuccess($id_src, $id_desc);
        } else {
            $ruleIpt['result'] = 'warning';
            $msg = "Cart Rule Id = {$id_src} import failed. Error: " . $ruleIpt['msg'];
            $ruleIpt['msg'] = $this->consoleWarning($msg);
        }
        return $ruleIpt;
    }
    
    public function insertCartrule($data) {
        $response = $catalogrule = $customer_group = array();
        if(isset($data['catalogrule'])) {
            $catalogrule = $data['catalogrule'];
        }
        if(isset($data['customer_group'])) {
            $customer_group = $data['customer_group'];
        }
        $table = $this->getTableName('catalogrule');
        $table_group_website = $this->getTableName('catalogrule_group_website');
        $table_website = $this->getTableName('catalogrule_website');
        $table_customer_group = $this->getTableName('catalogrule_customer_group');
        try {
            $catalogrule = $this->cleanArrayKeys($catalogrule);
            $this->insertTable($table, $catalogrule);
            $id_desc = $this->_db->lastInsertId($table);
            foreach ($customer_group as $group) {
                $this->insertTable($table_group_website, array('rule_id' => $id_desc, 'customer_group_id' => $group, 'website_id' => $this->_notice['config']['website_id']));
                $this->insertTable($table_website, array('rule_id' => $id_desc, 'website_id' => $this->_notice['config']['website_id']));
                $this->insertTable($table_customer_group, array('rule_id' => $id_desc, 'customer_group_id' => $group));
            }
            $response['result'] = 'success';
            $response['mage_id'] = $id_desc;
        } catch (\Exception $e) {
            $response['result'] = 'error';
            $response['msg'] = $e->getMessage();
        }
        return $response;
    }

    public function afterSaveCartrule($rule_id, $convert, $rules, $rulesExt)
    {
        return ;
    }

    public function additionCartrule($convert, $rules, $rulesExt)
    {
        return;
    }

    /**
     * Process clear cache adn reindex data after finish migration
     *
     * @return array
     */
    public function finishImport(){
        $response = array(
            'result' => 'success',
            'msg' => ''
        );
        $clear = $this->_process->clearCache();
        $index = $this->_process->reIndexes();
        if($clear['result'] != 'success' || $index['result'] != 'success'){
            if($clear['msg']){
                $response['msg'] .= $this->consoleWarning($clear['msg']);
            }
            if($index['msg']){
                $response['msg'] .= $this->consoleWarning($index['msg']);
            }
        } else {
            $response['msg'] = $this->consoleSuccess("Finished Clear cache & Reindex data");
        }
        return $response;
    }

    /**
     * TODO: Module Database
     */

    /**
     * Insert data to table lecamg_import
     */
    protected  function _insertLeCaMgImport($type, $id_import, $mage_id, $status, $value = false){
        return $this->insertTable(self::TABLE_IMPORTS, array(
            'domain' => $this->_cart_url,
            'type' => $type,
            'id_import' => $id_import,
            'mage_id' => $mage_id,
            'status' => $status,
            'value' => $value
        ));
    }

    protected  function _insertLeCaUpdate($id_import, $mage_id, $value = false){
        return $this->insertTable(self::TABLE_UPDATE, array(
            'domain' => $this->_cart_url,
            'id_import' => $id_import,
            'mage_id' => $mage_id,
            'value' => $value
        ));
    }

    /**
     * Insert data to table lecamg_user
     */
    protected  function _insertLeCaMgUser($user_id, $notice){
        if(is_array($notice)){
            $notice = serialize($notice);
        }
        return $this->insertTable(self::TABLE_USER, array(
            'user_id' => $user_id,
            'notice' => $notice
        ));
    }

    /**
     * Insert data to table lecamg_recent
     */
    protected  function _insertLeCaMgRecent($domain, $notice){
        if(is_array($notice)){
            $notice = serialize($notice);
        }
        return $this->insertTable(self::TABLE_RECENT, array(
            'domain' => $domain,
            'notice' => $notice
        ));
    }

    /**
     * Update data to table lecamg_import with condition
     *
     * @param array $data
     * @param array $where
     * @return boolean
     */
    protected  function _updateLeCaMgImport($data, $where){
        return $this->updateTable(self::TABLE_IMPORTS, $data, $where);
    }

    /**
     * Update data to table lecamg_user with admin id
     */
    protected  function _updateLeCaMgUser($user_id, $notice){
        if(is_array($notice)){
            $notice = serialize($notice);
        }
        return $this->updateTable(self::TABLE_USER, array('notice' => $notice), array('user_id' => $user_id));
    }

    /**
     * Update data to table lecamg_recent with domain
     */
    protected  function _updateLeCaMgRecent($domain, $notice){
        if(is_array($notice)){
            $notice = serialize($notice);
        }
        return $this->updateTable(self::TABLE_RECENT, array('notice' => $notice), array('domain' => $domain));
    }

    /**
     * Delete all data of table lecamg_import by domain
     */
    protected  function _deleteLeCaMgImport($domain){
        return $this->deleteTable(self::TABLE_IMPORTS, array(
            'domain' => $domain
        ));
    }

    /**
     * Delete data of table lecamg_user by admin id
     */
    protected  function _deleteLeCaMgUser($user_id){
        return $this->deleteTable(self::TABLE_USER, array(
            'user_id' => $user_id
        ));
    }

    /**
     * Delete data of table lecamg_recent by domain
     */
    protected  function _deleteLeCaMgRecent($domain){
        return $this->deleteTable(self::TABLE_RECENT, array(
            'domain' => $domain
        ));
    }

    /**
     * Get data of table lecamg_import with conditioin
     *
     * @param array $where
     * @return array
     */
    protected  function _selectLeCaMgImport($where){
        return $this->selectTableRow(self::TABLE_IMPORTS, $where);
    }

    /**
     * Get data of table lecamg_user with admin id
     */
    protected  function _selectLeCaMgUser($user_id){
        return $this->selectTableRow(self::TABLE_USER, array(
            'user_id' => $user_id
        ));
    }

    /**
     * Get data of table lecamg_recent with domain
     */
    protected  function _selectLeCaMgRecent($domain){
        return $this->selectTableRow(self::TABLE_RECENT, array(
            'domain' => $domain
        ));
    }

    /**
     * Get magento tax id import by src id
     */
    public function getMageIdTax($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_TAX,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }

    /**
     * Get magento tax customer id import by src id
     */
    public function getMageIdTaxCustomer($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_TAX_CUSTOMER,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }

    /**
     * Get magento tax product id import by src id
     */
    public function getMageIdTaxProduct($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_TAX_PRODUCT,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }

    /**
     * Get magento tax rate id import by src id
     */
    public function getMageIdTaxRate($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_TAX_RATE,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }

    /**
     * Get magento attribute manufacturer id import by src id
     */
    public function getMageIdManAttr($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_MAN_ATTR,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }

    /**
     * Get magento manufacturer option id import by src id
     */
    public function getMageIdManufacturer($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_MANUFACTURER,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }

    /**
     * Get magento category id import by src id
     */
    public function getMageIdCategory($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_CATEGORY,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }

    /**
     * Get magento product id import by src id
     */
    public function getMageIdProduct($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_PRODUCT,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }

    /**
     * Get magento attribute id import by src id
     */
    public function getMageIdAttribute($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_ATTR,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }

    /**
     * Get magento attribute option id import by src id
     */
    public function getMageIdAttrOption($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_ATTR_OPTION,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }

    /**
     * Get magento customer id import by src id
     */
    public function getMageIdCustomer($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_CUSTOMER,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }

    /**
     * Get magento order id import by src id
     */
    public function getMageIdOrder($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_ORDER,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }

    /**
     * Get magento review id import by src id
     */
    public function getMageIdReview($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_REVIEW,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }
    
    /**
     * Get magento page id import by src id
     */
    public function getMageIdPage($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_PAGE,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }
    
    /**
     * Get magento block id import by src id
     */
    public function getMageIdBlock($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_BLOCK,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }
    
    /**
     * Get magento rule id import by src id
     */
    public function getMageIdRule($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_RULE,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }
    
    /**
     * Get magento transaction id import by src id
     */
    public function getMageIdTransaction($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_TRANSACTION,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }
    
    /**
     * Get magento cat rule id import by src id
     */
    public function getMageIdCartrule($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => self::TYPE_CARTRULE,
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }
    
    public function getMageIdBundleOption($id_import){
        $result = $this->_selectLeCaMgImport(array(
            'domain' => $this->_cart_url,
            'type' => 'bundle_option',
            'id_import' => $id_import
        ));
        if(!$result){
            return false;
        }
        return $result['mage_id'];
    }

    /**
     * Save info of tax import successful to table lecamg_import
     */
    public function taxSuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_TAX, $id_import, $mage_id, 1, $value);
    }

    /**
     * Save info of tax customer import successful to table lecamg_import
     */
    public function taxCustomerSuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_TAX_CUSTOMER, $id_import, $mage_id, 1, $value);
    }

    /**
     * Save info of tax product import successful to table lecamg_import
     */
    public function taxProductSuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_TAX_PRODUCT, $id_import, $mage_id, 1, $value);
    }

    /**
     * Save info of tax rate import successful to table lecamg_import
     */
    public function taxRateSuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_TAX_RATE, $id_import, $mage_id, 1, $value);
    }

    /**
     * Save info of manufacturer attribute import successful to table lecamg_import
     */
    public function manAttrSuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_MAN_ATTR, $id_import, $mage_id, 1, $value);
    }

    /**
     * Save info of manufacturer option import successful to table lecamg_import
     */
    public function manufacturerSuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_MANUFACTURER, $id_import, $mage_id, 1, $value);
    }

    /**
     * Save info of category import successful to table lecamg_import
     */
    public function categorySuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_CATEGORY, $id_import, $mage_id, 1, $value);
    }

    /**
     * Save info of product import successful to table lecamg_import
     */
    public function productSuccess($id_import, $mage_id, $value = false){
        $this->_insertLeCaUpdate($id_import, $mage_id, $value);
        return $this->_insertLeCaMgImport(self::TYPE_PRODUCT, $id_import, $mage_id, 1, $value);
    }

    /**
     * Save info of attribute import successful to table lecamg_import
     */
    public function attributeSuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_ATTR, $id_import, $mage_id, 1, $value);
    }

    /**
     * Save info of attribute option import successful to table lecamg_import
     */
    public function attrOptionSuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_ATTR_OPTION, $id_import, $mage_id, 1, $value);
    }

    /**
     * Save info of customer import successful to table lecamg_import
     */
    public function customerSuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_CUSTOMER, $id_import, $mage_id, 1, $value);
    }

    /**
     * Save info of order import successful to table lecamg_import
     */
    public function orderSuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_ORDER, $id_import, $mage_id, 1, $value);
    }

    /**
     * Save info of review import successful to table lecamg_import
     */
    public function reviewSuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_REVIEW, $id_import, $mage_id, 1, $value);
    }
    
    /**
     * Save info of page import successful to table lecamg_import
     */
    public function pageSuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_PAGE, $id_import, $mage_id, 1, $value);
    }
    
    /**
     * Save info of block import successful to table lecamg_import
     */
    public function blockSuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_BLOCK, $id_import, $mage_id, 1, $value);
    }
    
    /**
     * Save info of transaction import successful to table lecamg_import
     */
    public function transactionSuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_TRANSACTION, $id_import, $mage_id, 1, $value);
    }
    
    /**
     * Save info of rule import successful to table lecamg_import
     */
    public function ruleSuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_RULE, $id_import, $mage_id, 1, $value);
    }
    
    /**
     * Save info of cartrule import successful to table lecamg_import
     */
    public function cartruleSuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_CARTRULE, $id_import, $mage_id, 1, $value);
    }
    
    public function bundleOptionSuccess($id_import, $mage_id, $value = false){
        return $this->_insertLeCaMgImport('bundle_option', $id_import, $mage_id, 1, $value);
    }

    /**
     * Save info of tax import error to table lecamg_import
     */
    public function taxError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_TAX, $id_import, false, 1, $value);
    }

    /**
     * Save info of tax customer import error to table lecamg_import
     */
    public function taxCustomerError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_TAX_CUSTOMER, $id_import, false, 1, $value);
    }

    /**
     * Save info of tax product import error to table lecamg_import
     */
    public function taxProductError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_TAX_PRODUCT, $id_import, false, 1, $value);
    }

    /**
     * Save info of tax rate import error to table lecamg_import
     */
    public function taxRateError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_TAX_RATE, $id_import, false, 1, $value);
    }

    /**
     * Save info of manufacturer attribute import error to table lecamg_import
     */
    public function manAttrError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_MAN_ATTR, $id_import, false, 1, $value);
    }

    /**
     * Save info of manufacturer import error to table lecamg_import
     */
    public function manufacturerError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_MANUFACTURER, $id_import, false, 1, $value);
    }

    /**
     * Save info of category import error to table lecamg_import
     */
    public function categoryError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_CATEGORY, $id_import, false, 1, $value);
    }

    /**
     * Save info of product import error to table lecamg_import
     */
    public function productError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_PRODUCT, $id_import, false, 1, $value);
    }

    /**
     * Save info of attribute import error to table lecamg_import
     */
    public function attributeError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_ATTR, $id_import, false, 1, $value);
    }

    /**
     * Save info of attribute option import error to table lecamg_import
     */
    public function attrOptionError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_ATTR_OPTION, $id_import, false, 1, $value);
    }

    /**
     * Save info of customer import error to table lecamg_import
     */
    public function customerError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_CUSTOMER, $id_import, false, 1, $value);
    }

    /**
     * Save info of order import error to table lecamg_import
     */
    public function orderError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_ORDER, $id_import, false, 1, $value);
    }

    /**
     * Save info of review import error to table lecamg_import
     */
    public function reviewError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_REVIEW, $id_import, false, 1, $value);
    }
    
    /**
     * Save info of page import error to table lecamg_import
     */
    public function pageError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_PAGE, $id_import, false, 1, $value);
    }
    
    /**
     * Save info of block import error to table lecamg_import
     */
    public function blockError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_BLOCK, $id_import, false, 1, $value);
    }
    
    /**
     * Save info of transaction import error to table lecamg_import
     */
    public function transactionError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_TRANSACTION, $id_import, false, 1, $value);
    }
    
    /**
     * Save info of rule import error to table lecamg_import
     */
    public function ruleError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_RULE, $id_import, false, 1, $value);
    }
    
    /**
     * Save info of cartrule import error to table lecamg_import
     */
    public function cartruleError($id_import, $value = false){
        return $this->_insertLeCaMgImport(self::TYPE_CARTRULE, $id_import, false, 1, $value);
    }

    /**
     * TODO: Connector
     */

    /**
     * Get url of source cart connector with action and token
     */
    protected  function _getUrlConnector($action){
        $url = $this->_cart_url . '/magento_connector/connector.php?token=' . $this->_cart_token . '&action=' . $action;
        return $url;
    }

    /**
     * Get url of source cart with suffix
     */
    public function getUrlSuffix($suffix){
        $url = rtrim($this->_cart_url, '/') . '/' . ltrim($suffix, '/');
        return $url;
    }

    /**
     * Change _DBPRF_ to table prefix detect or custom config
     */
    protected  function _addTablePrefix($data){
        if(isset($data['query'])){
            if($this->_notice['setting']['prefix']){
                $prefix = $this->_notice['setting']['prefix'];
            } else {
                $prefix = $this->_notice['config']['table_prefix'];
            }
            if(isset($data['serialize'])){
                $queries = unserialize($data['query']);
                $add = array();
                foreach($queries as $table => $query){
                    $change = str_replace('_DBPRF_', $prefix, $query);
                    $add[$table] = $change;
                }
                $data['query'] = serialize($add);
            } else {
                $query = $data['query'];
                $data['query'] = str_replace('_DBPRF_', $prefix, $query);
            }
        }
        return $data;
    }

    /**
     * Set charset for database convert
     */
    protected  function _insertParamCharSet($data)
    {
        $charset = array('utf8', 'cp1251');
        if (in_array($this->_notice['config']['charset'], $charset)) {
            $data['char_set'] = 'utf8';
        }
        return $data;
    }

    /**
     * Get data of source cart connector with url and data
     */

    /*
    protected  function _getDataImport($url, $data = array(), $query = true){
        $result = null;

        if($query){
            $data = $this->_addTablePrefix($data);
            $data = $this->_insertParamCharSet($data);
        }
        $client = new Zend_Http_Client($url);
        if($data){
            foreach($data as $key => $value){
                $client->setParameterPost($key, base64_encode($value));
            }
        }
        $client->setMethod(Zend_Http_Client::POST);
        $response = $client->request();
        if($response && $response->getBody()){
            $result = unserialize(base64_decode($response->getBody()));
        }
        sleep($this->_notice['setting']['delay']);
        return $result;
    }
    */

    protected  function _getDataImport($url, $data = array(), $query = true){
        $result = null;
        if($query){
            $data = $this->_addTablePrefix($data);
            $data = $this->_insertParamCharSet($data);
        }
        if($data){
            foreach($data as $key => $value){
                $data[$key] = base64_encode($value);
            }
        }
        $options = http_build_query($data);
		
        $userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0';
        $ch = curl_init($url);
        if($this->_notice['curl']['useragent']){
            curl_setopt( $ch, CURLOPT_USERAGENT, $userAgent );
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $options);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt_array($ch, array(CURLINFO_HEADER_OUT => true));
        $response = curl_exec($ch);
        curl_close($ch);
        if($this->_notice['setting']['delay']){
            @sleep($this->_notice['setting']['delay']);
        }
        if ($response) {
            return unserialize(base64_decode($response));
        }
        return false;
    }


    public function getDataImportByQuery($data){
        return $this->_getDataImport($this->_getUrlConnector('query'), array(
            'serialize' => true,
            'query' => serialize($data)
        ));
    }

    /**
     * Get list array from list by list field  value
     */
    public function getListFromListByListField($list, $field, $values){
        if(!$list){
            return array();
        }
        if(!is_array($values)){
            $values = array($values);
        }
        $result = array();
        foreach($list as $row){
            if(in_array($row[$field], $values)){
                $result[] = $row;
            }
        }
        return $result;
    }

    /**
     * Get list array from list by field  value
     */
    public function getListFromListByField($list, $field, $value){
        if(!$list){
            return array();
        }
        $result = array();
        foreach($list as $row){
            if($row[$field] == $value){
                $result[] = $row;
            }
        }
        return $result;
    }

    /**
     * Get one array from list array by field value
     */
    public function getRowFromListByField($list, $field, $value){
        if(!$list){
            return false;
        }
        $result = false;
        foreach($list as $row){
            if(isset($row[$field]) && $row[$field] == $value){
                $result = $row;
                break ;
            }
        }
        return $result;
    }

    /**
     * Get array value from list array by field value and key of field need
     */
    public function getRowValueFromListByField($list, $field, $value, $need){
        if(!$list){
            return false;
        }
        $row = $this->getRowFromListByField($list, $field, $value);
        if(!$row){
            return false;
        }
        return isset($row[$need]) ? $row[$need] : '';
    }

    /**
     * Get and unique array value by key
     */
    public function duplicateFieldValueFromList($list, $field){
        $result = array();
        if(!$list){
            return $result;
        }
        foreach ((array)$list as $item) {
            if (isset($item[$field])) {
                $result[] = $item[$field];
            }
        }
        $result = array_unique($result);
        return $result;
    }

    /**
     * Sync two result of connector
     */
    protected  function _syncResultQuery($data, $extra){
        if($data['object'] && $extra['object']){
            foreach($extra['object'] as $key => $rows){
                if(!isset($data['object'][$key])){
                    $data['object'][$key] = $rows;
                }
            }
        }
        return $data;
    }

    /**
     * TODO: Magento Core
     */

    /**
     * Get website id by store id
     */
    public function getWebsiteIdByStoreId($store_id){
        $store = $this->_objectManager->create('Magento\Store\Model\Store')->load($store_id);
        $website_id = $store->getWebsiteId();
        return $website_id;
    }

    /**
     * Get list website id by list store id
     */
    public function getWebsiteIdsByStoreIds($store_ids){
        if($store_ids && !empty($store_ids)){
            $website_id = array();
            foreach($store_ids as $store_id){
                $store = $this->_objectManager->create('Magento\Store\Model\Store')->load($store_id);
                $website_id[] = $store->getWebsiteId();
            }
            return $this->_filterArrayValueDuplicate($website_id);
        }
        return false;
    }

    /**
     * Get currency config of store and base website
     */
    public function getStoreCurrencyCode($store_id){
        $result = array();
        $store = $this->_objectManager->create('Magento\Store\Model\Store')->load($store_id);
        $result['base'] = $store->getBaseCurrencyCode();
        $result['current'] = $store->getCurrentCurrencyCode();
        return $result;
    }
    
    public function getProductMainTable() {
        return $this->getTableName('catalog_product_entity');
    }
    
    public function getCategoryMainTable() {
        return $this->getTableName('catalog_category_entity');
    }

    /**
     * Set attribute select to product
     */
    public function setProAttrSelect($attribute_id, $product_id, $option_id){
        $this->insertTable('catalog_product_entity_int', array(
            'attribute_id' => $attribute_id,
            'store_id' => 0,
            $this->_process->getLinkField($this->getProductMainTable()) => $product_id,
            'value' => $option_id
        ));
    }

    /**
     * Set attribute date to product
     */
    public function setProAttrDate($attribute_id, $product_id, $date){
        $this->insertTable('catalog_product_entity_datetime', array(
            'attribute_id' => $attribute_id,
            'store_id' => 0,
            $this->_process->getLinkField($this->getProductMainTable()) => $product_id,
            'value' => $date
        ));
    }

    /**
     * Set attribute text to product
     */
    public function setProAttrText($attribute_id, $product_id, $text){
        $this->insertTable('catalog_product_entity_text', array(
            'attribute_id' => $attribute_id,
            'store_id' => 0,
            $this->_process->getLinkField($this->getProductMainTable()) => $product_id,
            'value' => $text
        ));
    }

    /**
     * Set attribute varchar to product
     */
    public function setProAttrVarchar($attribute_id, $product_id, $varchar){
        $this->insertTable('catalog_product_entity_varchar', array(
            'attribute_id' => $attribute_id,
            'store_id' => 0,
            $this->_process->getLinkField($this->getProductMainTable()) => $product_id,
            'value' => $varchar
        ));
    }

    /**
     * Set option to product
     */
    public function setProductHasOption($product_id, $hasRequiredOptions = true){
        $this->updateTable('catalog_product_entity', array(
            'has_options' => true,
            'required_options' => (bool)$hasRequiredOptions
        ), array(
            'entity_id' => $product_id
        ));
    }
    
    /**
     * Format URL key to product
     */
    public function generateProductUrlKey($name) {
        $url_def = $this->_objectManager->create('Magento\Catalog\Model\Product')->formatUrlKey($name);
        return $this->generateUrlKeyFromUrlKey($url_def);
    }
    
    /**
     * Format URL key to category
     */
    public function generateCategoryUrlKey($name) {
        $url_def = $this->_objectManager->create('Magento\Catalog\Model\Category')->formatUrlKey($name);
        return $this->generateUrlKeyFromUrlKey($url_def);
    }

    /**
     * Import custom option to product
     */
    public function importProductOptionOld($product_id, $options){
        try{
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
            if(!$product->getOptionsReadonly()) {
                foreach($options as $option){
                    $opt = $this->_objectManager->create('Magento\Catalog\Model\Product\Option');
                    $opt->setProduct($product);
                    $opt->addOption($option);
                    $opt->saveOptions();
                }
                $this->setProductHasOption($product_id);
            }
        } catch(\Exception $e){}
    }
    
    public function importProductOption($product_id, $options){
        try {
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
            if (!$product->getOptionsReadonly()) {
                $customOptions = array();
                $hasRequiredOptions = false;
                foreach ($options as $option) {
                    $opt = $this->_objectManager->create('Magento\Catalog\Model\Product\Option');
                    $opt->setData($option)
                            ->setProductSku($product->getSku());
                    $customOptions[] = $opt;
                    if ($option['is_require']) {
                        $hasRequiredOptions = true;
                    }
                }
                $product->setOptions($customOptions);
                $product->save();
                $this->setProductHasOption($product_id, $hasRequiredOptions);
            }
        } catch (\Exception $e) {
            
        }
    }
    
    public function importProductBundleOption($bundleOptData, $bundleSelectData) {
        $tableOption = $this->getTableName('catalog_product_bundle_option');
        $tableOptionVal = $this->getTableName('catalog_product_bundle_option_value');
        $tableSelect = $this->getTableName('catalog_product_bundle_selection');
        foreach ($bundleOptData as $key => $bOpt) {
            $dataOpt = [
                'parent_id' => $bOpt['parent_id'],
                'required' => $bOpt['required'],
                'position' => $bOpt['position'],
                'type' => $bOpt['type'],
            ];
            //$where = ['attribute_id =?' => $attr_id, 'attribute_set_id =?' => $data['attribute_set_id']];
            //$connection->delete($tableOption, $where);
            $this->_db->insert($tableOption, $dataOpt);
            $id_option = $this->_db->lastInsertId($tableOption);
            $this->bundleOptionSuccess($bOpt['option_id_src'], $id_option);

            $dataOptVal = [
                'option_id' => $id_option,
                'store_id' => '0',
                'title' => $bOpt['title']
            ];
            $this->_db->insert($tableOptionVal, $dataOptVal);
            //Selection
            foreach ($bundleSelectData[$key] as $bSel) {
                $dataSel = [
                    'option_id' => $id_option,
                    'parent_product_id' => $bOpt['parent_id'],
                    'product_id' => $bSel['product_id'],
                    'position' => $bSel['position'],
                    'is_default' => $bSel['is_default'],
                    'selection_price_type' => $bSel['selection_price_type'],
                    'selection_price_value' => $bSel['selection_price_value'],
                    'selection_qty' => $bSel['selection_qty'],
                    'selection_can_change_qty' => $bSel['selection_can_change_qty'],
                ];
                $this->_db->insert($tableSelect, $dataSel);
            }
        }
    }
    
    public function importProductBundleSelection($bundleSelectData) {
        $tableSelect = $this->getTableName('catalog_product_bundle_selection');
        $this->_db->insert($tableSelect, $bundleSelectData);
    }
    
    /**
     * Set grouped product links
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $links
     * @return \Magento\Catalog\Model\Product
     */
    public function setProductGroupLinks(\Magento\Catalog\Model\Product $product, $links) {
        if ($product->getTypeId() === 'grouped' && !$product->getGroupedReadonly()) {
            //$links = $product->getGroupedLinkData();
            $newLinks = [];
            $existingLinks = $product->getProductLinks();
            foreach ($links as $linkRaw) {
                if (!isset($linkRaw['id'])) {
                    continue;
                }
                $productId = $linkRaw['id'];
                if (!isset($linkRaw['qty'])) {
                    $linkRaw['qty'] = 0;
                }
                $productLink = $this->_objectManager->create('Magento\Catalog\Model\ProductLink\Link');
                $linkedProduct = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
                $productLink->setSku($product->getSku())
                    ->setLinkType('associated')
                    ->setLinkedProductSku($linkedProduct->getSku())
                    ->setLinkedProductType($linkedProduct->getTypeId())
                    ->setPosition($linkRaw['position'])
                    ->getExtensionAttributes()
                    ->setQty($linkRaw['qty']);
                $newLinks[] = $productLink;
            }
            //$existingLinks = $this->removeExistingLinks($existingLinks, $newLinks);
            $product->setProductLinks(array_merge($existingLinks, $newLinks));
        }
        return $product;
    }

    /**
     * Get list rating review
     */
    public function getRatingOptions(){
        $data = array();
        $ratings = $this->_objectManager->create('Magento\Review\Model\Rating')->getCollection();
        foreach($ratings as $rating){
            $rating_id = $rating->getId();
            $options = $this->_objectManager->create('Magento\Review\Model\Rating\Option')->getCollection();
            $options->addRatingFilter($rating_id);
            $tmp = array();
            foreach($options as $option){
                $tmp = array_merge($tmp, array($option->getId()));
            }
            $data[$rating_id] = array_values($tmp);
        }
        return $data;
    }

    /**
     * Get or create default tax customer
     */
    public function getTaxCustomerDefault(){
        $response = array();
        $customerTax = $this->_objectManager->create('Magento\Tax\Model\ClassModel')
            ->getCollection()
            ->setClassTypeFilter(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER)
            ->getFirstItem();
        if($customerTax->getId()){
            $response['result'] = 'success';
            $response['mage_id'] = $customerTax->getId();
        } else{
            $newCustomerTax = $this->_objectManager->create('Magento\Tax\Model\ClassModel');
            $newCustomerTax->setClassType(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER);
            $newCustomerTax->setClassName('Retail Customer');
            try{
                $newCustomerTax->save();
                $response['result'] = 'success';
                $new_tax_customer_id = $newCustomerTax->getId();
                $response['mage_id'] = $new_tax_customer_id;
                $group = $this->_objectManager->create('Magento\Customer\Model\Group');
                $collection = $group->getCollection();
                foreach($collection as $item){
                    if($item->getCustomerGroupCode() == 'NOT LOGGED IN' || $item->getCustomerGroupCode() == 'General'){
                        $item->setTaxClassId($new_tax_customer_id);
                        try{$item->save();}catch (\Exception $e){}
                    }
                }
            } catch(\Exception $e){
                $response['result'] = 'error';
                $response['msg'] = $e->getMessage();
            }
        }
        return $response;
    }

    /**
     * Get or create manufacturer attribute
     */
    public function getManufacturerAttributeId($attribute_set_id){
        $result = array();
        $data = array(
            'attribute_code' 				=> self::MANUFACTURER_CODE,
            'frontend_input'				=> 'select',
            'backend_type'					=> 'int',
            //'apply_to'						=> array(),
            'is_global'						=> 1,
            'is_unique' 					=> 0,
            'is_required' 					=> 0,
            'is_configurable' 				=> 1,
            'is_searchable' 				=> 0,
            'is_visible_in_advanced_search' => 0,
            'is_comparable' 				=> 0,
            'is_filterable' 				=> 0,
            'is_filterable_in_search' 		=> 0,
            'is_used_for_promo_rules' 		=> 0,
            'is_user_defined'               => 1,
            'is_html_allowed_on_front' 		=> 1,
            'is_visible_on_front' 			=> 0,
            'used_in_product_listing' 		=> 0,
            'used_for_sort_by' 				=> 0,
            'frontend_label' 				=> array(
                '0'	=> 'Manufacturer',
            ),
        );
        $entityTypeId = $this->_objectManager->create('Magento\Eav\Model\Entity')->setType(\Magento\Catalog\Model\Product::ENTITY)->getTypeId();
        $manufacture = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute')
            ->getCollection()
            ->addFieldToFilter('attribute_code', $data['attribute_code'])
            ->setEntityTypeFilter($entityTypeId)
            ->getFirstItem();
        if($manufacture->getId()){
            $result['result'] = 'success';
            $result['mage_id'] = $manufacture->getId();
            if($attribute_group_id = $this->_getAttributeGroupId($attribute_set_id)){
                $manufacture->setAttributeSetId($attribute_set_id);
                $manufacture->setAttributeGroupId($attribute_group_id);
                try{
                    $manufacture->save();
                } catch(\Exception $e){
                    // do nothing
                }
            }
        } else {
            $attr = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute');
            $attr->setData($data);
            $attr->setEntityTypeId($entityTypeId);
            $attr->setAttributeSetId($attribute_set_id);
            if($attribute_group_id = $this->_getAttributeGroupId($attribute_set_id)){
                $attr->setAttributeGroupId($attribute_group_id);
            }
            try{
                $attr->save();
                $result['result'] = 'success';
                $result['mage_id'] = $attr->getId();
            } catch(\Exception $e){
                $result['result'] = 'error';
                $result['msg'] = $e->getMessage();
            }
        }
        return $result;
    }

    /**
     * Get default group attribute by attribute set
     */
    protected function _getAttributeGroupId($attribute_set_id){
        $attribute_group_id = false;
        $group_general = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\Group')
            ->getCollection()
            ->setAttributeSetFilter($attribute_set_id)
            ->addFieldToFilter('attribute_group_code', 'general')
            ->getFirstItem();
        if($group_general->getId()){
            $attribute_group_id = $group_general->getId();
        } else{
            $group_first = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\Group')
                ->getCollection()
                ->addFieldToFilter('attribute_set_id', $attribute_set_id)
                ->getFirstItem();
            if($group_first->getId()){
                $attribute_group_id = $group_first->getId();
            } else {
                $data = array(
                    'attribute_group_name'  => 'General',
                    'attribute_set_id'      => $attribute_set_id
                );
                $attrSet = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\Set');
                $attrGroup = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\Group');
                $attrGroup->addData($data);
                try{
                    $attrGroup->save();
                    $attrSet->setGroups(array($attrGroup));
                    $attribute_group_id = $attrGroup->getId();
                } catch(\Exception $e){
                    // do nothing
                }
            }
        }
        return $attribute_group_id;
    }

    /**
     * Create tax rule code with string
     */
    public function createTaxRuleCode($code){
        $i = 0;
        $new_code = $code;
        while($this->_checkTaxRuleCodeExists($new_code)){
            $i++;
            $new_code = $code.'-'.$i;
        }
        return $new_code;
    }

    /**
     * Check tax rule code exists
     */
    protected function _checkTaxRuleCodeExists($code){
        $taxRate = $this->_objectManager->create('Magento\Tax\Model\Calculation\Rule')
            ->getCollection()
            ->addFieldToFilter('code', $code)
            ->getFirstItem();
        if($taxRate->getId()){
            return true;
        }
        return false;
    }

    /**
     * Create tax rate code with string
     */
    public function createTaxRateCode($code){
        $i = 0;
        $new_code = $code;
        while($this->_checkTaxRateCodeExist($new_code)){
            $i++;
            $new_code = $code.' - '.$i;
        }
        return $new_code;
    }

    /**
     * Check tax rate code exists
     */
    protected function _checkTaxRateCodeExist($code){
        $taxRate = $this->_objectManager->create('Magento\Tax\Model\Calculation\Rule')
            ->getCollection()
            ->addFieldToFilter('code', $code)
            ->getFirstItem();
        if($taxRate->getId()){
            return true;
        }
        return false;
    }

    /**
     * Create product sku by string
     */
    public function createProductSku($sku, $store_ids){
        $i = 0;
        $new_sku = $sku;
        while($this->_checkProductSkuExists($new_sku, $store_ids)){
            $i++;
            $new_sku = $sku.'-'.$i;
        }
        return $new_sku;
    }

    /**
     * Check product sku exists
     */
    protected function _checkProductSkuExists($sku, $store_ids){
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')
            ->getCollection()
            ->addAttributeToSelect("sku")
            ->addFieldToFilter("sku", array('eq' => $sku))
            ->getFirstItem();
        if($product->getId()){
            return true;
        }
        return false;
    }

    /**
     * Import password for customer
     */
    protected function _importCustomerRawPass($customer_id, $pass){
        //$entityTypeId = $this->_objectManager->create('Magento\Eav\Model\Entity')->setType(\Magento\Customer\Model\Customer::ENTITY)->getTypeId();
        //$attrPass = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute')->loadByCode(\Magento\Customer\Model\Customer::ENTITY, 'password_hash');
        //$attrPassId = $attrPass->getAttributeId();
        return $this->updateTable('customer_entity', array(
            'password_hash' => $pass
        ), array(
            'entity_id' => $customer_id
        ));
    }

    /**
     * Get region id by name state and country iso code 2
     */
    public function getRegionId($name , $code){
        $result = null;
        $regions = $this->_objectManager->create('Magento\Directory\Model\Region')
            ->getCollection()
            ->addRegionNameFilter($name)
            ->addCountryFilter($code)
            ->getFirstItem();
        if($regions->getId()){
            $result = $regions->getId();
        } else{
            $result = 0;
        }
        return $result;
    }

    /**
     * Get order state by order status
     */
    public function getOrderStateByStatus($status){
        $result = false;
        $collection = $this->_objectManager->create('Magento\Sales\Model\Order\Status')->getCollection()->joinStates();
        foreach($collection as $item){
            if($item['status'] == $status){
                $result = $item['state'];
                break ;
            }
        }
        return $result;
    }

    /**
     * TODO: Magento Database
     */

    /**
     * Convert array to in condition in mysql query
     */
    public function arrayToInCondition($array){
        if(empty($array)){
            return "('null')";
        }
        $result = "('".implode("','", $array)."')";
        return $result;
    }

    /**
     * Convert array to set values condition in mysql query
     */
    public function arrayToSetCondition($array){
        if(empty($array)){
            return '';
        }
        $data = array();
        foreach($array as $key => $value){
            $data[] = "`" . $key . "` = '" . $value . "'";
        }
        $result = implode(',', $data);
        return $result;
    }

    /**
     * Convert array to string insert use in raw query
     *
     * @param array $data
     * @param array $allow_keys
     * @return array
     */
    public function arrayToInsertQueryObject($data, $allow_keys = array()){
        if(!$data){
            return false;
        }
        $items = array();
        $keys = array_keys($data);
        $data_allow = array();
        if(!$allow_keys){
            $items = $keys;
            $data_allow = $data;
        } else {
            foreach($keys as $key){
                if(in_array($key, $allow_keys)){
                    $items[] = $key;
                    $data_allow[$key] = $data[$key];
                }
            }
        }
        if(!$items){
            return false;
        }
        $row = '(' . implode(', ', $items) . ')';
        $value = '(:' . implode(', :', $items) . ')';
        return array(
            'row' => $row,
            'value' => $value,
            'data' => $data_allow
        );
    }

    /**
     * Convert array to string update use in raw query
     *
     * @param array $data
     * @param array $allow_keys
     * @return array
     */
    public function arrayToUpdateQueryObject($data, $allow_keys = array()){
        if(!$data){
            return false;
        }
        $items = array();
        $keys = array_keys($data);
        if(!$allow_keys){
            $allow_keys = $keys;
        }
        foreach($keys as $key){
            if(in_array($key, $allow_keys)){
                $set = $key . '= :' . $key;
                $items[] = $set;
            }
        }
        if(!$items){
            return false;
        }
        $set_query = implode(', ', $items);
        return $set_query;
    }

    /**
     * Convert array to where condition in mysql query
     */
    public function arrayToWhereCondition($array){
        if(empty($array)){
            return '1 = 1';
        }
        $data = array();
        foreach($array as $key => $value){
            $data[] = "`{$key}` = '{$value}'";
        }
        $result = implode(" AND ", $data);
        return $result;
    }

    /**
     * Get table in magento database with table prefix
     */
    public function getTableName($name){
        return $this->_resource->getTableName($name);
    }

    /**
     * Run write query with magento database
     */
    public function writeQuery($query, $bind = array()){
        try{
            $this->_db->query($query, $bind);
            return true;
        }catch (\Exception $e){
            if(Custom::DEV_MODE){
                $message = "\LitExtension\CartMigration\Model\Cart::writeQuery() error: " . $e->getMessage();
                $this->_logger->error($message);
            }
            return false;
        }
    }

    /**
     * Run read query with magento database
     */
    public function readQuery($query, $bind= array()){
        try{
            $result = $this->_db->fetchAll($query, $bind);
            return array(
                'result' => 'success',
                'data' => $result
            );
        }catch (\Exception $e){
            if(Custom::DEV_MODE){
                $message = "\LitExtension\CartMigration\Model\Cart::readQuery() error: " . $e->getMessage();
                $this->_logger->error($message);
            }
            return array(
                'result' => 'error',
                'msg' => $e->getMessage()
            );
        }
    }

    /**
     * Get data from table by where condition
     *
     * @param string $table
     * @param array $where
     * @return array
     */
    public function selectTable($table, $where = array()){
        $where_query = $this->arrayToWhereCondition($where);
        $table_name = $this->getTableName($table);
        $query = "SELECT * FROM " . $table_name . " WHERE " . $where_query;
        try{
            $result = $this->_db->fetchAll($query);
            return $result;
        }catch (\Exception $e){
            if(Custom::DEV_MODE){
                $message = "\LitExtension\CartMigration\Model\Cart::selectTable() error: " . $e->getMessage();
                $this->_logger->error($message);
            }
            return false;
        }
    }

    /**
     * Insert data with type array to table
     *
     * @param string $table
     * @param array $data
     * @param array $allow_keys
     * @return boolean
     */
    public function insertTable($table, $data, $allow_keys = array()){
        $obj = $this->arrayToInsertQueryObject($data, $allow_keys);
        if(!$obj){
            return false;
        }
        $row = $obj['row'];
        $value = $obj['value'];
        $data_allow = $obj['data'];
        $table_name = $this->getTableName($table);
        $query = "INSERT INTO " . $table_name . " " . $row ." VALUES " . $value;
        try{
            $this->_db->query($query, $data_allow);
            return true;
        }catch (\Exception $e){
            if(Custom::DEV_MODE){
                $message = "\LitExtension\CartMigration\Model\Cart::insertTable() error: " . $e->getMessage();
                $this->_logger->error($message);
            }
            return false;
        }
    }

    /**
     * Update data with type array to table by where condition
     *
     * @param string $table
     * @param array $data
     * @param array $where
     * @param array $allow_keys
     * @return boolean
     */
    public function updateTable($table, $data, $where, $allow_keys = array()){
        $set_query = $this->arrayToUpdateQueryObject($data, $allow_keys);
        if(!$set_query){
            return false;
        }
        $where_query = $this->arrayToWhereCondition($where);
        $table_name = $this->getTableName($table);
        $query = "UPDATE " . $table_name . " SET " . $set_query . " WHERE " . $where_query;
        try{
            $this->_db->query($query, $data);
            return true;
        }catch (\Exception $e){
            if(Custom::DEV_MODE){
                $message = "\LitExtension\CartMigration\Model\Cart::updateTable() error: " . $e->getMessage();
                $this->_logger->error($message);
            }
            return false;
        }
    }

    /**
     * Delete data from table by where condition
     *
     * @param string $table
     * @param array $where
     * @return boolean
     */
    public function deleteTable($table, $where){
        $where_query = $this->arrayToWhereCondition($where);
        $table_name = $this->getTableName($table);
        $query = "DELETE FROM " . $table_name . " WHERE " . $where_query;
        try{
            $this->_db->query($query);
            return true;
        }catch (\Exception $e){
            if(Custom::DEV_MODE){
                $message = "\LitExtension\CartMigration\Model\Cart::deleteTable() error: " . $e->getMessage();
                $this->_logger->error($message);
            }
            return false;
        }
    }

    /**
     * Get one row of result select
     *
     * @param string $table
     * @param array $where
     * @return array
     */
    public function selectTableRow($table, $where){
        $result = $this->selectTable($table, $where);
        if(!$result){
            return false;
        }
        return (isset($result[0])) ? $result[0] : false;
    }


    /**
     * TODO: Extends
     */

    /**
     * Convert result of query get count to count
     */
    public function arrayToCount($array){
        if(empty($array)){
            return 0;
        }
		$count = 0;
        if(isset($array[0]['COUNT(1)'])){
            $count = $array[0]['COUNT(1)'];
        } elseif (isset($array[0][0])) {
            $count = $array[0][0];
        }
        return $count;
    }

    /**
     * Add class success to text for show in console
     */
    public function consoleSuccess($msg){
        $result = '<p class="success"> - ' . $msg . '</p>';
        $this->_logger->info($msg . "\r\n");
        return $result;
    }

    /**
     * Add class warning to text for show in console
     */
    public function consoleWarning($msg){
        $result = '<p class="warning"> - ' . $msg . '</p>';
        $this->_logger->info($msg . "\r\n");
        return $result;
    }

    /**
     * Add class error to text for show in console
     */
    public function consoleError($msg){
        $result = '<p class="error"> - ' . $msg . '</p>';
        $this->_logger->info($msg . "\r\n");
        return $result;
    }

    /**
     * Message if not connector to connector
     */
    public function errorConnector($console = false){
        $msg = "Could not connect to Connector!";
        if($console){
            $msg = $this->consoleError($msg);
        }
        return array(
            'result' => 'error',
            'msg' => $msg
        );
    }

    /**
     * Message if not save info to magento database
     */
    public function errorDatabase($console = false){
        $msg = "Magento database isn't working!";
        if($console){
            $msg = $this->consoleError($msg);
        }
        return array(
            'result' => 'error',
            'msg' => $msg
        );
    }

    /**
     * Convert time to string show in console
     */
    public function createTimeToShow($time){
        $hour = gmdate('H', $time);
        $minute = gmdate('i', $time);
        $second = gmdate('s', $time);
        $result = '';
        if($hour && $hour > 0) $result .= $hour.' hours ';
        if($minute && $minute > 0) $result .= $minute. ' minutes ';
        if($second && $second >0 ) $result .= $second . ' seconds ';
        return $result;
    }

    /**
     * Create key by string
     */
    public function joinTextToKey($text, $length = false, $char = '-', $lower = true){
        $text .= " ";
        if($length){
            $length = (int) $length;
            $text = substr($text, 0, $length);
            if($end = strrpos($text, ' ')){
                $text = substr($text, 0, strrpos($text, ' '));
            }
        }
        $text = preg_replace('/[^A-Za-z0-9 ]/', '', $text);
        $text = preg_replace('/\s+/', ' ',$text);
        $text = str_replace(' ', $char, $text);
        $text = trim($text, $char);
        if($lower) $text = strtolower($text);
        return $text;
    }

    /**
     * Filter value of array 3D
     */
    protected  function _filterArrayValueDuplicate($array){
        $result = array();
        if($array && !empty($array)){
            $array_values = array_values($array);
            foreach($array_values as $key => $value){
                foreach($array_values  as $key_filter => $value_filter){
                    if($key_filter < $key){
                        if($value == $value_filter){
                            unset($array_values[$key]);
                        }
                    }
                }
            }
            $result = array_values($array_values);
        }
        return $result;
    }

    public function filterArrayValueFalse($array){
        if(!$array){
            return $array;
        }
        foreach($array as $key => $value){
            if(!$value){
                unset($array[$key]);
            }
        }
        return $array;
    }

    /**
     * Check sync cart type select and cart type detect
     */
    protected  function _checkCartSync($cms, $select) {
        $pos = strpos($select, $cms);
        if($pos === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get percent by total and import
     */
    public function getPoint($total, $import, $finish = false){
        if(!$finish && $total == 0){
            return 0;
        }
        if($finish){
            return 100;
        }
        if ($total < $import) {
            $point = 100;
        } else {
            $percent = $import / $total;
            $point = number_format($percent, 2) * 100;
        }
        return $point;
    }

    /**
     * Get message for next entity import
     */
    public function getMsgStartImport($type){
        $result = '';
        if(!$type){
            $result .= $this->consoleSuccess("Finished migration!");
            return $result;
        }
        $entities = array(
            'taxes' => 'Taxes',
            'manufacturers' => 'Manufacturers',
            'categories' => 'Categories',
            'products' => 'Products',
            'customers' => 'Customers',
            'orders' => 'Orders',
			'reviews' => 'Reviews',
            'pages' => 'Pages',
            'blocks' => 'Static blocks',
            'widgets' => 'Widgets',
            'polls' => 'Polls',
            'transactions' => 'Transaction email',
            'newsletters' => 'Newsletter template',
            'users' => 'Users',
            'rules' => 'Rules',
            'cartrules' => 'Cart Rules'
        );
        $types = array_keys($entities);
        $labels = array_values($entities);
        $type_key = array_search($type, $types);
        foreach ($types as $key => $value) {
            if ($type_key <= $key && $this->_notice['config']['import'][$value]) {
                $result .= $this->consoleSuccess('Importing ' . $labels[$key] . ' ... ');
                break;
            }
        }
        return $result;
    }

    /**
     * Increment order price pass through magento order grand total not equal 0
     */
    public function incrementPriceToImport($price){
        if($price == 0){
            $price = 0.001;
        }
        return $price;
    }

    /**
     * Convert string of full name to first name and last name
     */
    public function getNameFromString($name){
        $result = array();
        $parts = explode(' ', $name);
        $result['lastname'] = array_pop($parts);
        $result['firstname'] = implode(" ", $parts);
        return $result;
    }

    /**
     * Check url exists
     */
    protected  function _urlExists($url){
        return true;
    }

    public function imageExists($url){
        $header = @get_headers($url, 1);
        if(!$header){
            return false;
        }
        $string = $header[0];
        if(strpos($string, "404")){
            return false;
        }
        return true;
    }

    public function defaultResponse(){
        return array(
            'result' => '',
            'msg' => '',
            'elm' => '',
            'html' => ''
        );
    }

    /**
     * TODO: Image
     */

    /**
     * Download image to media folder
     */
    public function downloadImage($url, $image_path, $type, $base_name = false, $return_path = false, $check_ext = true, $insert_ext = false){
        try{
            //if($check_ext && !$this->_checkFileTypeImport($image_path)){
                //return false;
            //}
            $desc_location = $this->_objectManager->get('Magento\Store\Model\Store')->getBaseMediaDir() . '/' . $type . '/';
            if(!is_dir($desc_location)){
                @mkdir($desc_location, 0777, true);
            }
            $img_src = rtrim($url, '/') . '/' ;
            if($this->_isUrlEncode($image_path)){
                $img_src .= ltrim($image_path, '/');
            } else {
                $img_src .= ltrim($this->_getUrlRealPath($image_path), '/');
            }
			//var_dump($img_src);exit;
            if(!$this->imageExists($img_src)){
                return false;
            }
            if(!$base_name){
                $path_save = $this->_createPathToSave(basename($image_path));
                $img_desc = $desc_location . $path_save;
            } else {
                $path_save = $this->_createPathToSave($image_path);
                $img_desc = $desc_location. $path_save;
                if(!is_dir(dirname($img_desc))){
                    @mkdir(dirname($img_desc), 0777, true);
                }
            }
            if($insert_ext){
                $extension = '';
                $img_src .= '?'.$insert_ext;
                $path_save .= $this->_createPathToSave($insert_ext);
                $header = @get_headers($img_src, 1);
                if($header){
                    $string = $header[0];
                    if (strpos($string, "404")) {
                        return false;
                    }
                    $content_type = $header['Content-Type'];
                    $extension = $this->_getImageTypeByContentType($content_type);
                }
                $path_save .= $extension;
                $img_desc = $desc_location. $path_save;
            }
            $path = false;
            if ($image_path != '') {
                $fp = fopen($img_desc, 'w');
                $ch = curl_init($img_src);
                curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30); //10s
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $data = curl_exec($ch);
                curl_close($ch);
                fclose($fp);
            }
            if (file_exists($img_desc) && filesize($img_desc)) {
                if (function_exists('exif_imagetype')) {
                    if (!exif_imagetype($img_desc)) return false;
                } elseif (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimetype = finfo_file($finfo, $img_desc);
                    finfo_close($finfo);
                    if (strpos($mimetype, 'image') !== false){

                    } else {
                        return false;
                    }
                } elseif (function_exists('getimagesize')) {
                    $exif = @getimagesize($img_desc);
                    if (!$exif) return false;
                }
                if(!$return_path){
                    $path = $path_save;
                } else {
                    $path = $img_desc;
                }
            } else {
                return false;
            }
            return $path;
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * Check image type for import
     */
    protected  function _checkFileTypeImport($file_name){
        $result = false;
        $typesAllow = array('jpg', 'jpeg', 'gif', 'png');
        $file_type = pathinfo($file_name, PATHINFO_EXTENSION);
        if(in_array(strtolower($file_type), $typesAllow)){
            $result = true;
        }
        return $result;
    }

    /**
     * Create url by encode special character
     */
    protected  function _getUrlRealPath($path){
        $splits = explode('/', $path);
        $data = array();
        foreach($splits as $key => $split){
            $data[$key] = rawurlencode($split);
        }
        $path = implode('/', $data);
        return $path;
    }

    /**
     * Create path save by replace special character to -
     */
    protected  function _createPathToSave($path){
        $splits = explode('/',$path);
        $data = array();
        foreach($splits as $key => $split){
            $split = preg_replace('/[^A-Za-z0-9._\-]/', '-', $split);
            $data[$key] = $split;
        }
        $path = implode('/',$data);
        return $path;
    }

    protected  function _isUrlEncode($path){
        $is_encoded = @preg_match('~%[0-9A-F]{2}~i', $path);
        return $is_encoded;
    }

    /**
     * Detect image extension with content type
     */
    protected  function _getImageTypeByContentType($content_type){
        $result = '';
        $mineType =array(
            'image/jpeg'    => '.jpg',
            'image/png'     => '.png',
            'image/gif'     => '.gif',
            'image/pjpeg'   => '.jpeg',
            'image/x-icon'  => '.ico',
        );
        if($mineType[$content_type]){
            $result = $mineType[$content_type];
        }
        return $result;
    }

    /**
     * Download image and change image tag in text
     */
    public function changeImgSrcInText($html, $img_des){
        if(!$img_des){ return $html;}
        $links = array();
        preg_match_all('/<img[^>]+>/i', $html, $img_tags);
        foreach ($img_tags[0] as $img) {
            if(!$img){
                continue;
            }
            preg_match('/(src(.*?)=(.*?)["\'](.*?)["\'])/', $img, $src);
            if(!isset($src[0])){
                continue;
            }
            $split = preg_split('/["\']/', $src[0]);
            $links[] = $split[1];
        }
        $links = $this->_filterArrayValueDuplicate($links);
        foreach($links as $link){
            if($new_link = $this->_getImgDesUrlImport($link)){
                $html = str_replace($link, $new_link, $html);
            }
        }
        return $html;
    }

    /**
     * Download image and change image tag in array
     */
    public function changeImgSrcInList($list, $fields, $img_des){
        if(!$img_des){
            return $list;
        }
        if(is_string($fields)){
            $fields = array($fields);
        }
        $links = array();
        foreach($list as $row){
            foreach($fields as $field){
                if(!isset($row[$field])){
                    continue ;
                }
                $content = $row[$field];
                if(!$content){
                    continue ;
                }
                preg_match_all('/<img[^>]+>/i', $content, $img_tags);
                foreach ($img_tags[0] as $img) {
                    if(!$img){
                        continue;
                    }
                    preg_match('/(src=["\'](.*?)["\'])/', $img, $src);
                    if(!isset($src[0])){
                        continue;
                    }
                    $split = preg_split('/["\']/', $src[0]);
                    $links[] = $split[1];
                }
            }
        }
        $links = $this->_filterArrayValueDuplicate($links);
        $data = array();
        foreach($links as $link){
            $new_link = $this->_getImgDesUrlImport($link);
            if($new_link){
                $data[] = array(
                    'old' => $link,
                    'new' => $new_link
                );
            }
        }
        if(!$data){
            return $list;
        }
        foreach($list as $key => $row){
            foreach($fields as $field){
                if(!isset($row[$field])){
                    continue ;
                }
                $content = $row[$field];
                if(!$content){
                    continue ;
                }
                foreach($data as $link){
                    $pattern = array(
                        '/src="' . $link['old'] . '"/',
                        "/src='" . $link['old'] . "'/",
                    );
                    $replacement = array(
                        'src="' . $link['new'] . '"',
                        "src='" . $link['new'] . "'",
                    );
                    $content = preg_replace($pattern, $replacement, $content);
                }
                $list[$key][$field] = $content;
            }
        }
        return $list;
    }

    /**
     * Download image with url
     */
    protected  function _getImgDesUrlImport($url){
        $result = false;
        $insert_extension = false;
        $url_tmp = parse_url($url);
        if(isset($url_tmp['host'])){
            $host = $url_tmp['scheme'].'://'.$url_tmp['host'];
            $path = substr($url_tmp['path'],1);
            if(isset($url_tmp['query'])){
                $insert_extension = $url_tmp['query'];
            }
        } else {
            if(substr($url_tmp['path'], 0, 2) == '//'){
                $real_url = 'http:' . $url;
                $url_tmp = parse_url($real_url);
                $host = $url_tmp['scheme'].'://'.$url_tmp['host'];
                $path = substr($url_tmp['path'],1);
                if(isset($url_tmp['query'])){
                    $insert_extension = $url_tmp['query'];
                }
            } else {
                $host = $this->_cart_url;
                $path = $url_tmp['path'];
            }
        }
        if($path_import = $this->downloadImage($host, $path, 'wysiwyg', false, false, false, $insert_extension)){
            $result = $this->_objectManager->get('Magento\Store\Model\Store')->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'wysiwyg/' . $path_import;
        }
        return $result;
    }

    /**
     * TODO: Demo Mode
     */

    /**
     * Set limit for demo mode
     */
    protected  function _limitDemoModel($counts){
        if (!self::DEMO_MODE) {
            $limit = false;
            $license = trim($this->_scopeConfig->getValue('lecm/general/license'));
            if ($license) {
                $check_license = $this->_getDataImport(
                        chr(104) . chr(116) . chr(116) . chr(112) . chr(58) . chr(47) . chr(47) . chr(108) . chr(105) . chr(116) . chr(101) . chr(120) . chr(116) . chr(101) . chr(110) . chr(115) . chr(105) . chr(111) . chr(110) . chr(46) . chr(99) . chr(111) . chr(109) . chr(47) . chr(108) . chr(105) . chr(99) . chr(101) . chr(110) . chr(115) . chr(101) . chr(46) . chr(112) . chr(104) . chr(112), array(
                    'user' => chr(108) . chr(105) . chr(116) . chr(101) . chr(120),
                    'pass' => chr(97) . chr(65) . chr(49) . chr(50) . chr(51) . chr(52) . chr(53) . chr(54),
                    'action' => chr(99) . chr(104) . chr(101) . chr(99) . chr(107),
                    'license' => $license,
                    'cart_type' => $this->_notice['config']['cart_type'],
                    'url' => $this->_cart_url,
                    'target_type' => base64_encode('magento2'),
                    'save' => true
                        ), false
                );
                if ($check_license['result'] == 'success') {
                    $limit = $check_license['data']['limit'];
                }
            }
            $this->_notice['config']['limit'] = $limit ? $limit : 0;
            $data = array();
            if (!$limit) {
                foreach ($counts as $type => $count) {
                    $data[$type] = 0;
                }
                return $data;
            } else {
                $total = $limit;
                if ($limit === 'unlimit') {
                    $limit = 'unlimited';
                    $this->_notice['config']['limit'] = 'unlimited';
                }
                if ($limit !== 'unlimited') {
                    foreach ($counts as $type => $count) {
                        $new_count = ($count < $total) ? $count : $total;
                        $counts[$type] = $new_count;
                    }
                }
            }
        } else {
            $this->_notice['config']['limit'] = 10;
            $data = array();
            foreach($counts as $type => $count){
                $data[$type] = ($count < $this->_demo_limit[$type])? $count : $this->_demo_limit[$type];
            }
            return $data;
        }
        return $counts;
    }

    public function updateApi(){
        $license = trim($this->_scopeConfig->getValue('lecm/general/license'));
        if(!$license){
            return ;
        }
        $update_license = $this->_getDataImport(
            chr(104).chr(116).chr(116).chr(112).chr(58).chr(47).chr(47).chr(108).chr(105).chr(116).chr(101).chr(120).chr(116).chr(101).chr(110).chr(115).chr(105).chr(111).chr(110).chr(46).chr(99).chr(111).chr(109).chr(47).chr(108).chr(105).chr(99).chr(101).chr(110).chr(115).chr(101).chr(46).chr(112).chr(104).chr(112),
            array(
                'user' => chr(108).chr(105).chr(116).chr(101).chr(120),
                'pass' => chr(97).chr(65).chr(49).chr(50).chr(51).chr(52).chr(53).chr(54),
                'action' => chr(117).chr(112).chr(100).chr(97).chr(116).chr(101),
                'license' => $license,
                'cart_type' => $this->_notice['config']['cart_type'],
                'url' => $this->_cart_url,
                'base' => $this->_objectManager->get('Magento\Store\Model\Store')->getBaseUrl(),
                'target_type' => base64_encode('magento2'),
            ),
            false
        );
        if($update_license){}
        return ;
    }
    
    public function addProductTags($tags, $store_id, $product_id) {
    }
    
    /**
     * @param int $link_type 1, 4, 5 is respectively related, up-sell, cross-sell
     */
    
    public function setProductRelation($src_product, $desc_product, $link_type = 1, $both = false) {
        $flag = true;
        if (is_array($desc_product)) {
            $product_mage_id = $src_product;
            $relate_products = $desc_product;
        } elseif (is_array($src_product)) {
            $flag = false;
            $product_mage_id = $desc_product;
            $relate_products = $src_product;
        }
        //$products_links = Mage::getModel('catalog/product_link_api');
        foreach ($relate_products as $product_src_id) {
            if (!$partner_id = $this->getMageIdProduct($product_src_id)) {
                continue;
            }
            if ($flag) {
                $this->insertTable("catalog_product_link", array('product_id' => $this->resolveProductId($product_mage_id), 'linked_product_id' => $partner_id, 'link_type_id' => $link_type));
            }
            if ($both || !$flag) {
                $this->insertTable("catalog_product_link", array('product_id' => $this->resolveProductId($partner_id), 'linked_product_id' => $product_mage_id, 'link_type_id' => $link_type));
            }
        }
    }
    
    public function resolveProductId($product_id) {
        $product_table = $this->getProductMainTable();
        return $this->_process->resolveEntityId($product_id, $product_table);
    }


    public function generateUrlKeyFromUrlKey($url_def) {
        $i = 0;
        if(!$url_def) {
            $url_def = 'empty-url-key';
        }
        $cur_url_key = $url_def;
        while ($this->_checkUrlKeyExisted($cur_url_key)) {
            $i++;
            $cur_url_key = $url_def . '-' . $i;
        }
        return $cur_url_key;
    }
    
    protected function _checkUrlKeyExisted($url_key) {
        $table_url = $this->getTableName('url_rewrite');
        $query = "SELECT * FROM ".$table_url." WHERE request_path IN ('" . $url_key . "','" . $url_key . ".html')";
        try{
            $result = $this->_db->fetchAll($query);
            if (!$result || !is_array($result)) {
                return false;
            } else {
                return count($result);
            }
        }catch (\Exception $e){
            return false;
        }
    }


    protected  function _convertVersionMagento($v, $num) {
        $digits = @explode(".", $v);
        $version = 0;
        if (is_array($digits)) {
            foreach ($digits as $k => $v) {
				if ($v >= 10) {
					$v = 9;
				}
                if($k <= $num){
                    $version += ($v * pow(10, max(0, ($num - $k))));
                }
            }
        }
        return $version;
    }

    protected  function _cookSpecialDate($special_date){
        if($special_date == '0000-00-00 00:00:00' || $special_date == '0000-00-00' || $special_date == '0001-01-01'){
            return '';
        }
        return $special_date;
    }

    /**
     * Create combinations from multi array
     */
    protected  function _combinationFromMultiArray($arrays = array()){
        $result = array();
        $arrays = array_values($arrays);
        $sizeIn = sizeof($arrays);
        $size = $sizeIn > 0 ? 1 : 0;
        foreach ($arrays as $array)
            $size = $size * sizeof($array);
        for ($i = 0; $i < $size; $i ++)
        {
            $result[$i] = array();
            for ($j = 0; $j < $sizeIn; $j ++)
                array_push($result[$i], current($arrays[$j]));
            for ($j = ($sizeIn -1); $j >= 0; $j --)
            {
                if (next($arrays[$j]))
                    break;
                elseif (isset ($arrays[$j]))
                    reset($arrays[$j]);
            }
        }
        return $result;
    }
    
    public function cleanArrayKeys($data) {
        if (!$data || !is_array($data)) return $data;
        foreach ($data as $key => $value) {
            if (is_numeric($key)) unset($data[$key]);
        }
        return $data;
    }
    
    public function subscribeCustomerById($customer_id) {
        return $this->_objectManager->create('Magento\Newsletter\Model\Subscriber')->subscribeCustomerById($customer_id);
    }

    /**
     * TODO: CRON
     */

    public function getAllTaxes()
    {
        return array(
            'result' => 'success',
            'object' => array()
        );
    }

    public function getAllManufacturers()
    {
        return array(
            'result' => 'success',
            'object' => array()
        );
    }

    public function getAllCategories()
    {
        return array(
            'result' => 'success',
            'object' => array()
        );
    }

    public function getAllProducts()
    {
        return array(
            'result' => 'success',
            'object' => array()
        );
    }

    public function getAllCustomers()
    {
        return array(
            'result' => 'success',
            'object' => array()
        );
    }

    public function getAllOrders()
    {
        return array(
            'result' => 'success',
            'object' => array()
        );
    }

    public function getAllReviews()
    {
        return array(
            'result' => 'success',
            'object' => array()
        );
    }
    
    public function selectTableMax($table, $field){
        $table_name = $this->getTableName($table);
        $query = "SELECT max(" . $field . ") FROM " . $table_name;
        try{
            $result = $this->_db->fetchOne($query);
            return $result;
        }catch (\Exception $e){
            if(Custom::DEV_MODE){
                $message = "\LitExtension\CartMigration\Model\Cart::selectTable() error: " . $e->getMessage();
                $this->_logger->error($message);
            }
            return false;
        }
    }
}