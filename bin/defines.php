<?php

$jsonAsArray = true;

// the main dir where e.g. package.json is located.
if (!defined('JPATH_MAIN'))
{
	define('JPATH_MAIN', dirname(__DIR__) . '/');
}

// newline shortcut
if (!defined('NL'))
{
	define('NL', PHP_EOL);
}

// Define PHP varaiables like '$npm_package_DIR_project'.
if (empty($packageJson))
{
	$packageJson = json_decode(
		file_get_contents(JPATH_MAIN . 'package.json'),
		$jsonAsArray
	);
	
	$replaceMe = $replaceWith = array();
if (isset($packageJson['DIR']))
{
	foreach ($packageJson['DIR'] as $key => $value)
	{
		$Variable = 'npm_package_DIR_' . $key;
		$$Variable = trim($value);
		
		$replaceMe[] = '$' . $Variable;
		$replaceWith[] = $value;
	}
}
}

/*if (!defined('CREATION_DATE'))
{
	define('CREATION_DATE', $packageJson['versionTxt']);
}*/

/*if (empty($ftpJson) && file_exists($npm_package_DIR_project . '/ftp-credentials.json'))
{
	$ftpJson = json_decode(
		file_get_contents($npm_package_DIR_project . '/ftp-credentials.json'),
		$jsonAsArray
	);
}*/

function mkShortPath($path)
{
	return str_replace(JPATH_MAIN, '', $path);
}

function doMinify($path)
{
	$needle = '.min.css';
	$strlen = -(strlen($needle));

	echo 'Start minify in dir ' . mkShortPath($path) . NL;

	foreach (glob("$path/*.css") as $filename)
	{
		if (substr($filename, $strlen) === $needle)
		{
			continue;
		}

		$path_parts = pathinfo($filename);
		$output = "$path/" . $path_parts['filename'] . '.min.css';
		$command = "cleancss --level 1 --format breakWith=lf --source-map --source-map-inline-sources --output $output $filename";
		exec($command);
		echo 'Minified: ' . $path_parts['basename'] . NL;
	}
}

function doPrefix($path)
{
	$command = "postcss --config build/postcss.config.js --replace \"$path/*.css\" \"!$path/*.min.css\"";
	exec($command);
	echo 'Prefixed: ' . $path . NL;
}