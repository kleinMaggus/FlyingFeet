<?php

$container
		->loadFromExtension('framework',
				array('secret' => 's3cr3t', 'form' => null, 'csrf_protection' => array('enabled' => true, 'field_name' => '_csrf',), 'esi' => array('enabled' => true,), 'profiler' => array('only_exceptions' => true,),
						'router' => array('resource' => '%kernel.root_dir%/config/routing.xml', 'type' => 'xml',),
						'session' => array('auto_start' => true, 'default_locale' => 'fr', 'storage_id' => 'session.storage.native', 'name' => '_SYMFONY', 'lifetime' => 86400, 'path' => '/', 'domain' => 'example.com', 'secure' => true, 'httponly' => true,),
						'templating' => array('assets_version' => 'SomeVersionScheme', 'assets_base_urls' => 'http://cdn.example.com', 'cache' => '/path/to/cache', 'engines' => array('php', 'twig'), 'loader' => array('loader.foo', 'loader.bar'),
								'packages' => array('images' => array('version' => '1.0.0', 'base_urls' => array('http://images1.example.com', 'http://images2.example.com'),), 'foo' => array('version' => '1.0.0',),
										'bar' => array('base_urls' => array('http://bar1.example.com', 'http://bar2.example.com'),),), 'form' => array('resources' => array('theme1', 'theme2')),), 'translator' => array('enabled' => true, 'fallback' => 'fr',),
						'validation' => array('enabled' => true, 'cache' => 'apc',), 'annotations' => array('cache' => 'file', 'debug' => true, 'file_cache_dir' => '%kernel.cache_dir%/annotations',), 'ide' => 'file%%link%%format'));
