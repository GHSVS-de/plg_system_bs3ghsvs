<?php
/**
 * @copyright   Copyright (C) 2013 S2 Software di Stefano Storti. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

require_once __DIR__ . '/../vendor/autoload.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Language\Text;

use Intervention\Image\ImageManager;

use Joomla\Registry\Registry;

class ImgResizeCache
{
	protected $cache_folder;
	protected $cache_folderRel;
	protected $force;
	protected $origBildAbs;

	// Wie übergeben:
	protected $imagePath;

	// Wie übergeben, aber Path::clean mit /
	protected $origBildRel;
	
	// getimagesize() of original image.
	protected $origImageInfos;
	
	// ImageManager. Only set if WEBP support.
	protected $driver = '';
	
	protected $scaleMethods = array();
	
	// Can be extended. See below.
	protected $supportedFormats = array(
		IMAGETYPE_JPEG,
		IMAGETYPE_PNG,
		IMAGETYPE_GIF
	);
 
	public function __construct(Registry $plgParams)
	{
		$this->cache_folder    = Factory::getApplication()->get('cache_path', JPATH_CACHE);
		$this->cache_folderRel = trim(str_replace(JPATH_SITE, '', $this->cache_folder), '\\/ ');
		$this->force           = $plgParams->get('resizeForce', 0);

		// 0: No, 1: Yes with fallback, 2: exclusive webp (if available).
		$this->webpSupport     = $plgParams->get('webpSupport', 0);

		// ImageManager.
    if ($this->webpSupport && extension_loaded('imagick') && \Imagick::queryFormats('WEBP'))
		{
      $this->driver             = 'imagick';
			$this->supportedFormats[] = IMAGETYPE_WEBP;

			if (\Imagick::queryFormats('BMP'))
			{
				$this->supportedFormats[] = IMAGETYPE_BMP;
			}
    }
    elseif ($this->webpSupport && extension_loaded('gd') && function_exists('imagewebp'))
		{
      $this->driver             = 'gd';
			$this->supportedFormats[] = IMAGETYPE_WEBP;
    }
		// Fallback to Joomla...\Image. Supports only gd at the moment.
		elseif (extension_loaded('gd'))
		{
			$this->driver       = 'joomla';
			$this->webpSupport  = 0;
			$this->scaleMethods = array(
				//1 Not supported yet bzw. unsinnig, da w und h jedesmal ratiokonform berechnet werden.
				//Kommt also selbes raus wie SCALE_INSIDE.
				//Wenn w und h, wird Bild exakt auf diese Größe gestaucht/gezogen. Ratio geht verloren.
				#'SCALE_FILL' => Image::SCALE_FILL,

				//2 Das größere h oder w entscheidet. Anderes wird verkleinert. Ratio bleibt erhalten.
				'SCALE_INSIDE' => Image::SCALE_INSIDE,

				//3 Es wird darauf geachtet, dass w bzw. h nicht unterschritten wird. Ratio bleibt erhalten.
				'SCALE_OUTSIDE' => Image::SCALE_OUTSIDE,

				//4 Not supported yet
				#'CROP' => Image::CROP,
				
				//5 Not supported yet
				#'CROP_RESIZE' => Image::CROP_RESIZE,

				//6 Not supported yet bzw. unsinnig, da w und h jedesmal ratiokonform berechnet werden.
				//w und h werden ggf. mit schwarzem Hintergrund gefüllt.
				#'SCALE_FIT' => Image::SCALE_FIT, 
			);
		}
	}

	public function resize(
		$imagePath,
		$opts,
		$scaleMethod = 'SCALE_INSIDE',
		$sizePostfix = '',
		$force = 0
	){
		// Defines also $this->origImageInfos array with image infos.
		if (!$opts || !$this->_checkImage($imagePath))
		{
			return array('img-1' => $imagePath, 'count' => 1, 'resized' => 0);
		}
		
		
		if (!$this->_checkImage($imagePath))
		{
			// Egal. Hauptsache falschen Pfad zurück, damit man im FE recherchieren kann.
			return $imagePath;
		}

		return $this->_resize(
			#$imagePath,
			$opts,
			$scaleMethod,
			$sizePostfix,
			(!$force ? $this->force : 1)
		);
	}
 
 /**
  * @param array $opts  (w(pixels), h(pixels), crop(boolean), scale(boolean), thumbnail(boolean), maxOnly(boolean), canvas-color(#abcabc), output-filename(string), cache_http_minutes(int))
  * @return new URL for resized image.
  */
	protected function _resize(
		$opts = null,
		$scaleMethod = 'SCALE_INSIDE',
		$sizePostfix = '',
		$force = 0
	){
		$defaults = array(
			// Fliegt erst mal raus:
			// 'crop' => false,
			// 'scale' => false,
			// 'thumbnail' => false,
			// 'bestfit' => true,
			// 'fill' => true,
			
			// false bedeutet upscale, also Bild ggf. auf w bzw. h vergrößern.
			'maxOnly' => false,
			'cacheFolder' => $this->cache_folder,
			'quality' => 80,
			'size' => ''
			// Weitere Werte können sein:
			// 'w' => 240,
			// 'h' => 360,
		);

		$opts = array_merge($defaults, $opts);
		
		$is_u = $opts['size'] === '_u';

		// Don't upscale.
		if ($opts['maxOnly'])
		{
			if (isset($opts['w']) && $opts['w'] > $this->origImageInfos[0])
			{
				$opts['w'] = $this->origImageInfos[0];
			}

			if (isset($opts['h']) && $opts['h'] > $this->origImageInfos[1])
			{
				$opts['h'] = $this->origImageInfos[1];
			}
		}

		$origPathInfos = \pathinfo($this->origBildRel);
/*
Array $origPathInfos
(
	[dirname] => images/logos
	[basename] => PLG_CONTENT_SYNTAXHIGHLIGHTERGHSVS.png
	[extension] => png
	[filename] => PLG_CONTENT_SYNTAXHIGHLIGHTERGHSVS
)
*/
		// B\C break somehow.
		$origPathInfos['extension'] = \strtolower($origPathInfos['extension']);
		
		$targetWidth = $targetHeight = false;
		$neuBildPfad = $origPathInfos['dirname'];
		
		// Build cache filename stepwise.
		$neuBildName = array($origPathInfos['filename']);

		if ($sizePostfix)
		{
			$neuBildName[] = $sizePostfix;
		}

		if (!empty($opts['w']))
		{
			$targetWidth = $opts['w'];

			if (!$sizePostfix)
			{
				$neuBildName[] = 'w' . $targetWidth;
			}
		}

		if (!empty($opts['h']))
		{
			$targetHeight = $opts['h'];

			if (!$sizePostfix)
			{
				$neuBildName[] = 'h' . $targetHeight;
			}
		}
		
		$imgCollector = array();
		
		// Without extension.
		$neuBildName = str_replace(
			'__', '_',
			implode('_', $neuBildName)
		);
  
		$neuBild     = $neuBildPfad . '/' . $neuBildName;
		$neuBildPfad = $this->cache_folder . '/' . $neuBildPfad;
		$neuBildAbs  = $this->cache_folder . '/' . $neuBild;
		$neuBild     = $this->cache_folderRel . '/' . $neuBild;
		$neuBildAbs  = Path::clean($neuBildAbs, '/');

		// Anything to do?
		$do = 0;
		if (!$force)
		{
			if ($this->webpSupport === 2 && \file_exists($neuBildAbs . '.webp'))
			{
				$imgCollector['img-1'] = $neuBild . '.webp';
				$imgCollector['count'] = 1;
				$do = 1;
			}
			elseif
			(
				$this->webpSupport === 1
				&& $webp = \file_exists($neuBildAbs . '.webp')
				&& $orig = \file_exists($neuBildAbs . '.' . $origPathInfos['extension'])
			){
				$imgCollector['img-1'] = $neuBild . '.' . $origPathInfos['extension'];
				$imgCollector['count'] = 1;
				
				if ($origPathInfos['extension'] !== 'webp')
				{
					$imgCollector['img-2'] = $neuBild . '.webp';
					$imgCollector['count'] = 2;
				}
				$do = 1;
			}
			elseif (\file_exists($neuBildAbs . '.' . $origPathInfos['extension']))
			{
				$imgCollector['img-1'] = $neuBild . '.' . $origPathInfos['extension'];
				$imgCollector['count'] = 1;
				$do = 1;
			}
			
			if ($do === 1)
			{
				list(
					$imgCollector['width'],
					$imgCollector['height']
				) = Bs3ghsvsItem::getImageSize($imgCollector['img-1'], true);
				return $imgCollector;
			}
		}

		if (!\is_dir($neuBildPfad))
		{
			Folder::create($neuBildPfad);
		}

		// Keep proportions if w or h is not defined
		if (empty($targetWidth))
		{
			$targetWidth = 0;
		}

		if (empty($targetHeight))
		{
			$targetHeight = 0;
		}

		list($origWidth, $origHeight) = $this->origImageInfos;

		if (!$targetWidth)
		{
			$targetWidth = ($targetHeight / $origHeight) * $origWidth;
		}

		if (!$targetHeight)
		{
			$targetHeight = ($targetWidth / $origWidth) * $origHeight;
		}

		try
		{
			if ($this->driver === 'joomla')
			{
				$image = new Image($this->origBildAbs);
			}
			else
			{
				$manager = new ImageManager(array('driver' => $this->driver));
				$image = $manager->make($this->origBildAbs);
			}
		}
		catch (Exception $e)
		{
			return $this->imagePath;
		}

		$quality = $opts['quality'];
		
		$targetWidth = round($targetWidth);
		$targetHeight = round($targetHeight);

		if ($this->driver === 'joomla')
		{
			// Don't resize. Don't create image in cache folder. Keep original path.
			if ($is_u)
			{
				$imgCollector['img-1'] = $this->origBildRel;
			}
			// Normal job.
			else
			{
				$resizedImage = $image->resize(
					$targetWidth,
					$targetHeight,
					true,
					$this->scaleMethods[$scaleMethod]
				);

				// fix compression level must be 0 through 9 (in case of png)
				if ($this->origImageInfos[2] === IMAGETYPE_PNG)
				{
					$quality = round(9 - $quality * 9/100); // 100 quality = 0 compression, 0 quality = 9 compression
				}

				$resizedImage->toFile($neuBildAbs . '.' . $origPathInfos['extension'], $this->origImageInfos[2], array('quality' => $quality));
				
				$imgCollector['img-1'] = $neuBild . '.' . $origPathInfos['extension'];
			}

			// Recalculate width and height after resizing with SCALE_OUTSIDE.
			if ($scaleMethod === 'SCALE_OUTSIDE')
			{
				list(
					$imgCollector['width'],
					$imgCollector['height']
				) = \getimagesize($neuBildAbs . '.' . $origPathInfos['extension']);
			}
			else
			{
				$imgCollector['width'] = $targetWidth;
				$imgCollector['height'] = $targetHeight;
			}
			
			$imgCollector['count'] = 1;
		}
		else
		{
			if ($scaleMethod === 'SCALE_OUTSIDE')
			{
				$rx = ($targetWidth > 0) ? ($image->width() / $targetWidth) : 0;
				$ry = ($targetHeight > 0) ? ($image->height() / $targetHeight) : 0;
				$ratio = min($rx, $ry);

				$targetWidth = (int) round($image->width() / $ratio);
				$targetHeight = (int) round($image->height() / $ratio);
			}

			$originalIsWebP = $this->origImageInfos[2] === IMAGETYPE_WEBP;
			$count          = 0;
			$image->resize($targetWidth, $targetHeight);

			if ($is_u)
			{
				$imgCollector['img-1'] = $this->origBildRel;
				
				switch ($this->webpSupport)
				{
					case 0:
						$count++;
					break;

					case 1:
						if ($originalIsWebP)
						{
							$count = 1;
						}
						else
						{
							$image->encode('webp');
							$image->save(
								$neuBildAbs . '.webp'
							);
							$imgCollector['img-2'] = $neuBild . '.webp';
							$count = 2;
						}
					break;

					case 2:
						if (!$originalIsWebP)
						{
							$image->encode('webp');
							$image->save(
								$neuBildAbs . '.webp'
							);
							$imgCollector['img-1'] = $neuBild . '.webp';
							$count = 1;
						}
						
					break;
				}
			}
			else
			{
				if ($this->webpSupport === 0 || $this->webpSupport === 1 || $originalIsWebP)
				{
					$image->save(
						$neuBildAbs . '.' . $origPathInfos['extension'],
						$quality
					);
					$count++;
					$imgCollector['img-' . $count] = $neuBild . '.' . $origPathInfos['extension'];
				}
	
				if ($this->webpSupport && !$originalIsWebP)
				{
					$image->encode('webp');
				
					$image->save(
						$neuBildAbs . '.webp',
						$quality
					);
					$count++;
					$imgCollector['img-' . $count] = $neuBild . '.webp';
				}
			}
			$imgCollector['count'] = $count;
			
			// Weil's das Runden spart.
			$imgCollector['width'] = $image->width();
			$imgCollector['height'] = $image->height();
		}

		return $imgCollector;
	}
 
 /**
  * Avoid errors if image corrupted
  * @param string $image_path
  * @return boolean
  */
	protected function _checkImage($imagePath)
	{
		if (!$this->driver)
		{
			return false;
		}

		$this->imagePath   = $imagePath;
		$this->origBildRel = \trim(Path::clean($this->imagePath, '/'), '\\/');
		$purl              = \parse_url($this->origBildRel);

		if (!empty($purl['scheme']) || !empty($purl['query']))
		{
			return false;
		}

		$this->origBildAbs = Path::clean(JPATH_SITE . '/' . $this->origBildRel, '/');
		
		if (!\file_exists($this->origBildAbs))
		{
			return false;
		}

		try
		{
			@$this->origImageInfos = \getimagesize($this->origBildAbs);

			if (!$this->origImageInfos)
			{
				return false;
			}
			
			// e.g. supportedFormats[IMAGETYPE_PNG].
			if (!\in_array($this->origImageInfos[2], $this->supportedFormats))
			{
				return;
			}
			
			if (!$this->checkMemoryLimit($this->origImageInfos, $this->origBildAbs))
			{
				return false;
			}

			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Check memory boundaries
	 *
	 * @param array  $properties   the Image properties array from 
	 * @param string  $imagePath    the image path
	 *
	 * @return boolean
	 *
	 * @since  3.0.3
	 *
	 * @author  Niels Nuebel: https://github.com/nielsnuebel
	 */
	protected function checkMemoryLimit($properties, $imagePath)
	{
		if (!isset($properties['channels']))
		{
			if ($properties[2] = IMAGETYPE_PNG)
			{
				$properties['channels'] = 4;
			}
			else
			{
				$properties['channels'] = 3;
			}
		}

		if (!isset($properties['bits']))
		{
			$properties['bits'] = 16;
		}
		
		$properties['bits'] = ($properties['bits'] / 8) * $properties['channels'];
		
		// width x height x bits
		$memorycheck      = $properties[0] * $properties[1] * $properties['bits'];
		$memorycheck_text = $memorycheck; # / (1024 * 1024);
		$memory_limit     = ini_get('memory_limit');
	
		if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches))
		{
			if ($matches[2] === 'M')
			{
				$memory_limit_value = $matches[1] * 1024 * 1024;
			}
			elseif ($matches[2] === 'K')
			{
				$memory_limit_value = $matches[1] * 1024;
			}
		}
	
		if (isset($memory_limit_value) && $memorycheck > $memory_limit_value)
		{
			/*$app = Factory::getApplication();
			$app->enqueueMessage(
				'In ' . __METHOD__ . ':<br>'
				. 'Image ' . $imagePath . ' seems to be too big to be processed.<br>Calculated memory usage: '
				. $memorycheck_text
				. ', memory_limit: ' . $memory_limit, 'warning');*/
	
			return false;
		}
	
		return true;
	}
}
