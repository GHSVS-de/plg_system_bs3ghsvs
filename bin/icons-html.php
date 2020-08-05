#!/usr/bin/php
<?php
require_once('defines.php');

$folder = JPATH_MAIN . 'src/media/svgs';

$file = $folder . '/prepped-icons.txt';

if (!file_exists($file))
{
	echo 'Error in ' . mkShortPath(__FILE__) . '. Wrong paths. Line ' . __LINE__ . NL . NL;
	exit;
}

$data = json_decode(file_get_contents($file));

$collector = [];
$html = array('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Unbenanntes Dokument</title>
</head>
<body>');

foreach ($data as $iconInfo)
{
	if (!isset($collector[$iconInfo[0]]))
	{
		$collector[$iconInfo[0]] = [];
	}
	$collector[$iconInfo[0]][] = $iconInfo[1];
}

foreach ($collector as $svgFolder => $files)
{
	$html[] = '<h2>' . $svgFolder . '</h2>';

	foreach ($files as $file)
	{
		$html[] = '<div class=iconcontainer style="width:200px;height:200px;display:inline-block;margin:2px;background-color:#eee;text-align:center;padding-top:1rem;">';
		$class = 'icon-' . $svgFolder . '-' . $file;
		$file = $folder . '/' . $svgFolder . '/' . $file . '.svg';
		$fileRel = mkShortPath($file);
		$html[] = '<div class=innercontainer>';
		$html[] = '<div class="' . $class . '" style="font-size:4rem;color:blue;">'
			. file_get_contents($file)
// . '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-file-earmark-medical-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
//  <path fill-rule="evenodd" d="M2 3a2 2 0 0 1 2-2h5.293a1 1 0 0 1 .707.293L13.707 5a1 1 0 0 1 .293.707V13a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V3zm7 2V2l4 4h-3a1 1 0 0 1-1-1zm-2.5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L5 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L7 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317V5.5zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5z"/>
//</svg>'

//. '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-file-earmark-medical-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
//  <path fill-rule="evenodd" d="M2 3a2 2 0 0 1 2-2h5.293a1 1 0 0 1 .707.293L13.707 5a1 1 0 0 1 .293.707V13a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V3zm7 2V2l4 4h-3a1 1 0 0 1-1-1zm-2.5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L5 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L7 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317V5.5zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5z"/>
//</svg>'

			. '</div><p>' . $fileRel . '</p></div></div>';
	}
}
$html[] = '</body>
</html>';
file_put_contents(__DIR__ . '/nur-test.html', implode("\n\n", $html));
//echo ' 4654sd48sa7d98sD81s8d71dsa <pre>' . print_r($collector, true) . '</pre>';exit;