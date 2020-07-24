/*const {
  //copy,
  //exists,
  //mkdir,
  //readFile,
  unlink: unl,
  // writeFile,
} = require("fs-extra");*/

const Fs = require('fs-extra');
// const Path = require('path');

const util = require("util");
const rimRaf = util.promisify(require("rimraf"));
const {
	version,
	minimumPhp,
	maximumPhp,
	minimumJoomla,
	maximumJoomla,
	allowDowngrades,
} = require("./package.json");

const RootPath = process.cwd();

(async function exec()
{
	// Remove old folders.
  await rimRaf("./dist");
  await rimRaf("./package");
	
	await rimRaf("./src/media/fontawesome-free");
  await Fs.copy(
		"./node_modules/@fortawesome/fontawesome-free",
		"./src/media/fontawesome-free"
	);
	// await unl("./src/media/fontawesome-free/composer.json");
	
	await Fs.copy(
		"./node_modules/bootstrap/dist/js",
		"./src/media/js/bootstrap"
		// ,
		// {overwrite:false, errorOnExist:true}
	);
	
	await Fs.copy(
		"./node_modules/bootstrap/dist/css",
		"./src/media/css/bootstrap"
		// ,
		// {overwrite:false, errorOnExist:true}
	);
	
	await Fs.copy(
		"./node_modules/jquery/dist",
		"./src/media/js/jquery"
		// ,
		// {overwrite:false, errorOnExist:true}
	);
	
	await Fs.copy(
		"./node_modules/jquery-migrate/dist",
		"./src/media/js/jquery-migrate"
		// ,
		// {overwrite:false, errorOnExist:true}
	);

	await rimRaf("./src/versions-installed");

	await Fs.copy(
		"./package-lock.json",
		"./src/versions-installed/npm_package-lock.json"
		// ,
		// {overwrite:false, errorOnExist:true}
	);

	await Fs.copy(
		"./src/vendor/composer/installed.json",
		"./src/versions-installed/composer_installed.json"
		// ,
		// {overwrite:false, errorOnExist:true}
	);

	// Copy and create new work dir.
  await Fs.copy("./src", "./package");

	// Create new dist dir.
  if (!(await Fs.exists("./dist")))
	{
    await Fs.mkdir("./dist");
  }

  let xml = await Fs.readFile("./package/bs3ghsvs.xml", { encoding: "utf8" });
  xml = xml.replace(/{{version}}/g, version);
	xml = xml.replace(/{{minimumPhp}}/g, minimumPhp);
	xml = xml.replace(/{{maximumPhp}}/g, maximumPhp);
	xml = xml.replace(/{{minimumJoomla}}/g, minimumJoomla);
	xml = xml.replace(/{{maximumJoomla}}/g, maximumJoomla);
	xml = xml.replace(/{{allowDowngrades}}/g, allowDowngrades);

  Fs.writeFileSync("./package/bs3ghsvs.xml", xml, { encoding: "utf8" });
  Fs.unlinkSync("./package/composer.json");
  Fs.unlinkSync("./package/composer.lock");
	
	let directory = `${RootPath}/package/media/fontawesome-free`;
	Fs.unlinkSync(`${directory}/package.json`);
	
	let folders = [
		"js",
		"less",
		"metadata",
	];

	folders.forEach((file) => {
		file = `${directory}/${file}`;
	
		if (Fs.existsSync(file) && Fs.lstatSync(file).isDirectory())
		{
			// rimRaf(file, ["rmdirSync"]);
			rimRaf(file);
			console.log(`rimrafed: ${file}`);
		}
	});

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
	
		if (Fs.existsSync(file) && Fs.lstatSync(file).isFile())
		{
			Fs.unlinkSync(file);
			console.log(`Unlinked: ${file}`);
		}
	});

  // Package it
  const zip = new (require("adm-zip"))();
  zip.addLocalFolder("package", false);
  zip.writeZip(`dist/plg_system_bs3ghsvs-${version}.zip`);

})();
