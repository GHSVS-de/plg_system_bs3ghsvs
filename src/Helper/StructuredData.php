<?php
defined('_JEXEC') or die;

require_once __DIR__ . '/../vendor/autoload.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\Organization;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

/*JLoader::registerNamespace(
	'Spatie\\SchemaOrg',
	__DIR__ . '/schema-org/src',
	false,
	false,
	'psr4'
);*/

class Bs3ghsvsStructuredData
{
	protected static $loaded = array();
	
	// Array (width, height, img) for 'logosmall' plugin parameter.
	protected static $logosmall;
	protected static $schemaOrganizationBase;
	
	/**
	 * Creates a Schema object for an article and returns it. 
	*/
	public static function sd_article($article)
	{
		$plgParams = PlgSystemBS3Ghsvs::getPluginParams();
		$app = Factory::getApplication();
		
		// Contains logos too.
		$organization = new Registry($plgParams->get('sd_organization'));

		JLoader::register(
			'Bs3ghsvsArticle',
			__DIR__ . '/ArticleHelper.php'
		);

		JLoader::register(
			'Bs3ghsvsItem',
			__DIR__ . '/ItemHelper.php'
		);
		
		### Logo for publisher > Organization.
		// Height 60px.
		if (self::$logosmall['img'] = $organization->get('logosmall', ''))
		{
			// Add 'width' and 'height' indices to array.
			self::$logosmall = array_merge(
				Bs3ghsvsItem::getImageSize(self::$logosmall['img']),
				self::$logosmall
			);
			self::$logosmall['img'] = Bs3ghsvsItem::addUriRoot(self::$logosmall['img']);
		}

		### headline.
		$various  = new Registry(Bs3ghsvsArticle::getVariousData($article->id));
		$headline = $article->title . ($various->get('articlesubtitle', '')
			? ' (' . $various->get('articlesubtitle') . ')' : '');
		
		### articleBody. Used several times for other things even if not used in the end.
		if (!$article->params->get('show_intro'))
		{
			$articleBody = Bs3ghsvsItem::strip_tags($article->introtext . $article->text);
		}
		else
		{
			$articleBody = Bs3ghsvsItem::strip_tags($article->text);
		}
		
		if (!($description = trim($article->metadesc)))
		{
			$description = StringHelper::substr($articleBody, 0, 300);
		}
		
		if (!((int) $article->modified))
		{
			$article->modified = $article->created;
		}

		// Den will Google
		//if (!((int) $item->publish_up))
		{
			$article->publish_up = $article->modified;
		}
		
		### Genre and other stuff like about and keywords.
		$genre = array();

		if (!empty($article->parent_slug))
		{
			$genre[] = $article->parent_title;
		}

		if (!empty($article->catslug))
		{
			$genre[] = $article->category_title;
		}
		
		$tags = array();

		foreach ($article->tags->itemTags as $tag)
		{
			#if (in_array($tag->access, $authorised))
			{
				$tags[] = $tag->title;
			}
		}
		
		$schema = Schema::article()
			->url(Uri::current())
			->mainEntityOfPage(array('@type' => 'WebPage', '@id' => Uri::current()))
			->name($article->title)
			// Google wants 100 chars max.
			->headline(StringHelper::substr($headline, 0, 110))
			->articleSection($article->category_title)
			->author(
				Schema::Person()->name($article->author)
			)
			->publisher(
				Schema::Organization()->name($organization->get('name'))
					->if(self::$logosmall['img'], function(Organization $schema)
					{
						$schema->logo(
							Schema::ImageObject()
							->url(self::$logosmall['img'])
							->contentUrl(self::$logosmall['img'])
							->width(self::$logosmall['width'])
							->height(self::$logosmall['height'])
						);
					})
				)
			->creator($article->author)
			->datePublished(HTMLHelper::_('date', $article->publish_up, 'c'))
			->dateCreated(HTMLHelper::_('date', $article->created, 'c'))
			->dateModified(HTMLHelper::_('date', $article->modified, 'c'))
			->wordCount(
				count(explode(' ', $articleBody))
			)
			->inLanguage(($article->language === '*') ? $app->get('language') : $article->language)
		;

		if($description)
		{
			$schema->description($description);
		};
		
		if($plgParams->get('sd_articleBody'))
		{
			$schema->articleBody($articleBody);
		};

		#### Collect the images - STSRT
		Bs3ghsvsItem::getItemImagesghsvs($article);
		
		$findImageIn = array(
			'image_fulltext',
			'image_intro',
		);
		
		$minWidth = $organization->get('minWidth', 696);
		$imageObjects = array();
		
		foreach ($findImageIn as $key)
		{
			// Passes $image as string (=path)
			if (
				($imageObject = self::buildImageObject($article->Imagesghsvs->get($key, ''), $minWidth))
			){
				$imageObjects[] = $imageObject;
				break;
			}
		}

		// if resizer is enabled we have an array.
		$articletext_imagesghsvs = $article->Imagesghsvs->get('articletext_imagesghsvs');

		if (!empty($articletext_imagesghsvs))
		{
			$articletext_imagesghsvs = ArrayHelper::getColumn(
				(array) $articletext_imagesghsvs,
				'_u'
			);

			foreach ($articletext_imagesghsvs as $image)
			{
				// Passes $image as array ('img-1', width, height)
				if (
					($imageObject = self::buildImageObject($image, $minWidth))
				){
					$imageObjects[] = $imageObject;
				}
			}
		}
		// Puuuh. The hard way.
		elseif ($imagesInArticle = Bs3ghsvsItem::getAllImgSrc($article->text))
		{
			foreach ($imagesInArticle['src'] as $image)
			{
				// Passes $image as string (=path).
				if (
					($imageObject = self::buildImageObject($image, $minWidth))
				){
					$imageObjects[] = $imageObject;
				}
			}
		}

		// Absolutely nothing found => Try fallback image and ignore $minWidth.
		// Passes $image as string (=path)
		if (
			!$imageObjects
			&& ($imageObject = self::buildImageObject($organization->get('fallbackimage', ''), 0))
		){
			$imageObjects[] = $imageObject;
		}
		
		if ($imageObjects)
		{
			$schema->image($imageObjects);
		}
		
		if ($genre)
		{
			$schema->genre($genre);
		}
		
		if ($tags)
		{
			$schema->keywords($tags);
			$schema->about($tags);
		}
		#### Collect the images - END
		
		return $schema;
	}

