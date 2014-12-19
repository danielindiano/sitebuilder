<?php

namespace app\models\items;

require_once 'lib/dom/SimpleHtmlDom.php';
require_once 'lib/utils/Video.php';//TODO use lazyload

use Model;
use Mapper;
use DOMDocument;
use HTMLPurifier;
use HTMLPurifier_Config;
use Filesystem;
use app\models\Items;
use Video;

class Articles extends \app\models\Items
{
	protected $type = 'Article';

	protected $fields = array(
		'title' => array(
			'title' => 'Title',
			'type' => 'string'
		),
		'description' => array(
			'title' => 'Description',
			'type' => 'richtext'
		),
		'author' => array(
			'title' => 'Author',
			'type' => 'string'
		),
		'groups' => array(
			'title' => 'Group',
			'type' => 'groups'
		),
	);

	protected static $blacklist = array(
		'gravatar.com'
	);

	public static function __init()
	{
		parent::__init();

		$self = static::_object();
		$parent = parent::_object();

		$self->_schema = $parent->_schema + array(
			'guid' => array('type' => 'string', 'default' => ''),
			'link' => array('type' => 'string', 'default' => ''),
			'pubdate' => array('type' => 'date', 'default' => 0),
			'description' => array('type' => 'string', 'default' => ''),
			'author' => array('type' => 'string', 'default' => ''),
			'medias' => array('type' => 'array', 'default' => array()),
		);
	}
	//TODO move this to extension
	public static function addToFeed($feed, $item, $extension)
	{
		$images = static::getArticleImages($item);
		$stats = array(
			'total_images' => 0,
			'failed_images' => 0
		);

		//remove captions from description
		$remove = array();
		foreach ($images as $img) {
			if (is_array($img) && $img['alt']) {
				$remove[] = "<p>{$img['alt']}</p>";
			}
		}

		$author = $item->get_author();
		$medias = static::getArticlesMedias($item);
		$article = array(
			'site_id' => $feed->site_id,
			'parent_id' => $feed->id,
			'guid' => static::filterGuid($item->get_id()),
			'link' => $item->get_link(),
			'title' => strip_tags($item->get_title()),
			'description' => static::cleanupHtml($item, $remove, $extension->use_html_purifier),
			'pubdate' => gmdate('Y-m-d H:i:s', $item->get_date('U')),
			'author' => $author ? $author->get_name() : '',
			'format' => 'html',
			'type' => $feed->type,
			'medias' => $medias
		);

		$article = static::create($article);
		$article->save();

		foreach ($images as $image) {
			$imageAlt = '';
			if (is_array($image)) {
				$imageAlt = $image['alt'];
				$image = $image['src'];
			}
			$image = static::getImageUrl($image, $article['guid']);
			$result = Model::load('Images')->download($article, $image, array(
				'url' => $image,
				'title' => $imageAlt,
				'visible' => 1
			));

			if ($result) $stats['total_images'] += 1;
			else $stats['failed_images'] += 1;
		}

		return $stats;
	}

	protected static function filterGuid($guid) {
		$guid = preg_replace('%;jsessionid=[\w\d]+%', '', $guid);

		if(preg_match('%rj\.gov\.br%', $guid)) {
			$guid = preg_replace('%\.lportal.*articleId=%', '?articleId=', $guid);
		}

		return $guid;
	}

	protected static function getPurifier() {
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Cache.SerializerPath', Filesystem::path(APP_ROOT . '/tmp/cache/html_purifier'));
		$config->set('HTML.Allowed', 'b,i,br,p,strong');
		return new HTMLPurifier($config);
	}

