<?php
/**
 * @copyright   Copyright (C) 2013 S2 Software di Stefano Storti. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\Image\Image;
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
	
	protected $imagesize;
 
	public function __construct(Registry $plgParams)
	{
		$app = Factory::getApplication();
		$this->cache_folder = $app->get('cache_path', JPATH_CACHE);
		$this->cache_folderRel = trim(str_replace(JPATH_SITE, '', $this->cache_folder), '\\/ ');

		if (!Folder::exists($this->cache_folder))
		{
			Folder::create($this->cache_folder);
		}
		
		$this->force = $plgParams->get('resizeForce', 0);
	}

	public function resize(
		$imagePath,
		$opts,
		$scaleMethod = Image::SCALE_INSIDE,
		$sizePostfix = '',
		$force = 0
	){

		if (!$opts) return $imagePath;

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
  * Avoid errors if image corrupted
  * @param string $image_path
  * @return boolean
  */
	protected function _checkImage($imagePath)
	{
		$this->imagePath = $imagePath;
		
		$this->origBildRel = trim(Path::clean($this->imagePath, '/'), '\\/');
		
		$this->origBildAbs = JPATH_SITE . '/' . $this->origBildRel;
		$this->origBildAbs = Path::clean($this->origBildAbs, '/');

		$purl = parse_url($this->origBildRel);

		if (!empty($purl['scheme']) || !empty($purl['query']))
		{
			return false;
		}
		
		if (!File::exists($this->origBildAbs))
		{
			return false;
		}

		try
		{
			@$this->imagesize = getimagesize($this->origBildAbs);

			if (!$this->imagesize)
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
  * @param array $opts  (w(pixels), h(pixels), crop(boolean), scale(boolean), thumbnail(boolean), maxOnly(boolean), canvas-color(#abcabc), output-filename(string), cache_http_minutes(int))
  * @return new URL for resized image.
  */
	protected function _resize(
		$opts = null,
		$scaleMethod = Image::SCALE_INSIDE,
		$sizePostfix = '',
		$force = 0
	){
  
		$defaults = array(
			'crop' => false,
			'scale' => false,
			'thumbnail' => false,
			'maxOnly' => false,
			'cacheFolder' => $this->cache_folder,
			'quality' => 80,
			'bestfit' => true,
			'fill' => true
		);

		$opts = array_merge($defaults, $opts);

		if ($opts['maxOnly'])
		{
			if (isset($opts['w']))
			{
				if ($opts['w'] > $this->imagesize[0])
				{
					$opts['w'] = $this->imagesize[0];
				}
			}

			if (isset($opts['h']))
			{
				if ($opts['h'] > $this->imagesize[1])
				{
					$opts['h'] = $this->imagesize[1];
				}
			}

			$opts['maxOnly'] = false;
		}

		$finfo = pathinfo($this->origBildRel);

		$w = $h = false;
  
		$neuBildPfad = $finfo['dirname'];

		$neuBildName = array($finfo['filename']);

		if ($sizePostfix)
		{
			$neuBildName[] = $sizePostfix;
		}

		if (!empty($opts['w']))
		{
			$w = $opts['w'];
			if (!$sizePostfix)
			{
				$neuBildName[] = 'w' . $w;
			}
		}

		if (!empty($opts['h']))
		{
			$h = $opts['h'];

			 if (!$sizePostfix)
			 {
				 $neuBildName[] = 'h' . $h;
			 }
		}

		if ($w && $h)
		{
			if (!empty($opts['crop']))
			{
				if (!$sizePostfix)
				{
					$neuBildName[] = 'cp';
				}
			}

			if (!empty($opts['scale']))
			{
				if (!$sizePostfix)
				{
					$neuBildName[] = 'sc';
				}
			}
		}

		$neuBildName = str_replace('__', '_', implode('_', $neuBildName)) . '.' . $finfo['extension'];
  
		$neuBild = $neuBildPfad . '/' . $neuBildName;
		
		$neuBildPfad = $this->cache_folder . '/' . $neuBildPfad;

		$neuBildAbs = $this->cache_folder . '/' . $neuBild;
		$neuBildAbs = Path::clean($neuBildAbs, '/');
		
		$neuBild = $this->cache_folderRel . '/' . $neuBild;

		if (!$force && File::exists($neuBildAbs))
		{
			return $neuBild;
		}

		if (!Folder::exists($neuBildPfad))
		{
			Folder::create($neuBildPfad);
		}

		$create = true;

		#if (class_exists('JImage'))
		{
			if (empty($w)) $w = 0;
			if (empty($h)) $h = 0;

			// Keep proportions if w or h is not defined
			list($width, $height) = $this->imagesize;

			if (!$w)
			{
				$w = ($h / $height) * $width;
			}

			if (!$h)
			{
				$h = ($w / $width) * $height;
			}

			try
			{
				$image = new Image($this->origBildAbs);
			}
			catch (Exception $e)
			{
				return $this->imagePath;
			}

			if ($opts['crop'] == true)
			{
				$rw = $w;
				$rh = $h;
				if ($width/$height < $rw/$rh)
				{
					$rw = $w;
					$rh = ($rw / $width) * $height;
				}
				else
				{
					$rh = $h;
					$rw = ($rh / $height) * $width;
				}

				$resizedImage = $image->resize($rw, $rh)->crop($w, $h);
			}
			else
			{

## $scaleMethod
#JImage::SCALE_INSIDE Standard. Das größere h oder w entscheidet. Anderes wird verkleinert.
#JImage::SCALE_FILL Wenn w und h, wird Bild exakt auf diese Größe gestaucht/gezogen.
#JImage::SCALE_OUTSIDE Es wird darauf geachtet, dass w bzw. h nicht unterschritten wird.
#JImage::SCALE_FIT w und h werden ggf. mit schwarzem Hintergrund gefüllt.
#public function resize($width, $height, $createNew = true, $scaleMethod = self::SCALE_INSIDE)

# IMAGETYPE_JPEG|IMAGETYPE_GIF|IMAGETYPE_PNG

				$resizedImage = $image->resize($w, $h, true, $scaleMethod);
			}

			$properties = Image::getImageFileProperties($this->origBildAbs);

			// fix compression level must be 0 through 9 (in case of png)
			$quality = $opts['quality'];

			if ($properties->type == IMAGETYPE_PNG)
			{
				$quality = round(9 - $quality * 9/100); // 100 quality = 0 compression, 0 quality = 9 compression
			}
			
			$resizedImage->toFile($neuBildAbs, $properties->type, array('quality' => $quality));
		}

		return $neuBild;
	}
}
