<?php

Config::write('App.encoding', 'utf-8');
Config::write('App.support', 'szNHAQVZXjPDDc8HYQjKMX2q6VjnMxSA');
Config::write('App.environment', trim(Filesystem::read(__DIR__ . '/ENVIRONMENT')));
Config::write('Security.salt', 'a5d5d5be3c69dbc8b49e3342db0f8952f6328abd67076bf65d7c3c67a1fbfcab4946aa0fbd6b506db958449ac5e81637d0f5b8ee88e4d0760909cabe2e78137c');
Config::write('Mailer.transport', 'mail');

Config::write('Sites.blacklist', array());
Config::write('FileExtensions.blacklist', array(
	'mpga', 'mp2', 'mp2a'
));

Config::write('PushWoosh.authToken', 'z8slYDk24hm2SJDIhzi6SBcdFPjCMU870gEH4wJ9WbzcdJsC6RBVl72r7k12b99yoHxZ39VDoOPYNsoLLtRk');
Config::write('OneSignal.authToken', 'Yjk0ZGM0NjctOWE3MC00YTZkLWE2Y2UtMDdhYTVlODczZGE3');

Config::write('Geocode.urls', array(
	'http://maps.googleapis.com',
	'http://elefante.ipanemax.com',
	'http://laguna.ipanemax.com',
	'http://branca.ipanemax.com',
	'http://bonita.ipanemax.com',
));

Config::write('SiteLogos.resizes', array('200x200'));
Config::write('SiteAppleTouchIcon.resizes', array('57x57', '72x72'));
Config::write('SitePhotos.resizes', array('139x139#', '314x220'));
Config::write('BusinessItems.resizes', array('80x60#', '80x80#', '139x139#', '314x220'));

// use LOGLEVEL env var or default
$log_level = getenv('LOGLEVEL');
Config::write('Log.level', $log_level ?: Psr\Log\LogLevel::INFO);

// error reporting should *always* be the maximum
ini_set('error_reporting', -1);
ini_set('display_errors', 'Off');

$dir = new GlobIterator(__DIR__ . '/segments/*.php');
foreach ($dir as $file) {
	if ($file->isFile()) {
		require $file->getPathname();
	}
}

require 'config/environments/' . Config::read('App.environment') . '.php';
