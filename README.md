# plg_system_bs3ghsvs
Joomla system plugin to register and override JHtml helpers. And more.

Plugin for ghsvs.de templates.

Don't use it if you don't need it.

## npm/composer. Create new Joomla extension installation package.

- Check `/package.json` and add plugin `version` and further settings like `minimumPhp` and so on. Will be copied during build process into manifest XML.
- Check also versions of dependencies.
- Check versions in `/src/composer.json`.

### "Download" PHP packages into `/src/vendor/`

```
cd src
composer install

OR

composer update
```

### "Download" JS/CSS packages into `/node_modules`
- `cd ..`
- `npm install`

#### Only if you want to include conflicting, other versions parallel to current ones.

Let's say you have already a Bootstrap 4 dependency in root `/package.json` but want also to download BS3 for later copy actions:

- Edit `/others/package.json`
- `cd others`
- `npm install`
- `cd ..`
- Edit `/build.js` to copy the downloaded files to `/src/media/` during build step.
 
### Build new Joomla package ZIP.

- `node build.js`
- New ZIP is in `/dist/`
- Create release with tag.
- Get download link inside tag release and add to release description.

