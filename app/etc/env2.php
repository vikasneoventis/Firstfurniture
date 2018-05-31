<?php
return array (
  'backend' => 
  array (
    'frontName' => 'morale1',
  ),
  'crypt' => 
  array (
    'key' => '79a2300767606973fd71ef00a010f57b',
  ),
  'session' => 
  array (
    'save' => 'memcached',
    'save_path' => '127.0.0.1:11220',
  ),
  'db' => 
  array (
    'table_prefix' => '',
    'connection' => 
    array (
      'default' => 
      array (
        'host' => '127.0.0.1',
        'dbname' => 'livefirs_mage2',
        'username' => 'livefirs_mage2',
        'password' => 'LaptopChasedRimingRood27',
        'active' => '1',
      ),
    ),
  ),
  'resource' => 
  array (
    'default_setup' => 
    array (
      'connection' => 'default',
    ),
  ),
  'x-frame-options' => 'SAMEORIGIN',
  'MAGE_MODE' => 'production',
  'cache_types' => 
  array (
    'config' => 1,
    'layout' => 1,
    'block_html' => 1,
    'collections' => 1,
    'reflection' => 1,
    'db_ddl' => 1,
    'eav' => 0,
    'customer_notification' => 1,
    'full_page' => 0,
    'config_integration' => 1,
    'config_integration_api' => 1,
    'translate' => 1,
    'config_webservice' => 1,
  ),
  'install' => 
  array (
    'date' => 'Wed, 28 Dec 2016 04:57:24 +0000',
  ),
  'cache' => 
  array (
    'frontend' => 
    array (
      'default' => 
      array (
        'backend' => 'Cm_Cache_Backend_Redis',
        'backend_options' => 
        array (
          'server' => '127.0.0.1',
          'database' => '0',
          'port' => '6381',
          'compress_data' => '1',
          'compress_tags' => '1',
          'compress_threshold' => '20480',
          'lifetimelimit' => '57600',
        ),
      ),
      'page_cache' => 
      array (
        'backend' => 'Cm_Cache_Backend_Redis',
        'backend_options' => 
        array (
          'server' => '127.0.0.1',
          'port' => '6382',
          'database' => '0',
          'compress_data' => '1',
          'compress_tags' => '1',
          'compress_threshold' => '20480',
          'lifetimelimit' => '57600',
        ),
      ),
    ),
  ),
);