	/**
	 * Creates a Schema object for an Organization and returns it. 
	*/
	public static function sd_organization(bool $onlyBase = false)
	{
		$plgParams = PlgSystemBS3Ghsvs::getPluginParams();

		// Contains logos too.
		$organization = new Registry($plgParams->get('sd_organization'));
		
		$schema = Schema::Organization()
		->url(Uri::root())
		->name($organization->get('name'))
		->logo(Bs3ghsvsItem::addUriRoot($organization->get('logo')));
		
		if ($onlyBase === true)
		{
			return $schema;
		}
		
		$schema->email($organization->get('email'))
		->telephone($organization->get('telephone'))
		->faxNumber($organization->get('faxNumber'))
		->foundingDate($organization->get('foundingDate'))
		->description($organization->get('description'))
		->foundingLocation($organization->get('foundingLocation'))
		->founder(Schema::Person()->name($organization->get('founder')))
		;
		
		return $schema;
	}

	/**
	 * Creates a Schema object for an Organization and returns it. 
	*/
	public static function sd_contactPoint(object $contact)
	{
		$schema = self::sd_organization();
		
		// Cloaked Email?
		if (!MailHelper::isEmailAddress($contact->email_to))
		{
			$db = Factory::getDbo();
			
			$query = $db->getQuery(true)
			->select($db->qn('email_to'))
			->from($db->qn('#__contact_details'))
			->where($db->qn('id') .'='. (int) $contact->id)
			;
			$db->setQuery($query);
			$email = $db->loadResult();
		}
		else
		{
			$email = $item->email_to;
		}
		
		$schema->contactPoint(
			Schema::contactPoint()
			->contactType('customer service')
			->telephone($contact->telephone)
			->email($email)
			->faxNumber($contact->fax)
			->availableLanguage(['German','English'])
			->areaServed('Worldwide')
		);
		return $schema;
	}

