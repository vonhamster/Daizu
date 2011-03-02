<?php
require(__DIR__.'/conf.php');
set_include_path('.:' . __DIR__ . '/lib/');
require(__DIR__ . '/lib/shozu/Shozu.php');

$settings = array(
    'db_dsn'                  => DAIZU_DB_DSN,
    'db_user'                 => DAIZU_DB_USER,
    'db_pass'                 => DAIZU_DB_PASS,
    'url_rewriting'           => DAIZU_URL_REWRITING,
    'use_i18n'                => true,
    'project_root'            => __DIR__ . '/',
    'document_root'           => __DIR__ . '/docroot/',
    'debug'                   => DAIZU_DEBUG,
    'benchmark'               => DAIZU_BENCHMARK,
    'init_db'                 => false,
    'redbean_start'           => true,
    'redbean_freeze'          => false,
    'default_application'     => 'cms',
    'default_controller'      => 'index',
    'default_action'          => 'index',
    'error_handler'           => 'cms/index/noroute',
    'enable_default_routing'  => php_sapi_name() == 'cli' ? true : false,
    'registered_applications' => defined('SHOZU_REGISTERED_APPS') ? explode(',', SHOZU_REGISTERED_APPS) : array()
);

if(defined('DAIZU_TEST'))
{
    $settings = array_merge($settings, array(
        'db_dsn' => 'sqlite::memory:'
    ));
}

\shozu\Shozu::getInstance()->handle($settings);
