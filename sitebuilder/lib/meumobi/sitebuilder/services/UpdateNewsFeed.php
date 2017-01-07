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
use app\models\Items;
use app\models\items\Articles;
use meumobi\sitebuilder\Logger;
use meumobi\sitebuilder\validators\ParamsValidator;

class UpdateNewsFeed
{
	const ARTICLES_TO_KEEP = 50;
	const COMPONENT = 'update_news_feed';

	protected $blacklist = ['gravatar.com'];

	public function perform($params)
	{
		list($category, $extension) = ParamsValidator::validate($params,
			['category', 'extension']);

		$sendPush = $extension->priority == Extensions::PRIORITY_LOW;
		$purifyHtml = $extension->use_html_purifier;

		$feed = $this->fetchFeed($extension->url, $extension);
		$articles = $this->extractArticles($feed, $category, $purifyHtml);

		$bulkImport = new BulkImportItems();
		$stats = $bulkImport->perform([
			'category' => $category,
			'items' => $articles,
			'mode' => $extension->import_mode,
			'sendPush' => $sendPush,
			'shouldUpdate' => function($item) use ($extension) {
				$shouldUpdate = $item->id() && (
					$item->changed('title') ||
					$item->changed('description') ||
					$item->changed('medias') ||
					$item->changed('published')
				);

				if ($shouldUpdate) {
					Logger::debug(self::COMPONENT, 'item will be updated', [
						'item_id' => $item->id(),
						'guid' => $item->guid,
						'category_id' => $extension->category_id,
						'extension_id' => $extension->id(),
					]);
				}

				return $shouldUpdate;
			},
			'shouldCreate' => function($item) use ($extension) {
				$shouldCreate = !$item->id();

				if ($shouldCreate) {
					Logger::debug(self::COMPONENT, 'item will be created', [
						'guid' => $item->guid,
						'category_id' => $extension->category_id,
						'extension_id' => $extension->id(),
					]);
				}

				return $shouldCreate;
			},
		]);

		if ($extension->import_mode == BulkImportItems::INCLUSIVE_IMPORT) {
			$stats['removed_articles'] = $this->removeOldArticles($category);
		}

		$category->updated = date('Y-m-d H:i:s');
		$category->save();

		if ($extension->priority != Extensions::PRIORITY_LOW) {
			$extension->priority = Extensions::PRIORITY_LOW;
			$extension->save(null, ['callbacks' => false]);

			Logger::info(self::COMPONENT, 'extension priority lowered', [
				'extension_id' => $extension->id(),
				'category_id' => $extension->category_id
			]);
		}

		return $stats;
	}

	protected function removeOldArticles($category)
	{
		$conditions = ['parent_id' => $category->id];
		$count = Articles::find('count', ['conditions' => $conditions]);

		$removed = 0;

		if ($count > self::ARTICLES_TO_KEEP) {
			$items = Articles::find('all', [
				'conditions' => $conditions,
				'limit' => $count - self::ARTICLES_TO_KEEP,
				'order' => ['published' => 'ASC']
			]);

			foreach ($items as $item) {
				Items::remove(['_id' => $item->id()]);

				Logger::info(self::COMPONENT, 'item deleted', [
					'item_id' => $item->id(),
					'guid' => $item->guid,
					'site_id' => $item->site_id,
					'category_id' => $item->parent_id,
				]);
			}

			$removed = count($items);
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
				'download_images' => $images,
				'format' => 'html',
			]);

			$mapToUrl = function($a) { return $a['url']; };
			$currentMediaNames = array_map($mapToUrl,
				$article->medias
					? $article->medias->to('array')
					: []
			);
			$newMediaNames = array_map($mapToUrl, $media);

			if (array_diff($currentMediaNames, $newMediaNames) || array_diff($newMediaNames, $currentMediaNames)) {
				unset($article['medias']);
				$article->set(['medias' => $media]);
			}

			return $article;
		}, $items);
	}

	/* fetching */

	protected function fetchFeed($url, $extension)
	{
		$feed = new SimplePie();
		$feed->enable_cache(false);
		$feed->set_feed_url($url);

		// because of videos in the description, removes iframe from the list
		// of tags to be stripped
		$strip_htmltags = $feed->strip_htmltags;
		array_splice($strip_htmltags, array_search('iframe', $strip_htmltags), 1);
		$feed->strip_htmltags($strip_htmltags);

		Logger::info(self::COMPONENT, 'fetching feed', [
			'url' => $url,
			'extension_id' => $extension->id(),
			'category_id' => $extension->category_id,
		]);

		$feed->init();

		Logger::info(self::COMPONENT, 'feed fetched', [
			'url' => $url,
			'extension_id' => $extension->id(),
			'category_id' => $extension->category_id,
		]);

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
		libxml_use_internal_errors(true);
		$doc->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES',
			mb_detect_encoding($html)));
		libxml_use_internal_errors(false);

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
			return $enclosure->get_link() && (
				$enclosure->get_medium() &&
				$enclosure->get_medium() != 'image'
			);
		};

		$map = function($enclosure) {
			return [
				'url' => $enclosure->link,
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
