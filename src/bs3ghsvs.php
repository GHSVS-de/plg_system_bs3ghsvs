<?php
/**
2015-08-30: GHSVS
*/
\defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Utility\Utility;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Uri\Uri;
use Spatie\SchemaOrg\Schema;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

JLoader::register('Bs3ghsvsTemplate', __DIR__ . '/Helper/TemplateHelper.php');
JLoader::register('Bs3GhsvsFormHelper', __DIR__ . '/Helper/FormHelper.php');
JLoader::register('Bs3ghsvsItem', __DIR__ . '/Helper/ItemHelper.php');

class PlgSystemBS3Ghsvs extends CMSPlugin
{
	protected $app;
	protected $db;
 
	// Da ich language "missbrauche" für andere Komponenten, Template etc.
	protected $autoloadLanguage = true;

	/**
	 * Array von Template-Namen, die html/plgSystemBs3GhsvsActive.json enthalten.
	 * Bspw. zum Ausschluss von HTMLHelper-Registreirung in nicht relevanten Templates.
	 */
	protected $templates = array();
	
	// A switch for some features that shall only run in $this->templates templates.
	private $executeFe = false;

	protected static $loaded = array();
 
	// Path inside /media/
	protected static $basepath = 'plg_system_bs3ghsvs';
 
	// Aktuelles FE-Template
	protected $template = null;
	
	// A shortcut for $this->app->getTemplate(true)->params;
	protected $templateParams = null;
	
	protected $formPath = null;
	
	/**
	 * Optionen aus plgSystemBs3Ghsvs.json des Templates.
	 * 
	 * Null | RegistryObject
	 */
	public static $options = null;

	public static $log = 0;
	
	// for public static getter function. Via PlgSystemBS3Ghsvs::getPluginParams.
	protected static $plgParams;
	
	// Collect og images for later output.
	protected $ogCollection = array();
	
	protected $sd_robotsStateOk = false;

	function __construct(&$subject, $config = array())
	{
		// NEIN!!!!!!!!!!!!!!!! Das darfst nicht in __construct!!!!
		#if (Factory::getDocument()->getType() !== 'html')

		parent::__construct($subject, $config);
		
		// For getter method from outside.
		static::$plgParams = $this->params;

		if (self::$log || (self::$log = $this->params->get('log', 0)))
		{
			Log::addLogger(
				array('text_file' => self::$basepath . '-log.php'), Log::ALL, array('bs3ghsvs')
			);
		}
		
		// Open graph feature can be used in any template.
		// Create arrays early to avoid PHP Notices later on.
		if ($this->params->get('opengraphActive') === 1 && $this->app->isClient('site'))
		{
			$this->ogCollection['com_content.article'] = array();
			$this->ogCollection['mod_custom.content'] = array();
			$this->ogCollection['default_images'] = array();
		}

		// Order this plugin as first running one in #__extensions
		$this->order();

		// Check for file plgSystemBs3Ghsvs.json in templatefolders.
		$this->templates = Bs3ghsvsTemplate::getActiveInTemplates();
		
		$this->formPath = JPATH_PLUGINS . '/system/bs3ghsvs/myforms/';

		HTMLHelper::addIncludePath(__DIR__ . '/html');

		if (!$this->templates || !$this->app->isClient('site'))
		{
			$this->executeFe = false;
			return;
		}

		$this->sd_robotsStateOk = $this->params->get('sd_robots', 1) === 0
			|| ($this->params->get('sd_robots', 1) === 1 && $this->app->client->robot);

		### VORSICHT BEI DEM GANZEN. Das Plugin plg_system_lessghsvs lädt ggf. lessc ebenfalls.
		### Darf also nicht verwundern, wenn class_exists hier ein true zurückliefert!
		### Je nach Reihenfolge der Plugins.
		
		// Lade Wunsch-lessc bevor Joomla ungeeignete Version laden kann!
		if (class_exists('lessc', $autoload = false))
		{
			if (self::$log)
			{
				$add = __METHOD__ . ': Class lessc is already loaded. Skipped loading.';
				Log::add($add, Log::INFO, 'bs3ghsvs');
			}
		}
		else if ($this->params->get('loadLessClass', 0) === 1)
		{
			$lessPluginParams = Bs3ghsvsTemplate::getLessPluginParams();

			if ($lessPluginParams->get('lesscPathAbs'))
			{
				require_once $lessPluginParams->get('lesscPathAbs');

				if (self::$log)
				{
					$add = __METHOD__ . ': Loaded class lessc. File: '
						. str_replace(JPATH_SITE, 'JROOT', $lessPluginParams->get('lesscPathAbs')) ;
					Log::add($add, Log::INFO, 'bs3ghsvs');
				}
			}
		}
		else
		{
			if (self::$log)
			{
				$add = __METHOD__ . ': lessc not loaded. Probably deactivated in plugin bs3ghsvs.';
				Log::add($add, Log::INFO, 'bs3ghsvs');
			}
		}
	}
	
