<?php

namespace meumobi\sitebuilder\services;

require_once 'lib/dom/SimpleHtmlDom.php';
require_once 'lib/simplepie/SimplePie.php';
require_once 'lib/utils/Video.php';

use DOMDocument;
use DOMXPath;
use Exception;
use Filesystem;
use HTMLPurifier;
use HTMLPurifier_Config;
use Mapper;
use SimplePie;
use Video;
use app\models\Extensions;
use app\models\items\Articles;
use meumobi\sitebuilder\Logger;
use meumobi\sitebuilder\validators\ParamsValidator;

class UpdateNewsFeed
{
	const ARTICLES_TO_KEEP = 50;

	protected $blacklist = ['gravatar.com'];

	public function perform($params)
	{
		list($category, $extension) = ParamsValidator::validate($params,
			['category', 'extension']);

		$sendPush = $extension->priority == Extensions::PRIORITY_LOW;
		$purifyHtml = $extension->use_html_purifier;

		$feed = $this->fetchFeed($extension->url);
		$articles = $this->extractArticles($feed, $category, $purifyHtml);

		$bulkImport = new BulkImportItems();
		$stats = $bulkImport->perform([
			'category' => $category,
			'items' => $articles,
			'mode' => $extension->import_mode,
			'sendPush' => $sendPush,
			'shouldUpdate' => function($item) {
				return $item->changed('title') || $item->changed('description');
			},
			'shouldCreate' => function($item) {
				return !$item->id();
			},
		]);

		$stats['removed_articles'] = $this->removeOldArticles($category);

		$category->updated = date('Y-m-d H:i:s');
		$category->save();

		$extension->priority = Extensions::PRIORITY_LOW;
		$extension->save(null, ['callbacks' => false]);

		Logger::info('extensions', 'extension priority lowered', [
			'extension_id' => (string) $extension->_id,
			'category_id' => $extension->category_id
		]);

		return $stats;
	}

	protected function removeOldArticles($category)
	{
		$conditions = ['parent_id' => $category->id];
		$count = Articles::find('count', ['conditions' => $conditions]);

		$removed = 0;

		if ($count > self::ARTICLES_TO_KEEP) {
			$ids = array_keys(Articles::find('list', [
				'conditions' => $conditions,
				'limit' => $count - self::ARTICLES_TO_KEEP,
				'order' => ['published' => 'ASC']
			]));

			if ($ids) {
				Articles::remove(['_id' => $ids]);
				$removed = count($ids);
			}
		}

		return $removed;
	}

	protected function extractArticles($feed, $category, $purify)
	{
		// gets last n items, most recent last
		$items = array_slice(array_reverse($feed->get_items()), -self::ARTICLES_TO_KEEP);

		return array_map(function($item) use ($purify, $category) {
			$article = Articles::find('first', [
				'conditions' => [
					'parent_id' => $category->id,
					'guid' => $item->get_id(),
				],
			]);

			$article = $article ?: Articles::create();

			$content = $item->get_content();
			$domDoc = $this->buildDOMDoc($content);

			list($images, $media) = $this->extractMedia($item, $domDoc);

			$article->set([
				'type' => 'articles',
				'site_id' => $category->site_id,
				'parent_id' => $category->id,
				'guid' => $item->get_id(),
				'link' => $item->get_link(),
				'title' => strip_tags($item->get_title()),
				'published' => gmdate('Y-m-d H:i:s',
					$item->get_date('U') ?: date('U')),
				'author' => ($author = $item->get_author())
					? $author->get_name()
					: '',
				'description' => $this->extractDescription($content, $purify),
				'medias' => $media,
				'download_images' => $images,
				'format' => 'html',
			]);

			return $article;
		}, $items);
	}

	/* fetching */

	protected function fetchFeed($url)
	{
		$feed = new SimplePie();
		$feed->enable_cache(false);
		$feed->set_feed_url($url);

		// because of videos in the description, removes iframe from the list
		// of tags to be stripped
		$strip_htmltags = $feed->strip_htmltags;
		array_splice($strip_htmltags, array_search('iframe', $strip_htmltags), 1);
		$feed->strip_htmltags($strip_htmltags);

		$feed->init();

		if ($error = $feed->error()) {
			throw new Exception($error);
		}

		return $feed;
	}

