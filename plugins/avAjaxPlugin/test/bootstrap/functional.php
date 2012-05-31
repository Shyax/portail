<?php

if (!isset($app))
{
	$app = 'frontend';
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

function avAjaxPlugin_cleanup()
{
	sfToolkit::clearDirectory(dirname(__FILE__).'/../fixtures/project/cache');
	sfToolkit::clearDirectory(dirname(__FILE__).'/../fixtures/project/log');
}
avAjaxPlugin_cleanup();
register_shutdown_function('avAjaxPlugin_cleanup');

require_once dirname(__FILE__).'/../fixtures/project/config/ProjectConfiguration.class.php';
$configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', isset($debug) ? $debug : true);
sfContext::createInstance($configuration);
