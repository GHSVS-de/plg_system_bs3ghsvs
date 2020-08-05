#!/usr/bin/env node

'use strict'

const fs = require('fs').promises;

// Keine Ahnung, warum ich das extra brauche, wenn oben "promises" steht.
// Dann schalgen einfachste Aufrufe der Art fs.existsSync() fehl.
const fse = require('fs-extra');
const path = require('path')
const chalk = require('chalk')
const cheerio = require('cheerio')
const SVGO = require('svgo')
const yaml = require('js-yaml')

const iconsDir = path.join(__dirname, '../src/media/svgs');

var loger = [];

if (!fse.existsSync(iconsDir))
{
	console.log(chalk.keyword('orange')(`Couldn't read directory ${iconsDir}! In file ${path.basename(__filename)}`));
	process.exit(1);
}

// Collect all folders in a directory recursively in a synchronous fashion
var walkSync = function(dir, filelist)
{
	let files = fse.readdirSync(dir);
	filelist  = filelist || [];
  
	files.forEach(function(file)
	{
		let Absolute = `${dir}/${file}`;

    if (fse.statSync(Absolute).isDirectory() && fse.readdirSync(Absolute).length > 0)
		{
			let Relative = Absolute.replace(`${iconsDir}/`, "").toLowerCase().replace(/\//g, '-');
			filelist.push([Absolute, Relative]);
      filelist = walkSync(Absolute, filelist);
    }
    else
		{
      //filelist.push(dir+file);
    }
  });
  return filelist;
};

let Folders = walkSync(iconsDir);

if (Folders.length > 0)
{
	Folders.push([iconsDir, ""]);
}
else
{
	console.log(chalk.keyword('orange')(`Directory ${iconsDir} is empty! In file ${path.basename(__filename)}`));
	process.exit(1);
}

// console.log(Folders);
// process.exit(1);

const svgAttributes = {
  class: '',
  fill: 'currentColor',
  xmlns: 'http://www.w3.org/2000/svg'
}

const getSvgoConfig = async () => {
  try {
    let svgoConfig = await fs.readFile(path.join(__dirname, '../svgo.yml'), 'utf8')

    svgoConfig = await yaml.safeLoad(svgoConfig)

    return svgoConfig
  } catch (error) {
    console.error('Couldn\'t read SVGO\'s config!')
    console.error(error)
    process.exit(1)
  }
}

const processFile = (file, config, CLASSPREFIX) => new Promise((resolve, reject) => {

  fs.readFile(file, 'utf8')
    .then(data => {
      const svgo = new SVGO(config)

      svgo.optimize(data)
        .then(result => {

result.data = result.data.replace(/[\n\r]/g, '');

          const $ = cheerio.load(result.data)
          const svg = $('svg')

          svg.replaceWith(() => $('<svg>').append($(this).html()))

          for (const [attr, val] of Object.entries(svgAttributes)) {
            $(svg).removeAttr(attr)
            $(svg).attr(attr, val)
          }

          //const dimensions = $(svg).attr('viewBox').split(' ')
          //const svgWidth = dimensions[2] / 16
          //const svgHeight = dimensions[3] / 16
					
					const svgWidth = 1;
					const svgHeight = 1;

          $(svg).attr('width', `${svgWidth}em`)
          $(svg).attr('height', `${svgHeight}em`)

          // Todo: Pass argument to script to flip between ems and pixels.
          // Until then, leaving code here—font-family generation requires
          // use of pixels.

          // const svgWidth = dimensions[2]
          // const svgHeight = dimensions[3]

          // $(svg).attr('width', `${svgWidth}`)
          // $(svg).attr('height', `${svgHeight}`)


          // $(svg).attr('class', `bi bi-${path.basename(file, '.svg')}`)
					
					$(svg).attr('class', `bi ${CLASSPREFIX}-${path.basename(file, '.svg')}`)

          fs.writeFile(file, $(svg), 'utf8')
            .then(() => {
              //console.log(`- ${path.basename(file, '.svg')}`)
							loger.push([CLASSPREFIX, path.basename(file, '.svg')]);
              resolve()
            })
            .catch(error => reject(error))
        })
        .catch(error => reject(error))
    })
    .catch(error => reject(error))
})

const main = async () => {
  const basename = path.basename(__filename)
  const timeLabel = chalk.cyan(`[${basename}] finished`)

  console.log(chalk.cyan(`[${basename}] started`))
  console.time(timeLabel)
  const config = await getSvgoConfig();
	let count = 0;

	for (let folderinfo of Folders)
	{
		let FOLDER = folderinfo[0];
		let CLASSPREFIX = folderinfo[1];
		let files = await fse.readdir(FOLDER);
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
			await Promise.all(files2.map(file => processFile(file, config, CLASSPREFIX)));
		}
	}
  console.log(chalk.green(`\nSuccess, ${count} icons prepped!`))
  console.timeEnd(timeLabel)
	
	console.log(chalk.green(loger.length));
	fse.writeFile(iconsDir + '/prepped-icons.txt', JSON.stringify(loger, null, 2));
}

main()
