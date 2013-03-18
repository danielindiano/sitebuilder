<?php

set_error_handler(function($no, $str, $file, $line, $context) {
	throw new Exception($str);
}, -1);

ini_set('error_reporting', -1);
ini_set('display_errors', 'On');

Config::write('Mail.preventSending', true);
Config::write('Debug.showErrors', true);

Config::write('Api.ignoreAuth', true);

Config::write('Themes.url', 'http://meu-cloud-db.meumobi.com/configs.json');
Config::write('Themes.ignoreTag', true);
Config::write('SiteManager.url', 'http://meu-site-manager.meumobilesite.com');
Config::write('TemplateEngine.url', 'http://meu-template-engine.meumobi.com');