	/* document mangling */

	protected function extractDescription($html, $purify)
	{
		if ($purify) {
			$html = $this->purifyHtml($html);
		}

		return $html;
	}

	protected function purifyHtml($html)
	{
		$path = Filesystem::path(APP_ROOT . '/tmp/cache/html_purifier');

		$config = HTMLPurifier_Config::createDefault();
		$config->set('Cache.SerializerPath', $path);
		$config->set('HTML.Allowed', 'b,i,br,p,strong');

		$purifier = new HTMLPurifier($config);

		return $purifier->purify($html);
	}

	protected function buildDOMDoc($html)
	{
		$html = $html ?: '<html></html>';
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES',
			mb_detect_encoding($html)));
		return $doc;
	}

	/* enclosures */

	protected function isBlackListed($url)
	{
		foreach ($this->blacklist as $i) {
			$pattern = preg_quote($i);

			if (preg_match('%' . $pattern . '%', $url)) {
				return true;
			}
		}

		return false;
	}

	protected function extractMedia($article, $domDoc)
	{
		$xpath = new DOMXPath($domDoc);

		$media = $this->extractMediaFromEnclosure($article, $xpath);
		$images = $this->extractImages($article, $xpath);

		return [$images, $media];
	}

	protected function extractDescriptionImages($xpath, $article)
	{
		$domain = parse_url($article->get_link(), PHP_URL_HOST);

		$expression = '//img[contains(@src, "wp-content/uploads")
			or contains(@src, "/photos/")]';

		return $this->extractFromDescription($xpath, $expression, function($img) use ($domain) {
			$url = $img->getAttribute('src');

			if (Mapper::isRoot($url)) {
				$url = 'http://' . $domain . $url;
			}

			return [
				'url' => $url,
				'title' => $img->getAttribute('alt'),
				'visible' => 1,
			];
		});
	}

	protected function extractDescriptionVideos($xpath)
	{
		$expression = '//iframe[contains(@src, "youtube")
			or contains(@src, "dailymotion")
			or contains(@src, "canalplus")
			or contains(@src, "gfycat")
			or contains(@src, "vimeo")]';

		return $this->extractFromDescription($xpath, $expression, function($iframe) {
			return [
				'url' => $iframe->getAttribute('src'),
				'type' => 'text/html',
				'title' => '',
				'thumbnails' => Video::getThumbnails($iframe->getAttribute('src')),
				'length' => null,
			];
		});
	}

	protected function extractFromDescription($xpath, $expression, $callback)
	{
		$elements = $xpath->query($expression);

		return array_map($callback, iterator_to_array($elements));
	}

	protected function extractMediaFromEnclosure($article, $xpath)
	{
		$filter = function($enclosure) {
			return $enclosure->get_link() && $enclosure->get_medium() != 'image';
		};

		$map = function($enclosure) {
			return [
				'url' => $enclosure->get_link(),
				'type' => $enclosure->get_type(),
				'title' => $enclosure->get_title(),
				'length' => $enclosure->get_length(),
				'thumbnails' => $enclosure->get_thumbnails()
					?: Video::getThumbnails($enclosure->get_link()),
			];
		};

		$media = $this->extractFromEnclosures($article->get_enclosures(), $filter, $map);

		return array_merge($media, $this->extractDescriptionVideos($xpath));
	}

	protected function extractImages($article, $xpath)
	{
		$filter = function($enclosure) {
			return $enclosure->get_link() && (
				!$enclosure->get_medium() ||
				$enclosure->get_medium() == 'image'
			);
		};

		$map = function($enclosure) {
			return [
				'url' => $enclosure->get_link(),
				'title' => $enclosure->get_title(),
				'visible' => 1
			];
		};

		// only use description images if there is no feed image available
		$images = $this->extractFromEnclosures($article->get_enclosures(), $filter, $map)
			?: $this->extractDescriptionImages($xpath, $article);

		return array_filter($images, function($image) {
			return !$this->isBlackListed($image['url']);
		});
	}

	protected function extractFromEnclosures($enclosures, $filter, $map)
	{
		return array_map($map, array_filter($enclosures, $filter));
	}
}