	protected static function cleanupHtml($item, $strToRemove = false, $purify = true) {
		$html = $item->get_content();
		if ($purify) {
			$purifier = static::getPurifier();
			$html = $purifier->purify($html);
		}
		$html = mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html));
		$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

		if ($strToRemove) {
			$html = str_replace($strToRemove, '', (string)$html);
		}

		if(!empty($html)) {
			$doc = new DOMDocument();
			$doc->loadHTML($html);
			$body = $doc->getElementsByTagName('body')->item(0);
			$results = '';

			// PRODERJ only
			if(strpos($item->get_id(), 'www.rj.gov.br') !== false) {
				$body->removeChild($body->getElementsByTagName('p')->item(1));
				$body->removeChild($body->getElementsByTagName('p')->item(0));
			}

			foreach($body->childNodes as $node) {
				if($node->nodeType == XML_TEXT_NODE) {
					$content = trim($node->textContent);
					if($content) {
						$new_node = $doc->createElement('p', $content);
						$node = $new_node;
					}
				}
				if($node->nodeType == XML_ELEMENT_NODE) {
					$results .= $doc->saveHTML($node) . PHP_EOL;
				}
			}
		}
		else {
			$results = '';
		}

		return $results;
	}

	protected static function getArticleImages($item) {
		$images = static::getEnclosureImages($item);
		$imagesAreInvalid = empty($images) || (is_array($images) && count($images) == 1 && !$images[0]);

		if($imagesAreInvalid) {
			if ($image = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'image')) {
				$images = (array)$image[0]['data'];
			} else {
				$images = static::getContentImages($item);
			}
		}

		foreach($images as $k => $image) {
			if (is_array($image)) {
				$image = $image['src'];
			}
			if(static::isBlackListed($image)) {
				unset($images[$k]);
			}
		}

		return $images;
	}

	protected static function getArticlesMedias($item) {
			$medias = [];
			foreach($item->get_enclosures() as $enclosure) {
				if ($enclosure->get_link())//stackoverflow.com/questions/4053664/simplepie-includes-phantom-enclosures-that-dont-exist
					$medias[] = [
						'url' => $enclosure->get_link(),
						'type' => $enclosure->get_type(),
						'title' => html_entity_decode($enclosure->get_title(), ENT_QUOTES, 'UTF-8'),
						'length' => $enclosure->get_length(),
						'thumbnails' => $enclosure->get_thumbnails(),
					];
			}
			$medias = array_merge($medias, static::getContentVideos($item));
			//try to generate video thumbs if none is set
			return array_map(function($media) {
				if (!$media['thumbnails'])
					$media['thumbnails'] = Video::getThumbnails($media['url']); //return thumbnails if the url is from a video
				return $media;
			}, $medias);
	}

	protected static function getContentVideos($item) {
		$videos = [];
		$dom = new \DOMDocument('1.0', 'UTF-8');
		@$dom->loadHtml('<?xml encoding="UTF-8">' . $item->get_content());
		$xpath = new \DOMXPath($dom);
		$nodes = $xpath->query('//iframe[contains(@src,"youtube") 
			or contains(@src,"dailymotion") 
			or contains(@src,"canalplus")
			or contains(@src,"gfycat")
			or contains(@src,"vimeo")]');
		if ($nodes->length) {
			foreach ($nodes as $iframe) {
					$videos[] = [
					'url' => $iframe->getAttribute('src'),
					'type' => 'text/html',
					'title' => '',
					'thumbnails' => [],
					'length' => null,
					];	
			}
		}
		return $videos;	
	}

	protected static function getContentImages($item) {
		//$content = str_get_html($item->get_content());
		//$links = $content->find('a[rel*=lightbox]');
		$dom = new \DOMDocument('1.0', 'UTF-8');
		@$dom->loadHtml('<?xml encoding="UTF-8">' . $item->get_content());
		$xpath = new \DOMXPath($dom);
		$images = array();
		$src = array();

		$nodes = $xpath->query('//a[@rel="lightbox"]');
		if ($nodes->length) {
			foreach ($nodes as $img) {
				$src []= $img->getAttribute('src');
				$images []= empty($src)?$img->getAttribute('href'):$src;
			}
			return $images;
		}

		$nodes = $xpath->query('//img[contains(@src, "wp-content/uploads")]|//img[contains(@src, "/photos/")] ');
		if ($nodes->length) {
			foreach ($nodes as $img) {
				$images []= array( 
					'src' => $img->getAttribute('src'),  
					'alt' => $img->getAttribute('alt')
				);
			}
		}

		return $images;
	}

	protected static function getEnclosureImages($item) {
		$images = array();
		$enclosures = $item->get_enclosures();
		if(is_null($enclosures)) return $images;

		foreach($enclosures as $enclosure) {
			$medium = $enclosure->get_medium();
			if(!$medium || $medium == 'image') {
				$images []= $enclosure->get_link();
			}
		}

		return $images;
	}

	protected static function getImageUrl($url, $article) {
		if(Mapper::isRoot($url)) {
			$domain = parse_url($article, PHP_URL_HOST);
			$url = 'http://' . $domain . $url;
		}
		else if(preg_match('%^(http://download.rj.gov.br/imagens/\d+/\d+/\d+.jpg)%', $url, $output)) {
			return $output[0];
		}

		return $url;
	}

	protected static function isBlackListed($link) {
		foreach(static::$blacklist as $i) {
			$pattern = preg_quote($i);
			if(preg_match('%' . $pattern . '%', $link)) {
				return true;
			}
		}

		return false;
	}

}

Articles::applyFilter('remove', function($self, $params, $chain) {
	return Items::updateOrdering($self, $params, $chain);
});

Articles::applyFilter('remove', function($self, $params, $chain) {
	return Items::removeImages($self, $params, $chain);
});

Articles::applyFilter('save', function($self, $params, $chain) {
	$item = $params['entity'];
	if ($item->pubdate) {
		$item->created = $item->pubdate;
	}
	return $chain->next($self, $params, $chain);
});

Articles::applyFilter('save', function($self, $params, $chain) {
	return Items::addTimestamps($self, $params, $chain);
});

Articles::applyFilter('save', function($self, $params, $chain) {
	return Items::addThumbnails($self, $params, $chain);
});

Articles::applyFilter('save', function($self, $params, $chain) {
	return Items::addOrder($self, $params, $chain);
});