<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;

class plgSystemBs3GhsvsInstallerScript extends InstallerScript
{
	/**
	 * A list of files to be deleted with method removeFiles().
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $deleteFiles = array(
		'/media/plg_system_bs3ghsvs/css/index.html',
		'/media/plg_system_bs3ghsvs/js/jquery/version.txt',
		'/media/plg_system_bs3ghsvs/js/jquery/jquery-3.4.1.js',
		'/media/plg_system_bs3ghsvs/js/jquery/jquery-3.4.1.min.js',
		'/media/plg_system_bs3ghsvs/js/jquery/jquery-3.4.1.min.map',
		'/media/plg_system_bs3ghsvs/js/jquery/jquery-3.4.1.slim.js',
		'/media/plg_system_bs3ghsvs/js/jquery/jquery-3.4.1.slim.min.js',
		'/media/plg_system_bs3ghsvs/js/jquery/jquery-3.4.1.slim.min.map',
		'/media/plg_system_bs3ghsvs/fontawesome-free/5/_V5.11.2/index.html',
		'/media/plg_system_bs3ghsvs/fontawesome-free/5/_V5.13.0/index.html',
		'/media/plg_system_bs3ghsvs/fontawesome-free/5/svgs/solid/haykal.svg',
		'/plugins/system/bs3ghsvs/vendor/spatie/schema-org/src/Contracts/LockerDeliveryContract.php',
		'/plugins/system/bs3ghsvs/vendor/spatie/schema-org/src/LockerDelivery.php',
		'/plugins/system/bs3ghsvs/vendor/spatie/schema-org/src/ParcelService.php',
		'/plugins/system/bs3ghsvs/vendor/spatie/schema-org/src/Contracts/ParcelServiceContract.php',
	);

	/**
	 * A list of folders to be deleted with method removeFiles().
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $deleteFolders = array(
		'/media/plg_system_bs3ghsvs/fontawesome-free/5/_V5.11.2',
		'/media/plg_system_bs3ghsvs/fontawesome-free/5/_V5.13.0',
		'/media/plg_system_bs3ghsvs/fontawesome-free/5',
		'/media/plg_system_bs3ghsvs/js/bootstrap/4',
		'/media/plg_system_bs3ghsvs/css/bootstrap/4',
		'/plugins/system/bs3ghsvs/Helper/schema-org',
		'/media/plg_system_bs3ghsvs/js/skipto',
	);


	public function preflight($type, $parent)
	{
		$manifest = @$parent->getManifest();

		if ($manifest instanceof SimpleXMLElement)
		{
			if ($type === 'update' || $type === 'install' || $type === 'discover_install')
			{
				$minimumPhp = trim((string) $manifest->minimumPhp);
				$minimumJoomla = trim((string) $manifest->minimumJoomla);

				// Custom
				$maximumPhp = trim((string) $manifest->maximumPhp);
				$maximumJoomla = trim((string) $manifest->maximumJoomla);

				$this->minimumPhp = $minimumPhp ? $minimumPhp : $this->minimumPhp;
				$this->minimumJoomla = $minimumJoomla ? $minimumJoomla : $this->minimumJoomla;

				if ($maximumJoomla && version_compare(JVERSION, $maximumJoomla, '>'))
				{
					$msg = 'Your Joomla version (' . JVERSION . ') is too high for this extension. Maximum Joomla version is: ' . $maximumJoomla . '.';
					Log::add($msg, Log::WARNING, 'jerror');
				}

				// Check for the maximum PHP version before continuing
				if ($maximumPhp && version_compare(PHP_VERSION, $maximumPhp, '>'))
				{
					$msg = 'Your PHP version (' . PHP_VERSION . ') is too high for this extension. Maximum PHP version is: ' . $maximumPhp . '.';
	
					Log::add($msg, Log::WARNING, 'jerror');
				}

				if (isset($msg))
				{
					return false;
				}
			}

			if (trim((string) $manifest->allowDowngrades))
			{
				$this->allowDowngrades = true;
			}
		}
		
		if (!parent::preflight($type, $parent))
		{
			return false;
		}

		if ($type === 'update')
		{
			$this->removeOldUpdateservers();
		}

		return true;
	}

	/**
	 * Runs right after any installation action is preformed on the component.
	 *
	 * @param  string    $type   - Type of PostFlight action. Possible values are:
	 *                           - * install
	 *                           - * update
	 *                           - * discover_install
	 * @param  \stdClass $parent - Parent object calling object.
	 *
	 * @return void
	 */
	function postflight($type, $parent)
	{
		if ($type === 'update')
		{
			$this->removeFiles();
		}
	}

	public function update($installer)
	{
		$this->updateManifestCaches();
	}

	/**
	 * Remove the outdated updateservers.
	 *
	 * @return  void
	 *
	 * @since   version after 2019.05.29
	 */
	 protected function removeOldUpdateservers()
 	{
 		$db = Factory::getDbo();
 		try
 		{
 			$query = $db->getQuery(true);

 			$query->select('update_site_id')
 				->from($db->qn('#__update_sites'))
 				->where($db->qn('location') . ' = '
 					. $db->q('https://raw.githubusercontent.com/GHSVS-de/upadateservers/master/bs3ghsvs-update.xml'));

 			$id = (int) $db->setQuery($query)->loadResult();

 			if (!$id)
 			{
 				return;
 			}

 			// Delete from update sites
 			$db->setQuery(
 				$db->getQuery(true)
 					->delete($db->qn('#__update_sites'))
 					->where($db->qn('update_site_id') . ' = ' . $id)
 			)->execute();

 			// Delete from update sites extensions
 			$db->setQuery(
 				$db->getQuery(true)
 					->delete($db->qn('#__update_sites_extensions'))
 					->where($db->qn('update_site_id') . ' = ' . $id)
 			)->execute();
 		}
 		catch (Exception $e)
 		{
 			return;
 		}
 	}
	/**
	 * Update the manifest caches
	 *
	 * @return  void
	 */
	protected function updateManifestCaches()
	{
		/* [type, element, folder, client_id] */
		$extensions = [
			['plugin', 'bs3ghsvs', 'system', 0],
		];

		// Attempt to refresh manifest caches
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__extensions');

		foreach ($extensions as $extension)
		{
			$query->where(
				'type=' . $db->quote($extension[0])
				. ' AND element=' . $db->quote($extension[1])
				. ' AND folder=' . $db->quote($extension[2])
				. ' AND client_id=' . $extension[3], 'OR'
			);
		}

		$db->setQuery($query);

		try
		{
			$extensions = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			echo Text::sprintf(
				'DB function failed with error number %s <br /><span style=\"color: red;\">%s</span>',
				$e->getCode(), $e->getMessage()) . '<br>';

			return;
		}

		$installer = new Installer;

		foreach ($extensions as $extension)
		{
			if (!$installer->refreshManifestCache($extension->extension_id))
			{
				echo Text::sprintf(
					'Error on updating manifest cache: (type, element, folder, client) = (%s, %s, %s, %s)',
					$extension->type, $extension->element, $extension->name, $extension->client_id) . '<br>';
			}
		}
	}
}
