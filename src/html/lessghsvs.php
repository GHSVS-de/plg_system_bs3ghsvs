<?php
/* lessghsvs.php JHtmlLessGhsvs */
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

abstract class JHtmlLessGhsvs
{
	protected static $loaded = array();
	private static $lessplugin = array('system', 'lessghsvs');	
	
/**
2015-11-10
$input: Less-File. Ohne Pfad. Wird mit file_get_contents eingebunden.

$options(
'output': optionale CSS-Datei + relativer Pfad. Sonst Name wie Less-File plus angehängtes .css und /css/separateLessCompile im Templateordner.

'outputFile': erleichterte, optionale CSS-Datei. Nur dateiname ohne .css. Landet im Standardornder /css/separateLessCompile im Templateordner. Überschreibt 'output'.

'inputDir': optional abweichendes Verzeichnis für $input. Sonst Suche in templates/AKTIVESTEMPLATE/less/separateLessCompile.
Falls false oder leer: templates/AKTIVESTEMPLATE/less

'force': Bool false|true|String 'plugin' (= Einstellung aus Plugin übernehmen)

'relativeUrls': Bool false|true|String 'plugin' (= Einstellung aus Plugin übernehmen)

'compress': Bool false|true|String 'plugin' (= Einstellung aus Plugin übernehmen)

'addStyleSheet': Bool false => kein §doc->addStyleSheet.

return: Relativer Pfad des CSS. Oder false, falls Fehler.
Oder falls 'returnInOut' = true, ein Array mit Input und Outputfile
)
*/
	public static function addLessCss(
		$input,
		$options = array(),
		$folder = 'separateLessCompile'
	){
		if (!is_string($input) || !is_array($options) || !($input = trim($input)))
		{
			return false;
		}

		JLoader::register('Bs3ghsvsTemplate', __DIR__ . '/../Helper/TemplateHelper.php');

		$params = Bs3ghsvsTemplate::getLessPluginParams();

		if (!$params->get('isInstalled'))
		{
			if (PlgSystemBS3Ghsvs::$log)
			{
				$add = __METHOD__ . ': Could not compile file ' . $input . '. Plugin ' . $params->get('checkedPlugin') . ' not available.';
				Log::add($add, Log::ERROR, 'bs3ghsvs');
			}

			return false;
		}
		
		$tplName = Factory::getApplication()->getTemplate();
		
		$tplPath = 'templates/' . $tplName;
		
		$default_options = array(
			'inputDir' => $tplPath . '/less/' . $folder,
			'output' => $tplPath . '/css/' . $folder . '/' . $input . '.css',
			'outputFile' => '',
			'force' => $params->get('less_force', false),
			'relativeUrls' => true,
			'compress' => $params->get('less_compress', false),
			'importDirs' => array($tplPath . '/less'),
			'variables' => array('@testghsvs1' => '4359px',),
			'importBefore' =>  array(
				'variables-paths.less',
				'import.less',
			),
			'addStyleSheet' => true,
			'returnInOut' => false,
		);

		$options = array_merge($default_options, $options);
		$trimString = array('inputDir', 'outputFile');

		foreach ($trimString as $chck)
		{
			$options[$chck] = trim($options[$chck]);
		}

		// So kann man auch ein false o.a. verwenden, für keine.
		$checkArray = array('importDirs', 'variables', 'importBefore');

		foreach ($checkArray as $chck)
		{
			if (!is_array($options[$chck]))
			{
				$options[$chck] = array();
			}
		}
		
		$options = new Registry($options);

		// Falls false oder leer übergeben wurde,
		// nicht $folder verwenden, sondern direkt
		// im /less/-Verzeichnis nach LESS-Datei suchen.
		if (!$options->get('inputDir'))
		{
			$options->set('inputDir', $tplPath . '/less');
		}
		
		$lessFile = Path::clean(
			$options->get('inputDir') . '/' .$input,
			'/'
		);
		$lessFile = trim($lessFile, ' /\\');
		
		$lessFileAbs = JPATH_SITE . '/' . $lessFile;
		
		if (!file_exists($lessFileAbs))
		{
			if (PlgSystemBS3Ghsvs::$log)
			{
				$add = __METHOD__ . ': File ' . $lessFileAbs . ' does not exist.';
				Log::add($add, Log::ERROR, 'bs3ghsvs');
			}

			return false;
		}
  
		// Ein vom LESS abweichender CSS-Dateiname?
		if ($options->get('outputFile', ''))
		{
			$options->set('output',
				$tplPath . '/css/' . $folder . '/' . $options->get('outputFile') . '.css'
			);
		}
		
		$output = Path::clean($options->get('output'), '/');
		$output = trim($output, ' /\\');
  
		$outputAbs = JPATH_SITE . '/' . $output;

		if (!file_exists($outputAbs))
		{
			$options->set('force', true);
		}

		if ((bool) $options->get('compress'))
		{
			// Muss exakt so formuliert sein, wie komprimierte Ausgabe.
			// Einfaches Whitespace nach Doppelpunkt und kein finales Semicolon.
			// Wenn ich es richtig sehe, verwendet Parser nur normales Whitespace.
			$marker = '.CompileMarker' . (str_replace('.', '', uniqid('', true))) . '{color: inherit}';
		}
		else
		{
			$marker = '/* CompileMarker' . microtime() .' */';
		}

		// 1000000000 = 2001-09-09
		$constantTimestamp = '1000000000';
		$lessContent = array();

		if (!class_exists('lessc'))
		{
			if (empty($params->get('lesscPathAbs')) || !file_exists($params->get('lesscPathAbs')))
			{
				if (PlgSystemBS3Ghsvs::$log)
				{
					$add = __METHOD__ . ': Could not load class lessc.';
					Log::add($add, Log::ERROR, 'bs3ghsvs');
				}
				return false;
			}
			require $params->get('lesscPathAbs');
		}

		$less = new lessc();

		foreach ($options->get('importDirs') as $importDir)
		{
			$less->addImportDir($importDir);
		}

		$less->setOption('relativeUrls', (bool) $options->get('relativeUrls'));
		$less->setOption('compress', (bool) $options->get('compress'));
		$less->setVariables($options->get('variables'));

		foreach ($options->get('importBefore') as $import)
		{
			$lessContent[] = '@import "' . $import . '";';
		}

		$lessContent[] = $marker;
		//DEBUG: $lessContent[] = '.test-ghsvs1{font-size: @testghsvs1;}';
		$lessContent[] = trim(file_get_contents($lessFileAbs));
		$lessContent[] = $marker;
		//DEBUG: $lessContent[] = '.test-ghsvs1{font-size: @testghsvs1;}';
		$lessContent = implode('', $lessContent);
		
		// Is there a cache path in configuration.php?
		if (!($cache_path = trim(Factory::getConfig()->get('cache_path', ''))))
		{
			$cache_path = 'cache';
			$cache_path = Path::clean(
				JPATH_SITE . '/' . $cache_path . '/' . $folder . '/',
				'/'
			);
		}

		// temporäre Datei, die dann kompiliert wird.
		$compileFile = $cache_path . $tplName . '.' . $input;

		// File::write hat Vorteil, dass Verzeichnis erstellt wird.
		File::write($compileFile, $lessContent);

		// Immer konstanten timestamp für diese Datei.
		// Sonst gibt Cache-Vergleich immer aus, dass neu.
		touch($compileFile, $constantTimestamp, $constantTimestamp);
		
		// Die ggf. früher gecachten Daten.
		$cacheFile = $compileFile . '.cache';

		if (file_exists($cacheFile))
		{
			$cache = unserialize(file_get_contents($cacheFile));
		}
		else
		{
			$cache = $compileFile;
		}
		$newCache = $less->cachedCompile($cache, $options->get('force'));

		if (!is_array($cache) || $newCache['updated'] > $cache['updated'])
		{
			// Startmarker
			$start = strpos($newCache['compiled'], $marker);
			$newCache['compiled'] = substr($newCache['compiled'], $start);

			//Endmarker
			$start = strpos($newCache['compiled'], $marker, (strlen($marker)));
			$newCache['compiled'] = substr($newCache['compiled'], 0, $start);

			$newCache['compiled'] = trim(str_replace($marker, '', $newCache['compiled']));

			// File::write hat Vorteil, dass Verzeichnis erstellt wird.
			$content = serialize($newCache);
			File::write($cacheFile, $content);
			
			$content = array('/* ' . basename($output) . ' */');
			$content[] = '/** Compiled by ' . __METHOD__ . ' ' . date('c', time());
			$content[] = 'Source: ' . str_replace(JPATH_SITE, '', $lessFileAbs) . " */\n";
			$content = implode(' ', $content) . $newCache['compiled'];
			
			// File::write hat Vorteil, dass Verzeichnis erstellt wird.
			File::write($outputAbs, $content);
		}
		
		File::delete($compileFile);
		
		if ((bool) $options->get('addStyleSheet'))
		{
			Factory::getDocument()->addStyleSheet($output);
		}

		if ($options->get('returnInOut'))
		{
			return array(
				'output' => $output,
				'input' => $lessFile
			);
		}
		
		// relativer CSS-Pfad oder false bei Fehler:
		return $output;		
	}
}