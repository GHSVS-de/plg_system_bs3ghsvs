<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Log\Log;

class plgSystemBs3GhsvsInstallerScript extends InstallerScript
{
	public function preflight($type, $parent)
	{
		$manifest = @$parent->getManifest();

		if ($manifest instanceof SimpleXMLElement)
		{
			$minimumPhp = trim((string) $manifest->minimumPhp);
			$minimumJoomla = trim((string) $manifest->minimumJoomla);

			// Custom
			$maximumPhp = trim((string) $manifest->maximumPhp);
			$maximumJoomla = trim((string) $manifest->maximumJoomla);
			/*
			$databaseServerType = trim((string) $manifest->databaseServerType);
			$databaseServerType = explode(',', $databaseServerType);
			*/

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

			// serverType mysql = MySQL, MySQLi, MySQL (PDO), MariaDB
			/*if (!in_array(Factory::getDbo()->getServerType(), $databaseServerType))
			{
				$msg = 'Your databse server type (' . Factory::getDbo()->getServerType() . ') is not supported by this plugin. Expected database type is "mysql" (MySQL, MySQLi, MySQL (PDO), MariaDB).';
				Log::add($msg, Log::WARNING, 'jerror');
			}*/

			if (isset($msg))
			{
				return false;
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
}
