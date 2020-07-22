const {
  copy,
  exists,
  mkdir,
  readFile,
  unlink: unl,
  writeFile,
} = require("fs-extra");
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

(async function exec()
{
	// Remove old folders.
  await rimRaf("./dist");
  await rimRaf("./package");
	
	await rimRaf(".src/media/fontawesome-free");
  await copy("./node_modules/@fortawesome/fontawesome-free", "./src/media/fontawesome-free");
	// await unl("./src/media/fontawesome-free/composer.json");

	// Copy and create new work dir.
  await copy("./src", "./package");

	// Create new dist dir.
  if (!(await exists("./dist"))) {
    await mkdir("./dist");
  }

  let xml = await readFile("./package/bs3ghsvs.xml", { encoding: "utf8" });
  xml = xml.replace(/{{version}}/g, version);
	xml = xml.replace(/{{minimumPhp}}/g, minimumPhp);
	xml = xml.replace(/{{maximumPhp}}/g, maximumPhp);
	xml = xml.replace(/{{minimumJoomla}}/g, minimumJoomla);
	xml = xml.replace(/{{maximumJoomla}}/g, maximumJoomla);
	xml = xml.replace(/{{allowDowngrades}}/g, allowDowngrades);

  await writeFile("./package/bs3ghsvs.xml", xml, { encoding: "utf8" });
  await unl("./package/composer.json");
  await unl("./package/composer.lock");

  // Package it
  const zip = new (require("adm-zip"))();
  zip.addLocalFolder("package", false);
  zip.writeZip(`dist/plg_system_bs3ghsvs-${version}.zip`);

})();