	/**
	 * Creates a Schema object for an BreadcrumbList and returns it. 
	*/
	public static function sd_breadcrumbList($app, $article = null)
	{
		$pathway = $app->getPathway();
		$items = $pathway->getPathWay();
		$lang = $app->getLanguage();
		$menu = $app->getMenu();

		if (Multilanguage::isEnabled())
		{
			$home = $menu->getDefault($lang->getTag());
		}
		else
		{
			$home  = $menu->getDefault();
		}

		$crumbs = array();
		$count = count($items);

		for ($i = 0; $i < $count; $i++)
		{
			if (!trim($items[$i]->link)) continue;
			
			$uri = Uri::getInstance($items[$i]->link);
			$option = $uri->getVar('option');
			
			if (($Itemid = (int) $uri->getVar('Itemid')) && $item = $menu->getItem($Itemid))
			{
				$params = new Registry($item->params);
				
				if (!(
					($name = trim($params->get('page_title')))
					|| ($name = trim($params->get('page_heading')))
					)
				){
					$name = stripslashes(htmlspecialchars($items[$i]->name, ENT_COMPAT, 'UTF-8'));
				}
			}

			$crumbs[$i] = new \stdClass;
			$crumbs[$i]->name = $name;
			##$crumbs[$i]->link = Bs3ghsvsItem::addUriRoot(Route::_($items[$i]->link));
			$crumbs[$i]->link = Route::_($items[$i]->link);
			// Scheiß Krücken für schrottige Pathways.
			if ($app->get('sef'))
			{
				$uri = Uri::getInstance($crumbs[$i]->link);
				$uri->delVar('layout');

				if ($option === 'com_osmap')
				{
					$uri->delVar('view');
					$uri->delVar('id');
				}
				$crumbs[$i]->link = $uri->toString();
			}
		}
		
		// Add first (Home link).
		$item = new \stdClass;
		$item->name = Text::_('PLG_SYSTEM_BS3GHSVS_BREADCRUMBS_HOMETEXT');
		#$item->link = Bs3ghsvsItem::addUriRoot(Route::_('index.php?Itemid=' . $home->id));
		$item->link = Route::_('index.php?Itemid=' . $home->id);
		array_unshift($crumbs, $item);

		// Get rid of duplicated entries on trail including home page when using multilanguage
		for ($i = 0; $i < count($crumbs); $i++)
		{
			if ($i === 1 && !empty($crumbs[$i]->link) && !empty($crumbs[$i - 1]->link) && $crumbs[$i]->link === $crumbs[$i - 1]->link)
			{
				unset($crumbs[$i]);
			}
		}
		
		if (
			is_object($article)
			&& $menu
			#&& isset($article->query['view'])
			#&& $article->query['view'] === 'category'
		){
			$active = $menu->getActive();

			if (
				isset($active->query['view'])
				&& $active->query['view'] === 'category'
				&& ($count = count($crumbs)) > 1
				&& $crumbs[$count - 1]->link !== $article->readmore_link
			){
				$crumbs[$count] = new stdClass;
				$crumbs[$count]->name = $article->title;
				$crumbs[$count]->link = $article->readmore_link;
			}
		}

		$itemListElements = array();
		$i = 0;

		foreach ($crumbs as $crumb)
		{
			$itemListElements[] = Schema::ListItem()->position(++$i)->item(
				['@id' => Bs3ghsvsItem::addUriRoot($crumb->link), 'name' => $crumb->name]
			);
		}
		return Schema::BreadcrumbList()->itemListElement($itemListElements);
	}

	/**
	 * $image: String (path) or Array ['img'=>...,'width'=>...,'height'=>...]
	*/
	protected static function buildImageObject($image, int $minSize = 0)
	{
		if (!$image)
		{
			return false;
		}

		if (is_string($image))
		{
			$size = Bs3ghsvsItem::getImageSize($image);
		}
		else
		{
			$size['width'] = $image['width'];
			$size['height'] = $image['height'];
			$image = $image['img-1'];
		}
		
		if ($size['width'] < $minSize)
		{
			return false;
		}
		
		$image = Bs3ghsvsItem::addUriRoot($image);
		
		return Schema::ImageObject()
			->url($image)
			->contentUrl($image)
			->width($size['width'])
			->height($size['height']);
	}
	
	public static function buildScriptTag(object $schema, int $prettyPrint = 0) : string
	{
		if ($prettyPrint === 0)
		{
			return $schema->toScript();
		}
		
		// $schema->toArray() adds an additional empty property "toArray" to the $schema. Thus:
		return '<script type="application/ld+json">' . json_encode($schema, $prettyPrint | JSON_UNESCAPED_UNICODE) . '</script>';
	}
}