	public function onAfterRoute()
	{
		if (!$this->app->isClient('site'))
		{
			$this->executeFe = false;
			return;
		}
		
		// GZIP off for Facebook and LinkedIn bots.
		if (
			$this->params->get('gzipOffFacebook', 1) === 1
			&& $this->app->get('gzip', 0)
			&& preg_match('/facebookexternalHit|LinkedInBot/i', $this->app->client->userAgent)
		){
			$this->app->set('gzip', 0);
		}

		// Vorschaltseite START.
		$vorschaltseite = (int) $this->params->get('vorschaltseite', '');

		if ($vorschaltseite && $vorschaltseite !== 1)
		{
			$isVorschaltseite = ((int) $this->app->input->get('Itemid') === $vorschaltseite);
			$isRobot = (int) $this->app->client->robot;

			if ($isVorschaltseite && $isRobot)
			{
				$this->app->redirect(Uri::root());
				return;
			}
			
			// Force Vorschaltseite OFF by query parameter &vsoff=1. 
			$vsoff = (1 === (int) Uri::getInstance()->getVar('vsoff'));

			$node = 'VorschaltseiteOff';

			// Weiß nicht, ob Sinn macht. Idee ist, wenigstens für eine Session Ruhe.
			$session = Factory::getSession();
			$sessionData = (int) $session->get($node);
			$cookie = $this->app->input->cookie;
			$cookieData = (int) $cookie->get($node);

			// Write Session and Cookie always
			$session->set($node, 1);

			// 14 days 60 * 60 * 24 * 14
			$cookie_time = 60 * 60 * 24 * 365;

			$cookie->set($node,
				1,
				time() + $cookie_time
				,
				// Wichtig! Damit Cookie sowohl unter /de/ als auch /en/ verfügbar.
				'/',
				'',
				false,
				true
			);
			
			$redirectCase = (
				$vsoff === false
				&& !(
					$cookieData === 1
					||
					$sessionData === 1
				)
			);

			if (
				$redirectCase
				&& !$isRobot
				&& Bs3ghsvsTemplate::getIsFrontpage() === true
			){
				$this->app->redirect(Route::_('index.php?Itemid=' . $vorschaltseite));
				return;
			}
		}
		// Vorschaltseite END.

		$this->template = Bs3ghsvsTemplate::getTemplateNameEarly($this->app, $this->db);
		$this->executeFe = in_array($this->template, $this->templates);		

		if ($this->executeFe === false)
		{
			return;
		}

		self::$options = Bs3ghsvsTemplate::getTemplateOptionsFromJson($this->template);

		$this->executeFe = $this->register();
	}

	public function onAfterDispatch()
	{
		// Hint for user in back-end that image resizer doesn't cache.
		if (
			$this->app->isClient('administrator')
			&& ($this->params->get('resizeForce') + $this->params->get('resizeForceMessage') === 2)
		){
			$this->app->enqueueMessage(Text::_('PLG_SYSTEM_BS3GHSVS_FORCE_MESSAGE'), 'info');
		}
		
		if ($this->executeFe === true || $this->params->get('initTemplateAlways', 0) === 1)
		{
			Bs3ghsvsTemplate::initTemplate();

			if ($this->params->get('loadBootstrapEarly', 1))
			{
				// Load BS.
				HTMLHelper::_('bootstrap.framework');
			}
		}
	}

	public function onContentBeforeSave($context, $article, $isNew, $data = array())
	{
		// articleconnect is deprecated as far as I remember.
		// A: depends on articleconnect plugin. Following code seems to be just for clean out.
		if (
			$context === 'com_content.article'
			&& !empty($article->attribs)
			&& is_string($article->attribs)
		){
			$attribs = json_decode($article->attribs);
			$registry = new Registry($attribs);

			if (is_object($registry->get('articleconnect')))
			{
				foreach ($attribs->articleconnect as $key => $value)
				{
					if (!trim($value))
					{
						unset($attribs->articleconnect->$key);
					}
				}
			}

			$article->attribs = json_encode($attribs);
		}
	}
	
	/**
	 * Save/renew bs3ghsvs extra fields in database table #__bs3ghsvs_article.
	 */
	public function onContentAfterSave($context, $article, $isNew, $data = array())
	{
		if ($context === 'com_content.article')
		{
			// Deletion protection e.g. when j2xml is importing.
			// More j2xml specific exclusions:
			// Factory::getDocument()->getType() === 'xmlrpc'
			// $this->app->input->get('option') === 'com_j2xml'
			// $this->app->input->get('task') === 'services.import'
			// $this->app->input->get('format') === 'xmlrpc'
			if (Factory::getDocument()->getType() !== 'html')
			{
				return true;
			}

			$articleId = isset($article->id) ? (int) $article->id : 0;

			if (!$articleId)
			{
				return true;
			}
			
			$prefix = 'article';
			$activeXml = Bs3GhsvsFormHelper::getActiveXml($prefix, $this->params, array(1));
			
			// Delete all old rows in db table.
			$query = $this->db->getQuery(true)
				->delete($this->db->qn('#__bs3ghsvs_article'))
				->where($this->db->qn('article_id') . ' = ' . $articleId);
			$this->db->setQuery($query);
			$this->db->execute();
			
			// Get the relevant values for column "key" in db table.
			foreach ($activeXml as $key => $status)
			{
				$activeXml[$key] = strtolower(substr_replace($key, '', 0, strlen($prefix)));
			}

			$prefix = 'bs3ghsvs_';
			$tuples = array();

			foreach ($activeXml as $column)
			{
				$dataKey = $prefix . $column;

				if (isset($data[$dataKey]))
				{
					// e.g. field "bs3ghsvs_extension_active".
					$activeKey = $dataKey . '_active';

					if (isset($data[$dataKey][$activeKey]) && $data[$dataKey][$activeKey] === 0)
					{
						continue;
					}

					$values = json_encode($data[$dataKey]);
					// Because filter="url" leads to (bool) false instead of empty string:
					// Shouldn't be a problem.
					##$values = str_replace(array(':false,', ':false}'), array(':"",', ':""}'), $values);

					$tuples[] = $articleId
						. ', ' . $this->db->q($column)
						. ', ' . $this->db->q($values);
				}
			}

			if ($tuples)
			{
				$query = $this->db->getQuery(true)
					->insert($this->db->quoteName('#__bs3ghsvs_article'))
					->columns($this->db->quoteName(array('article_id', 'key', 'value')))
					->values($tuples);
				$this->db->setQuery($query);
				$this->db->execute();
			}
		}
		return true;		
	}

	/**
	 * .
	 */
	public function onContentAfterDelete($context, $article)
	{
		if ($context === 'com_content.article')
		{
			$query = $this->db->getQuery(true)
				->delete($this->db->qn('#__bs3ghsvs_article'))
				->where($this->db->qn('article_id') . ' = ' . (int) $article->id);
			$this->db->setQuery($query);
			$this->db->execute();
		}
		return true;
	}

