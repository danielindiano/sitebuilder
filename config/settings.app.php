<?php

Config::write('Sites.blacklist', array('feedback', 'blog', 'restaurant',
    'support', 'dropbox', 'analytics', 'wiki', 'events', 'corporate'));

Config::write('SiteLogos.resizes', array('200x200'));
Config::write('SitePhotos.resizes', array('80x80#'));
Config::write('BusinessItems.resizes', array('80x80#', '30x30#'));

Config::write('Segments', array(
    'restaurant' => array(
        'title' => 'Restaurante',
        'items' => 'products',
        'root' => 'Cardápio'
    ),
    'events' => array(
        'title' => 'Events',
        'items' => 'events',
        'root' => 'Agenda'
    ),
    'corporate' => array(
        'title' => 'Corporate',
        'items' => array('articles', 'events', 'products', 'links'),
        'root' => 'Index'
    )
));

Config::write('Themes.url', 'http://meu-template-engine.meumobi.com/api/index');
