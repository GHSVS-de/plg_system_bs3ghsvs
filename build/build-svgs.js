#!/usr/bin/env node

'use strict'

const fs = require('fs').promises;

// Keine Ahnung, warum ich das extra brauche, wenn oben "promises" steht.
// Dann schalgen einfachste Aufrufe der Art fs.existsSync() fehl.
const fse = require('fs-extra');
const path = require('path')
const chalk = require('chalk')
const cheerio = require('cheerio')

// const SVGO = require('svgo');
const { optimize } = require('svgo');

const yaml = require('js-yaml')

// Collect some messages for 'prepped-icons.txt'.
var loger = [];

// Icons main folder (absolute Path). Has subfolders like bi/, solid/, brand/
// with svg files inside.
const iconsDir = path.join(__dirname, '../src/media/svgs');

// No icons main folder => Leave!
if (!fse.existsSync(iconsDir))
{
  console.log(chalk.keyword('orange')(`Couldn't read directory ${iconsDir}! In file ${path.basename(__filename)}`));
  process.exit(1);
}

// Function to collect all folders (not files) in a directory recursively in a
// synchronous fashion and push to array "folderlist"
// which is an array of arrays like [AbsolutePath, RelativePath] per found folder.
var walkSync = function(dir, folderlist)
{
  let folders = fse.readdirSync(dir);

  // No filelist array provided? Create empty one.
	folderlist = folderlist || [];

	folders.forEach(function(folder)
	{
		let folderPathAbs = `${dir}/${folder}`;

    if (fse.statSync(folderPathAbs).isDirectory() && fse.readdirSync(folderPathAbs).length > 0)
		{
			let folderPathRel = folderPathAbs.replace(`${iconsDir}/`, "").toLowerCase().replace(/\//g, '-');
			folderlist.push([folderPathAbs, folderPathRel]);
      folderlist = walkSync(folderPathAbs, folderlist);
    }
    else
		{
      //filelist.push(dir+file);
    }
  });
  return folderlist;
};

// Get all subfolders
let Folders = walkSync(iconsDir);

// console.log(Folders);
// process.exit(1);

if (Folders.length > 0)
{
  // Auch das Stammverzeichnis hinzufügen (neben den Unterordnern).
	Folders.push([iconsDir, ""]);
}
else
{
  console.log(chalk.keyword('orange')(`Directory ${iconsDir} is empty!
    In file ${path.basename(__filename)}`));
	process.exit(1);
}

// For me my custom height/width is important.
// Don't change the viewBox!
const svgAttributes = {
  class: '',
  fill: 'currentColor',
  xmlns: 'http://www.w3.org/2000/svg',
  width: '1em',
  height: '1em',
  // viewBox: '0 0 16 16'
}

// Function to get code *.svg cleaner rules.
async function getSvgoConfig()
{
  const svgoConfigFile = await fs.readFile(
    path.join(__dirname, '../svgo.yml'), 'utf8'
  )

  return yaml.load(svgoConfigFile)
}

/**
 * file: Absolute path of *.svg
 * config: See function getSvgoConfig().
 * CLASSPREFIX: name of the subfolder, e.g. 'bi', 'solid', 'brand' ...
 */
async function processFile(filepath, config, CLASSPREFIX)
{
  // Pure svg filename without extension.
  const fileBasename = path.basename(filepath, '.svg');

  const originalSvg = await fs.readFile(filepath, 'utf8');
  //const svgo = await new SVGO(config);

  // Clean the code:
  const optimizedSvg = await optimize(originalSvg);

  // "Prepare" for "jQuery talk":
  const $ = await cheerio.load(optimizedSvg.data, {
    xml: {
      xmlMode: true
    }
  });

  // Create jQuery object:
  const $svgElement = $('svg');

  // I have absolutely no idea what the following lines are good for???
  // And what the comments are telling me??

	// We keep all SVG contents apart from the `<svg>` element.
	// `$(this)` refers to the original object not the replaced one!
  $svgElement.replaceWith($('<svg>').append($(this).html()));

	// Then we set the `svgAttributes` in the order we want to,
	// hence why we remove the attributes and add them back
  for (const [attribute, value] of Object.entries(svgAttributes))
  {
    $svgElement.removeAttr(attribute);

    $svgElement.attr(attribute, attribute === 'class' ?
      `bi ${CLASSPREFIX}-${fileBasename}` : value
    );
  }

  const resultSvg = $svgElement.toString().replace(/\r\n?/g, '\n');

  if (resultSvg !== originalSvg)
  {
    await fs.writeFile(filepath, resultSvg, 'utf8');
  }

  loger.push([CLASSPREFIX, fileBasename]);
}

// const main = async () => {
module.exports.main = async () =>
{
  try
  {
    // 'build-svgs.js'
    const basename = path.basename(__filename);
    const timeLabel = chalk.cyan(`[${basename}] finished`);

    console.log(chalk.cyan(`[${basename}] started`));
    console.time(timeLabel);

    const config = await getSvgoConfig();
    let count = 0;

    for (let folderinfo of Folders)
    {
      // Absolute folder path.
      let FOLDER = folderinfo[0];

      // Subfolder name.
      let CLASSPREFIX = folderinfo[1];

      // Get all  files inside folder.
      let files = await fse.readdir(FOLDER);

      // Collector for svg files (abolute paths).
      let files2 = [];

      for (let file of files)
      {
        if (path.extname(file) == '.svg')
        {
          files2.push(FOLDER + '/' + file);
        }
      }

      if (files2.length)
      {
        count = count + files2.length;
        await Promise.all(files2.map(file => processFile(
          file, config, CLASSPREFIX
        )));
      }
    }

    console.log(chalk.green(`\nSuccess, ${count} icons prepped!`))
    console.timeEnd(timeLabel)
    console.log(chalk.green(loger.length));

    fse.writeFile(
      iconsDir + '/prepped-icons.txt',
      JSON.stringify(loger, null, 2)
    );

    console.log(chalk.red(`Be patient! A lot to copy now and so...`));

    // return true;
  } catch (error) {
    console.error(error)
    process.exit(1)
  }
}

//main()