	/**
	 * Load form field values from db table.
	 */
	public function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if ($context === 'com_content.article')
		{
			$prefix = 'article';

			if (is_object($data))
			{
				$articleId = isset($data->id) ? (int) $data->id : 0;
				
				if (!$articleId)
				{
					return true;
				}
				
				$activeXml = Bs3GhsvsFormHelper::getActiveXml($prefix, $this->params, array(1));
				
				if (!$activeXml)
				{
					return true;
				}
				
				foreach ($activeXml as $key => $status)
				{
					// Get the key name for db request.
					$activeXml[$key] = strtolower(substr_replace($key, '', 0, strlen($prefix)));
				}

				// Load the article extra fields from the database.
				$query = $this->db->getQuery(true);
				$query->select($this->db->qn(array('key', 'value')))
				->from($this->db->qn('#__bs3ghsvs_article'))
				->where($this->db->qn('article_id') . ' = ' . $articleId)
				->where($this->db->qn('key') . ' IN('
					. implode(', ', $this->db->q($activeXml)) . ')')
				;
				$this->db->setQuery($query);
				$result = $this->db->loadObjectList('key');
				
				if (!$result)
				{
					return true;
				}
				
				foreach ($result as $key => $value)
				{
					$result[$key] = json_decode($value->value, true);
					
					if (json_last_error() !== JSON_ERROR_NONE)
					{
						$result[$key] = $value->value;
					}
					$data->{'bs3ghsvs_' . $key} = $result[$key];
				}
			} // is_object($data)
		} // end $context === 'com_content.article'
		return true;
	}

	public function onContentPrepareForm(Form $form, $data)
	{
		$context = $form->getName();

		// Template styles.
		if (
			$this->app->isClient('administrator')
			&& $context === 'com_templates.style'
			&& ($template = Bs3ghsvsTemplate::getTemplateByStyleId($this->app->input->get('id')))
			&& in_array($template, $this->templates)
		){
			$form->loadFile($this->formPath . '/base.xml', $reset = false, $path = false);
			$form->loadFile($this->formPath . '/template.xml', $reset = false, $path = false);
		}	

		// Module edit in backend UND in Frontend.
		if (
			($this->app->isClient('administrator') && $context === 'com_modules.module')
			// Load form:
			|| ($this->app->isClient('site') && $context === 'com_config.modules')
			// Save form (how stupid is that?):
			|| ($this->app->isClient('site') && $context === 'com_modules.module')
		){
			$key = 'Module';

			foreach (Bs3GhsvsFormHelper::getActiveXml($key, $this->params) as $file => $x)
			{
				$form->loadFile($this->formPath . '/' . $file . '.xml', $reset = false, $path = false);
			}
		}

		// Article edit in backend.
		if (
			$this->app->isClient('administrator')
			&& $context === 'com_content.article'
		){
			foreach (Bs3GhsvsFormHelper::getActiveXml('Article', $this->params) as $file => $x)
			{
				$form->loadFile($this->formPath . '/' . $file . '.xml', $reset = false, $path = false);
			}
		}

		// Menuitem edit in backend.
		if (
			$this->app->isClient('administrator')
			&& $context === 'com_menus.item'
		){
			$key = 'Menuitem';

			foreach (Bs3GhsvsFormHelper::getActiveXml($key, $this->params) as $file => $x)
			{
				$form->loadFile($this->formPath . '/' . $file . '.xml', $reset = false, $path = false);
			}
		}

		// Contact form frontend
		if (
			$this->app->isClient('site')
			&& $this->app->input->get('view', '') === 'contact'
			&& $this->app->input->get('option', '') === 'com_contact'
			&& $context === 'com_contact.contact'
		){
			$key = 'ContactForm';

			foreach (Bs3GhsvsFormHelper::getActiveXml($key, $this->params) as $file => $x)
			{
				$form->loadFile($this->formPath . '/' . $file . '.xml', $reset = false, $path = false);
				
				// Remove "* Required field" / "* Benötigtes Feld"
				$form->removeField('spacer');
				
				$paras = new Registry($this->params->get('XmlActive' . $key));
				$setRequired = array('contact_phoneghsvs', 'contact_name');
				
				foreach ($setRequired as $field)
				{
					$form->setFieldAttribute($field, 'required',
						$paras->get($field . '_required', 0) ? 'true' : 'false');
				}
			}
			#
		}
		return true;
	}
	
	/**
	 * called when after a module is rendered on the back- or front-end.
	*/
	public function onAfterRenderModule(&$module, $attribs)
	{
		// Add frontend module editing button if activated.
		if ($this->app->isClient('site'))
		{
			if (
				Bs3GhsvsFormHelper::getActiveXml('Module', $this->params, array(1))
				
				// Global switch:
				&& $this->params->get('frontendEditingOn', 0) === 1
			
				// Check setting of myforms/module.xml:
				&& strpos($module->params, '"frontendEditingOn":1') !==false

				// Check the user rights before we do regex shit:
				&& Factory::getUser()->authorise(
					'module.edit.frontend', 'com_modules.module.' . $module->id
				)

				&& trim($module->content)
			){
				$editBtn = LayoutHelper::render('ghsvs.frontediting_modules_in_article',
					array('module' => $module)
				);
				
				// Find first HTML tag. Normally a <div>.
				$muster = '/^(\s*<(?:div|span|nav|ul|ol|h\d|section|aside|nav|address|article) [^>]*>)/m';
				
				// Add editing link directly after the first HTML tag.
				$module->content = preg_replace($muster, '\\0' . $editBtn, $module->content, 1);
			}
		}
	}

	/**
	 * Called after the list of modules that must be rendered is created.
	 *
	 * 1) Add a module marker 'iAmAModuleGhsvs' to exclude modules like mod_articles_news
	 *  from running through onContentPrepare with context com_content.article.
	 * 2) Make parameters in 'bs3ghsvsModule' directly available in module's $params.
	 */
	public function onAfterModuleList(&$modules)
	{
		if (!$this->app->isClient('site'))
		{
			return;
		}

		foreach ($modules as $module)
		{
			$registry = new Registry($module->params);
			$registry->set('iAmAModuleGhsvs', 1);

			###### Bootstrap-Size-Parameters and others from bs3ghsvsModule.xml - START
			$colClass = array();
			$freeColClasses = '';

			// Is module.xml active?
			$prefix = 'module';
			$activeXml = Bs3GhsvsFormHelper::getActiveXml(
				$prefix,
				$this->params,
				array(1) // stati
			);

			if ($activeXml)
			{
				// BS-Bootstrap Classes
				$bs3ghsvsModule = $registry->get('bs3ghsvsModule');

				if (
					is_object($bs3ghsvsModule)
					&& count(get_object_vars($bs3ghsvsModule))
				){
					if (!empty($bs3ghsvsModule->bootstrap_size_new))
					{
						foreach ($bs3ghsvsModule->bootstrap_size_new as $size)
						{
							if (!$size->active || !$size->bootstrap_class)
							{
								continue;
							}
	
							if ($size->bootstrap_class === 'col')
							{
								$size->bootstrap_size = '';
							}
							
							// e.g "col-md-6"
							$colClass[] = $size->bootstrap_class . $size->bootstrap_size;
						}
					}
					
					// Fine for parameters in first level.
					$bs3ghsvsModule = new Registry($bs3ghsvsModule);
					$registry->merge($bs3ghsvsModule);
				}
			}
			$registry->set('colClass', implode(' ', $colClass));
			###### Bootstrap-Size-Parameters and others from bs3ghsvsModule.xml -  - END
			
			$registry->set('isRobot', (int) $this->app->client->robot);
			
			$module->params = $registry->toString();
		}
	}


	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		$isRobot = (int) $this->app->client->robot;

		$this->templateParams = $this->app->getTemplate(true)->params;
		
		$view = $this->app->input->get('view');
		
		// Create a switch to exclude fucking modules with fucking wrong context.
		$iAmAnArticle =
			$this->app->isClient('site')
			&& isset($article->params)
			&& $article->params instanceof Registry
			&& isset($article->images)
			&& $params instanceof Registry
			&& !$params->get('iAmAModuleGhsvs', 0)
			&& isset($article->text);

		$iAmAContactView =
			$this->app->isClient('site')
			&& $context === 'com_contact.contact'
			&& $view === 'contact'
			&& !empty($article->id) && !empty($article->name)
			&& !empty($article->params) && isset($article->misc)
			&& !$params->get('iAmAModuleGhsvs', 0);
		
		$print = $this->app->input->getBool('print');

		###### image resize - START

		// START image resize Article-Intro-Images in catgeory/featured view.
		if (
			$iAmAnArticle
			&& $this->params->get('imageoptimizer') === 1
			&& in_array($context, array('com_content.category', 'com_content.featured'))
			&& strpos($article->images, '"introtext_imagesghsvs":') === false
		){
			// Build basic $article->Imagesghsvs based upon $article->images and more.
			Bs3ghsvsItem::getItemImagesghsvs($article);
			$collect_images = array();

			if (
				($IMAGE = $article->Imagesghsvs->get('image_intro'))
				&& file_exists(JPATH_SITE . '/' . $IMAGE)
			){
				$collect_images = Bs3ghsvsItem::getImageResizeImages('image_intro', $IMAGE);
			} // end if ($IMAGE = $article->Imagesghsvs->get('image_intro'))

			$article->Imagesghsvs->set('introtext_imagesghsvs', $collect_images);
			$article->images = $article->Imagesghsvs->toString();
		}
		// END image resize Article-Intro-Images in catgeory/featured view.

		// START image resize Article-fulltext-Images in article view.
		if (
			$iAmAnArticle
			&& $this->params->get('imageoptimizer') === 1
			&& in_array($context, array('com_content.article'))
			&& strpos($article->images, '"fulltext_imagesghsvs":') === false
		){
			// Build basic $article->Imagesghsvs based upon $article->images and more.
			Bs3ghsvsItem::getItemImagesghsvs($article);
			$collect_images = array();

			if (
				($IMAGE = $article->Imagesghsvs->get('image_fulltext'))
				&& file_exists(JPATH_SITE . '/' . $IMAGE)
			){
				$collect_images = Bs3ghsvsItem::getImageResizeImages('image_fulltext', $IMAGE);
			} // end if ($IMAGE = $article->Imagesghsvs->get('image_fulltext'))

			$article->Imagesghsvs->set('fulltext_imagesghsvs', $collect_images);
			$article->images = $article->Imagesghsvs->toString();
		}
		// END image resize Article-fulltext-Images in article view.

		// START image resize Article-fulltext-Images in article view.
		if (
			$iAmAnArticle
			&& $this->params->get('imageoptimizer_articletext') === 1
			&& in_array($context, array('com_content.article'))
			&& $view === 'article'
			// Collection already done?
			&& strpos($article->images, '"articletext_imagesghsvs":') === false
			// All images found in article text.
			&& ($imagesInArticle = Bs3ghsvsItem::getAllImgSrc($article->text))
		){
			// Build *basic* $article->Imagesghsvs based upon $article->images and more.
			Bs3ghsvsItem::getItemImagesghsvs($article);
			$collect_images = array();
			
			// First resize found images and collect results.
			if ($imagesInArticle['src'])
			{
				$collect_images = Bs3ghsvsItem::getImageResizeImages(
					'image_articletext',
					$imagesInArticle['src']
				);

				// Möglichkeit in einem com_content/article/xyz.php ein eigenes 'jlayout_articletext_print' unterzujubeln.
				// Anlass war gs-wesendorf, wo im Ausdruck die Bilder full sein müssen.
				// Derzeit existiert die Einstellung nur im Blog-Layout blogghsvs-standard.xml.
				if (
					#$print
					#&& 
					$jlayout_articletext_print = trim($article->params->get('jlayout_articletext_print', ''))
				){
					$this->params->set('jlayout_articletext', $jlayout_articletext_print);
				}

				if (($jlayout_articletext = trim($this->params->get('jlayout_articletext', 'ghsvs.article_image'))))
				{
					// The array indices are the same for $imgTags and $collect_images.
					// Egal welcher Index, aber 'quote' hat kleinsten String. Deshalb den.
					// Wir brauchen zum "Zählen" nur die $key.
					foreach($imagesInArticle['quote'] as $key => $dummy)
					{
						$displayData = array(
							'attributes' => $imagesInArticle['attributes'][$key],
							'pre'  => $imagesInArticle['pre'][$key],
							'post' => $imagesInArticle['post'][$key],
							'images' => $collect_images[$key]
						);

						$figure = HTMLHelper::_('bs3ghsvs.layout', $jlayout_articletext, $displayData);
	
						// Originalbild gegen Austauschbild austauschen.
						$article->text = str_replace(
							array(
								'<p>' . $imagesInArticle['all'][$key] . '</p>',
								$imagesInArticle['all'][$key],
							),
							$figure,
							$article->text
						);
					}
				}
			}

			$article->Imagesghsvs->set('articletext_imagesghsvs', $collect_images);
			$article->images = $article->Imagesghsvs->toString();
		}
		###### image resize - END
		
		###### open graph - START
		
		if (
			$iAmAnArticle
			&& $this->params->get('opengraphActive') === 1
			&& $context === 'com_content.article'
			&& $view === 'article'
			&& Factory::getDocument()->getType() === 'html'
		){
			// Build basic $article->Imagesghsvs based upon $article->images and more.
			Bs3ghsvsItem::getItemImagesghsvs($article);
			
			if (
				$this->params->get('imageoptimizer') === 1
				&& $article->Imagesghsvs->get('fulltext_imagesghsvs')
				&& $this->params->get('image_fulltext')->active_og
			){
				// Do we have a _og fulltext image?
				$this->ogCollection['com_content.article'] = ArrayHelper::getColumn(
					(array) $article->Imagesghsvs->get('fulltext_imagesghsvs'),
					'_og'
				);
				
				// Reduce to only image paths.
				$this->ogCollection['com_content.article'] = ArrayHelper::getColumn(
					$this->ogCollection['com_content.article'],
					'img'
				);
			}
			
			// Nothing found? Try standard images.
			if (!$this->ogCollection['com_content.article'])
			{
				$imgFields = array(
					'image_fulltext_uncached',
					'image_fulltext',
					'image_fulltext_popupghsvs',
					'image_intro_uncached',
					'image_intro',
					'image_intro_popupghsvs',
				);
				
				foreach ($imgFields as $field)
				{
					if (($img = $article->Imagesghsvs->get($field, '')))
					{
						$this->ogCollection['com_content.article'][] = $img;
					}
				}
			}
			
			// Any images inside articletext?
			if (
				$this->params->get('imageoptimizer_articletext') === 1
				&& $article->Imagesghsvs->get('articletext_imagesghsvs')
				&& $this->params->get('image_articletext')->active_og
			){
				// Do we have _og articletext images?
				$images_articletext = ArrayHelper::getColumn(
					(array) $article->Imagesghsvs->get('articletext_imagesghsvs'),
					'_og'
				);
				
				// Reduce to only image paths.
				$images_articletext = ArrayHelper::getColumn($images_articletext, 'img');
				$this->ogCollection['com_content.article'] = \array_merge(
					$this->ogCollection['com_content.article'],
					$images_articletext
				);
			}
			// Nothing found? Get all images by yourself from articletext.
			else
			{
				if ($imagesInArticle = Bs3ghsvsItem::getAllImgSrc($article->text))
				{
					$this->ogCollection['com_content.article'] = \array_merge(
						$this->ogCollection['com_content.article'],
						$imagesInArticle['src']
					);
				}
			}
		}
		
		###### open graph - END

		###### schema-org - START
		if (
			$this->sd_robotsStateOk
			&& $this->params->get('structureddataActive', 1) === 1
			&& Factory::getDocument()->getType() === 'html'
		){
			JLoader::register('Bs3ghsvsStructuredData', __DIR__ . '/Helper/StructuredData.php');
			$doc = Factory::getDocument();
			$prettyPrint = JDEBUG || $this->params->get('sd_prettyPrint', 0) ? JSON_PRETTY_PRINT : 0;

			// start Schema BreadcrumbList
			if (!isset(static::$loaded[__METHOD__]['sd_breadcrumbList']))
			{
				if (
					$iAmAnArticle
					&& in_array($context, array('com_content.article'))
					&& $view === 'article'
					&& !empty($article->readmore_link)
					&& !empty($article->title)
				){
					$schema = Bs3ghsvsStructuredData::sd_breadcrumbList($this->app, $article);
				}
				else
				{
					$schema = Bs3ghsvsStructuredData::sd_breadcrumbList($this->app);
				}
				// Don't use addScriptDeclaration in Joomla 3! Maybe in Joomla 4 possible.
				// https://github.com/joomla/joomla-cms/pull/25117#issuecomment-518005517
				// https://github.com/joomla/joomla-cms/pull/25357
				$doc->addCustomTag(Bs3ghsvsStructuredData::buildScriptTag($schema, $prettyPrint));

				static::$loaded[__METHOD__]['sd_breadcrumbList'] = 1;
			}
			// end Schema BreadcrumbList

			// start Schema Organization
			if (!isset(static::$loaded[__METHOD__]['sd_organization']))
			{
				if ($iAmAContactView)
				{
					$schema = Bs3ghsvsStructuredData::sd_contactPoint($article);
				}
				else
				{
					$schema = Bs3ghsvsStructuredData::sd_organization(
						!$this->templateParams->get('isFrontpage')
					);
				}

				// Don't use addScriptDeclaration in Joomla 3! Maybe in Joomla 4 possible.
				// https://github.com/joomla/joomla-cms/pull/25117#issuecomment-518005517
				// https://github.com/joomla/joomla-cms/pull/25357
				$doc->addCustomTag(Bs3ghsvsStructuredData::buildScriptTag($schema, $prettyPrint));

				// double Paranoia.
				static::$loaded[__METHOD__]['sd_organization'] = 1;
			}
			// end Schema Organization
			
			// start Schema Article
			if (
				$iAmAnArticle
				&& !isset(static::$loaded[__METHOD__]['sd_article'])
				&& in_array($context, array('com_content.article'))
				&& $view === 'article'
			){
				$schema = Bs3ghsvsStructuredData::sd_article($article);

				// Don't use addScriptDeclaration in Joomla 3! Maybe in Joomla 4 possible.
				// https://github.com/joomla/joomla-cms/pull/25117#issuecomment-518005517
				// https://github.com/joomla/joomla-cms/pull/25357
				$doc->addCustomTag(Bs3ghsvsStructuredData::buildScriptTag($schema, $prettyPrint));
				
				// double Paranoia.
				static::$loaded[__METHOD__]['sd_article'] = 1;
			} // end Schema Article
		}
		###### schema-org - END

		###### pagebreakghsvs-slider - START
		if (
			$this->app->isClient('site')
			&& $context === 'com_content.article'
			&& strpos($article->text, '{pagebreakghsvs-slider ') !== false
		){
			$regex = '#<p[^>]*>\s*{pagebreakghsvs-slider\s+(.+?)}\s*</p>#i';
			$regexEnd = '#<p[^>]*>\s*{pagebreakghsvs-slider[^}]*slidersEnd[^}]*}\s*</p>#iU';
			$headingTagGhsvs = $this->params->get('headingTagGhsvs', 'h4');
			$collector = $endedTextBlocks = array();

			// Array mit mindestens 1 Text-Element, egal, ob slidersEnd gefunden oder nicht.
			$endedTextBlocks = preg_split($regexEnd, $article->text);
			
			// Finde falsch platzierte Eingaben von slidersEnd.
			foreach ($endedTextBlocks as $i => $endedText)
			{
				if (!trim($endedText))
				{
					unset($endedTextBlocks[$i]);
				}
			}
			
			// Now find embedded slides in each block.
			foreach ($endedTextBlocks as $i => $endedText)
			{
				preg_match_all($regex, $endedText, $matches, PREG_SET_ORDER);
				$text = preg_split($regex, $endedText);

				// Teil vor erstem slide, der aber ggf. auch leer sein kann,
				//  falls erstes regex ganz am Anfang im Beitrag.
				$collector[] = $text[0];
				
				// Es wurden Panel-Regex gefunden. Dann Accordion aufbauen
				if (count($text) > 1)
				{
					$selector = $dataParent = 'pagebreakghsvs' . $article->id . '-' . $i;
					
					if (!$print && !$isRobot)
					{
						$collector[] = HTMLHelper::_('bootstrap.startAccordion',
								$selector,
								array(
									// Damit mehrere geöffnet werden können auf FALSE!
									'parent' => false,
								)
							);
					}

					// Panels.
					foreach ($text as $key => $subtext)
					{
						if ($key)
						{
							$match = $matches[$key - 1];
							$match = (array) Utility::parseAttributes($match[0]);
							$title = $title2 = $class = '';
							
							$title = htmlspecialchars_decode($match['title'], ENT_COMPAT);
							$title = htmlspecialchars($title, ENT_COMPAT, 'utf-8');
							$title2 = @htmlspecialchars_decode($match['title2'], ENT_COMPAT);
							$title2 = htmlspecialchars($title2, ENT_COMPAT, 'utf-8');
	
							$href = $selector . '-' . $key;
							
							if (!$print && !$isRobot)
							{
								$collector[] = HTMLHelper::_('bootstrap.addSlide',
											$selector,
											$title,
											$href,
											$class,
											$headingTagGhsvs,
											$title2
										);
							}
							else
							{
								// $collector[] = '<' . $headingTagGhsvs . '>';
								// $collector[] = $title . ($title2 ? ' - ' . $title2 : '');
								// $collector[] = '</' . $headingTagGhsvs . '>';
							}
							
							$collector[] = '<h4 class="headAutoByPagebrekghsvs">' . $title . ($title2 ? ' - ' . $title2 : '') . '</h4>';

							$collector[] = $subtext;
							
							if (!$print && !$isRobot)
							{
								$collector[] = HTMLHelper::_('bootstrap.endSlide');
							}
							$collector[] = "\n<!--endSlide-->\n";
						}
					}

					if (!$print && !$isRobot)
					{
						$collector[] = HTMLHelper::_('bootstrap.endAccordion');

					// Aktive IDs in Session schreiben mit Ajay-Plugin.
					$collector[] = HTMLHelper::_('bs3ghsvs.activeToSession', $dataParent);
					}
					
					$collector[] = "\n<!--endAccordion-->\n";
				}
			}
			$article->text = implode("\n", $collector);
		}
		###### pagebreakghsvs-slider - END
	}

	public function onSubmitContact(&$contact, &$data)
	{
		if (!empty($data['contact_phoneghsvs']))
		{
			$data['contact_message'] .= "\n\n";
			$data['contact_message'] .= Text::_('COM_CONTACT_CONTACT_VIEW_GHSVS_TELEPHONE');
			$data['contact_message'] .= ': ' . $data['contact_phoneghsvs'];
		}
		return true;
	}

	public function onExtensionAfterDelete($context, $table)
	{
		$this->order();
	}

	public function onExtensionAfterSave($context, $table, $isNew)
	{
		$this->order();
	}
	
	public function onBeforeCompileHead()
	{
		// Auf manchen Seiten nötig sonst fatal error.
		// Vielleicht Methode schreiben, um nicht doppelt zu laden?
		// mit static::$loaded.
		$this->templateParams = $this->app->getTemplate(true)->params;

		#### Kill canonicals START
		// Kein Bock mehr. Kille canonical komplett und Schluss!
		if ($this->params->get('deleteCanonicals', 1))
		{
			$doc = Factory::getDocument();
			
			foreach ($doc->_links as $k => $array)
			{
				if ($array['relation'] === 'canonical')
				{
					unset($doc->_links[$k]);
				}
			}
		}
		#### Kill canonicals END

		#### Open Graph tags START
		if (
			$this->app->isClient('site')
			&& $this->params->get('opengraphActive') === 1
		){
			$doc = Factory::getDocument();
			$doc->addCustomTag('<meta property="og:title" content="' . htmlentities($doc->getTitle(), ENT_QUOTES, 'utf-8') . '">');
			$doc->addCustomTag('<meta property="og:url" content="' . $doc->getBase() . '">');
			$doc->addCustomTag('<meta property="og:site_name" content="' . htmlentities($this->app->get('sitename'), ENT_QUOTES, 'utf-8') . '">');
			$doc->addCustomTag('<meta property="og:description" content="' . htmlentities($doc->getDescription(), ENT_QUOTES, 'utf-8') . '">');
			
			if ($this->templateParams->get('isFrontpage'))
			{
				$doc->addCustomTag('<meta property="og:type" content="website">');
			}
			elseif ($this->app->input->get('view') === 'article')
			{
				$doc->addCustomTag('<meta property="og:type" content="article">');
			}
   
			$default_images_path = $this->params->get('og_default_images', 'images/fb_default_images');
	
			if (is_dir(JPATH_SITE . '/' . $default_images_path))
			{
				$filter = '\.(jpg|JPG|gif|GIF|jpeg|JPEG|png|PNG)$';
				$this->ogCollection['default_images'] = Folder::files(
					JPATH_SITE . '/' . $default_images_path,
					$filter
				);
				
				foreach ($this->ogCollection['default_images'] as $i => $img_url)
				{
					$this->ogCollection['default_images'][$i] = $default_images_path . '/' . $img_url;
				}
			}
			
			$this->ogCollection = \array_merge(
				$this->ogCollection['com_content.article'],
				$this->ogCollection['mod_custom.content'],
				$this->ogCollection['default_images']
			);
			
			$this->ogCollection = \array_unique($this->ogCollection);
			
			foreach ($this->ogCollection as $imagePath)
			{
				$doc->addCustomTag('<meta property="og:image" content="' . Bs3ghsvsItem::addUriRoot($imagePath) . '">');
			}
		}
		#### Open Graph tags END
		return;
	}
	/**
	 * Structureddata. Kill microdata attributes if activated.
	 */
	public function onAfterRender()
	{
		if (
			$this->params->get('structureddataActive', 1) === 1
			&& $this->params->get('sd_killmicrodata') === 1
		){
			$all = $this->app->getBody();
			
			if (strpos($all, '<body') === false)
			{
				return;
			}
			$sepa = '<body ';

			$html = explode('<body ', $all);
			
			if (!$html)
			{
				$html = explode('<body>', $all);
				$sepa = '<body>';
			}

			if (count($html) !== 2 || strpos($html[1], 'itemscope') === false)
			{
				return;
			}

			$html[1] = str_replace(
				array(' itemscope ', ' itemtype=', ' itemprop='),
				array(' data-itemscopeOff ', ' data-itemtypeOff=', ' data-itempropOff='),
				$html[1], $count);
				
			if ($count)
			{
				$this->app->setBody(implode($sepa, $html));
			}
		}
	}

	protected function register()
	{
		JLoader::register('Bs3GhsvsRegisterJQuery', __DIR__ . '/Helper/RegisterJQuery.php');
		JLoader::register('Bs3GhsvsRegisterBootstrap', __DIR__ . '/Helper/RegisterBootstrap.php');
		JLoader::register('Bs3GhsvsRegisterBehavior', __DIR__ . '/Helper/RegisterBehavior.php');
		JLoader::register('Bs3GhsvsRegisterFormbehavior', __DIR__ . '/Helper/RegisterFormbehavior.php');
		JLoader::register('Bs3GhsvsRegisterBs3ghsvs', __DIR__ . '/Helper/RegisterBs3ghsvs.php');
		JLoader::register('Bs3GhsvsRegisterIcon', __DIR__ . '/Helper/RegisterIcon.php');
		JLoader::register('Bs3GhsvsRegisterSelect', __DIR__ . '/Helper/RegisterSelect.php');

		$error = array();

		if (false === Bs3GhsvsRegisterJQuery::register())
		{
			$this->executeFe = false;
			$error[] = 'Bs3GhsvsRegisterJQuery';
		}

		if (false === Bs3GhsvsRegisterBootstrap::register())
		{
			$this->executeFe = false;
			$error[] = 'Bs3GhsvsRegisterBootstrap';
		}

		if (false === Bs3GhsvsRegisterBehavior::register())
		{
			$this->executeFe = false;
			$error[] = 'Bs3GhsvsRegisterBehavior';
		}

		if (false === Bs3GhsvsRegisterFormbehavior::register())
		{
			$this->executeFe = false;
			$error[] = 'Bs3GhsvsRegisterFormbehavior';
		}

		if (false === Bs3GhsvsRegisterBs3ghsvs::register())
		{
			$this->executeFe = false;
			$error[] = 'Bs3GhsvsRegisterBs3ghsvs';
		}

		if (false === Bs3GhsvsRegisterIcon::register())
		{
			$this->executeFe = false;
			$error[] = 'Bs3GhsvsRegisterIcon';
		}

		if (false === Bs3GhsvsRegisterSelect::register())
		{
			$this->executeFe = false;
			$error[] = 'Bs3GhsvsRegisterSelect';
		}
		if ($error)
		{
			$add = __METHOD__ . ': Something went completely wrong while registering these HTMLHelper-Methods: ' . implode(', ', $error);

			if (self::$log)
			{
				Log::add($add, Log::CRITICAL, 'bs3ghsvs');
			}
			
			$this->app->enqueueMessage($add, 'error');
			
			return false;
		}

		return true;
	}

	/**
	 * Reorder this plugin as FIRST one in table #__extensions.
	*/
	protected function order()
	{
 		$ordering = $this->db->qn('ordering');
		$table = $this->db->qn('#__extensions');
		$type = $this->db->qn('type') . ' = ' . $this->db->q('plugin');
		$folder = $this->db->qn('folder') . '=' . $this->db->q('system');
		$element = $this->db->qn('element') . ' != ' . $this->db->q($this->_name);
		$query = $this->db->getQuery(true)
			->select('MIN(' . $ordering . ')')->from($table)
			->where($type)->where($folder)->where($element);

		$this->db->setQuery($query);

		$min = (int) $this->db->loadResult();
		$min = $min - 10;

		$query->clear()
			->update($table)->set($ordering . '=' . (int) $min)
			->where($type)->where($folder)->where(str_replace('!=', '=', $element))
			->where($ordering . '!=' . (int) $min);

		$this->db->setQuery($query);

		try
		{
			$this->db->execute();
		}
		catch (RuntimeException $e)
		{
			if (self::$log)
			{
				$add = __METHOD__ . ': DB-Sortierung konnte nicht geprüft/gesetzt werden.';
				Log::add($add, Log::CRITICAL, 'bs3ghsvs');
			}
			return false;
		}
		return true;
	}
	
	/**
	 * Getter for parameters of this plugin via PlgSystemBS3Ghsvs::getPluginParams()
	 *
	 */
	public static function getPluginParams()
	{
		return static::$plgParams;
	}

	function onAjaxSessionBs3Ghsvs()
	{
		if (strtolower($this->app->input->server->get('HTTP_X_REQUESTED_WITH', '')) !== 'xmlhttprequest')
		{
			return 'Have fun, laugh louder';
			// return __METHOD__ . ' not allowed.';
		}
		
		// AJAX-Input.
		$input = $this->app->input;
		$cmd = $input->get('cmd', '', 'ALNUM');
		$key = trim($input->get('key', '', 'STRING'));
// file_put_contents(__DIR__ . '/dannloeschen.txt', $cmd . ' ' . $key);
		if (!$key || !in_array($cmd, array('add', 'get', 'destroy')))
		{
			return;
		}
		
		$data = $input->get('data', '', 'STRING');

		$node  = static::$basepath;
		$session = Factory::getSession();
		$sessionData = $session->get($node);
		
		switch($cmd)
		{
			case 'add':
				$sessionData[$key] = $data;
				$session->set($node, $sessionData);
				return($sessionData[$key] . ' written.');
			break;
			case 'get':
				return isset($sessionData[$key]) ? $sessionData[$key] : null;
			break;
			case 'destroy':
				$sessionData = null;
				$session->set($node, $sessionData);
				return '$sessionData destroyed';
			break;
		}
		return;
	}
	
	/**
	 * Geht wahrscheinlich besser. Ist zum Resizen von speziellen Modul-Bildern.
	 * $imagesToResizeCollect Die Bilder, schon als HTML-Tags. Sollen final mit figure HTML zurückgehen.
	 */
	public static function moduleImagesResizing(
		array $imagesToResizeCollect,
		$moduleParams = false
	) : array
	{
		$PlgParams = self::getPluginParams();

		// Modules resizing should get an own setting. Too lazy at the moment.
		if ($PlgParams->get('imageoptimizer_articletext') !== 1)
		{
			return $imagesToResizeCollect;
		}
		
		// "cache" original keys. Some modules can have 'foto0', 'foto1'...
		$imagesToResizeCollectKeys = array_keys($imagesToResizeCollect);
		
		// Extract images HTML tags parts via preg_match all.
		$imagesInModule = Bs3ghsvsItem::getAllImgSrc(implode($imagesToResizeCollect));

		$collect_images = array();
		$imagesToResizeCollectTemp = array();
		$layoutFound = false;

		// First resize found images and collect results. 
		if (!empty($imagesInModule['src']))
		{
			// Resize and create resized images collection (contains width/height and so on, too).
			$collect_images = Bs3ghsvsItem::getImageResizeImages(
				// Be careful. Already reserved: 'image_intro', 'image_fulltext', 'image_articletext'!
				'image_module',
				$imagesInModule['src'],
				// Which sizePostfixes to use (plugin settings).
				'image_articletext'
			);

			if ($moduleParams instanceof Registry)
			{
				if (!($jlayout_articletext = trim($moduleParams->get('jlayout_articletext', ''))))
				{
					$jlayout_articletext = trim($PlgParams->get('jlayout_articletext',
						'ghsvs.article_image'));
				}
			}

			// Call resize and create figure HTML.
			if ($jlayout_articletext)
			{
				// The array indices are the same for $imgTags and $collect_images.
				// Egal welcher Index, aber 'quote' hat kleinsten String. Deshalb den.
				// Wir brauchen zum "Zählen" nur die $key.
				foreach($imagesInModule['quote'] as $key => $dummy)
				{
					$displayData = array(
						'attributes' => $imagesInModule['attributes'][$key],
						'pre'  => $imagesInModule['pre'][$key],
						'post' => $imagesInModule['post'][$key],
						'images' => $collect_images[$key]
					);

					$figure = HTMLHelper::_('bs3ghsvs.layout', $jlayout_articletext, $displayData);
					
					if ($figure && !$layoutFound)
					{
						$layoutFound = true;
					}

					$imagesToResizeCollectTemp[$imagesToResizeCollectKeys[$key]] = $figure;
				}
			}
		}

		if ($layoutFound)
		{
			return $imagesToResizeCollectTemp;
		}
		
		return $imagesToResizeCollect;
	}
}
