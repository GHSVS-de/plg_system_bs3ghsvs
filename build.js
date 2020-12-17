const fse = require('fs-extra');
const util = require("util");
const rimRaf = util.promisify(require("rimraf"));

const Manifest = "./package/bs3ghsvs.xml";

const {
	author,
	creationDate,
	copyright,
	name,
	version,
	licenseLong,
	minimumPhp,
	maximumPhp,
	minimumJoomla,
	maximumJoomla,
	allowDowngrades,
} = require("./package.json");

const Program = require('commander');

const RootPath = process.cwd();

Program
  .version(version)
  .option('--svg', 'Additionally prepare svgs in /svg-icons/ for Joomla usage')
  .on('--help', () => {
    // eslint-disable-next-line no-console
    console.log(`Version: ${version}`);
    process.exit(0);
  })
  .parse(process.argv);

(async function exec()
{
	const firstCleanOuts = [
		`./package`,
		`./dist`,
		"./src/media/fontawesome-free",
		"./src/media/scss/bootstrap",
		"./src/versions-installed"
	];

	for (const file of firstCleanOuts)
	{
		await rimRaf(file).then(
			answer => console.log(`rimrafed: ${file}.`)
		);
	}

	let source = "./node_modules/@fortawesome/fontawesome-free";
	let target = "./src/media/fontawesome-free";

  await fse.copy(source, target
	).then(
		answer => console.log(`Copied ${source} to ${target}.`)
	);

	source = "./node_modules/bootstrap/dist/js";
	target = "./src/media/js/bootstrap";

  await fse.copy(source, target
	).then(
		answer => console.log(`Copied ${source} to ${target}.`)
	);

	source = "./node_modules/bootstrap/scss";
	target = "./src/media/scss/bootstrap";

  await fse.copy(source, target
	).then(
		answer => console.log(`Copied ${source} to ${target}.`)
	);

	source = "./node_modules/bootstrap/dist/css";
	target = "./src/media/css/bootstrap";

  await fse.copy(source, target
	).then(
		answer => console.log(`Copied ${source} to ${target}.`)
	);
	
	await fse.copy(
		"./node_modules/jquery/dist",
		"./src/media/js/jquery"
		// ,
		// {overwrite:false, errorOnExist:true}
	);
	
	await fse.copy(
		"./node_modules/jquery-migrate/dist",
		"./src/media/js/jquery-migrate"
		// ,
		// {overwrite:false, errorOnExist:true}
	);

	await fse.copy(
		"./package-lock.json",
		"./src/versions-installed/npm_package-lock.json"
	).then(
		answer => console.log(`Copied ./package-lock.json.`)
	);

	await fse.copy(
		"./src/vendor/composer/installed.json",
		"./src/versions-installed/composer_installed.json"
		// ,
		// {overwrite:false, errorOnExist:true}
	);

	if (Program.svg)
	{
		console.log(`Program.svg: YES`);

		await rimRaf("./src/media/svgs");

  		await fse.copy(
			"./node_modules/@fortawesome/fontawesome-free/svgs",
			"./src/media/svgs"
		);
	
  		await fse.copy(
			"./node_modules/bootstrap-icons/icons",
			"./src/media/svgs/bi"
		);

		const buildSvgs = require('./build/build-svgs.js');
		await buildSvgs.main();
	}

	// Copy and create new work dir.
  await fse.copy("./src", "./package"
	).then(
		answer => console.log(`Copied ./src to ./package.`)
	);

	// Create new dist dir.
  if (!(await fse.exists("./dist")))
	{
    await fse.mkdir("./dist"
		).then(
			answer => console.log(`Created ./dist.`)
		);
  }

  let xml = await fse.readFile(Manifest, { encoding: "utf8" });
	xml = xml.replace(/{{name}}/g, name);
	xml = xml.replace(/{{nameUpper}}/g, name.toUpperCase());
	xml = xml.replace(/{{authorName}}/g, author.name);
	xml = xml.replace(/{{creationDate}}/g, creationDate);
	xml = xml.replace(/{{copyright}}/g, copyright);
	xml = xml.replace(/{{licenseLong}}/g, licenseLong);
	xml = xml.replace(/{{authorUrl}}/g, author.url);
  xml = xml.replace(/{{version}}/g, version);
	xml = xml.replace(/{{minimumPhp}}/g, minimumPhp);
	xml = xml.replace(/{{maximumPhp}}/g, maximumPhp);
	xml = xml.replace(/{{minimumJoomla}}/g, minimumJoomla);
	xml = xml.replace(/{{maximumJoomla}}/g, maximumJoomla);
	xml = xml.replace(/{{allowDowngrades}}/g, allowDowngrades);
	
	await fse.writeFile(Manifest, xml, { encoding: "utf8" }
	).then(
		answer => console.log(`Replaced entries in ${Manifest}.`)
	);;
	
	// HOUSE CLEANING	
	let directory = `${RootPath}/package/media/fontawesome-free`;
	fse.unlinkSync(`${directory}/package.json`);
	
	let folders = [
		"js",
		"less",
		"metadata",
	];

	for (let file of folders)
	{
		file = `${directory}/${file}`;
		if (fse.existsSync(file) && fse.lstatSync(file).isDirectory())
		{
			await rimRaf(file).then(
				answer => console.log(`rimrafed: ${file}.`)
			);
		}
	};

	// Für weiter vendor-Ordner zu faul!!!
	directory = `${RootPath}/package/vendor/spatie/schema-org`;
	
	await rimRaf(`${directory}/.github`);
	
	let files = [
		".editorconfig",
		".styleci.yml",
		"composer.json",
		"CHANGELOG.md",
		"CONTRIBUTING.md",
		"README.md",
		"provides.json"
	];

	files.forEach((file) => {
		file = `${directory}/${file}`;
	
		if (fse.existsSync(file) && fse.lstatSync(file).isFile())
		{
			fse.unlinkSync(file);
			console.log(`Unlinked: ${file}`);
		}
	});
	
  fse.unlinkSync("./package/composer.json");
  fse.unlinkSync("./package/composer.lock");
	fse.unlinkSync("./package/media/js/jquery-migrate/.eslintrc.json");

  // Package it
  const zip = new (require("adm-zip"))();
  zip.addLocalFolder("package", false);
  zip.writeZip(`dist/plg_system_bs3ghsvs-${version}.zip`);

})();