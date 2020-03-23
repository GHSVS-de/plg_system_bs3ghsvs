<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Log\Log;

class plgSystemBs3GhsvsInstallerScript extends InstallerScript
{
	public function preflight($type, $parent)
	{
		if (!parent::preflight($type, $parent))
		{
			return false;
		}

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
		return true;
	}
}
