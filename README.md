# plg_system_bs3ghsvs
Joomla system plugin to register and override JHtml helpers. And more.

Plugin for ghsvs.de templates.

Don't use it if you don't need it.

## npm/composer. Create new Joomla extension installation package
- Clone repository into your server environment (WSL or whatever).
- Check/edit `/package.json` and add plugin `version` and further settings like `minimumPhp` and so on. Will be copied during build process into manifest XML.
- Check also versions of dependencies, devDependencies.
- Check versions in `/src/composer.json`.

### "Download" PHP packages into `/src/vendor/`

```
cd src
composer install
```

OR
(whenever libraries in vendor/ have been updated)

```
cd src
composer update
```

### "Download" JS/CSS packages into `/node_modules`
- `cd ..`
- `npm install`

OR

- `npm update`


#### Only if you want to include conflicting, other versions parallel to current ones:

Let's say you have already a Bootstrap 4 dependency in root `/package.json` but want also to download BS3 for later copy actions:

- Edit `/others/package.json`
- `cd others`
- `npm install`
- `cd ..`
- Edit `/build.js` to also copy these "downloaded" files to `/src/media/` during build step.
 
### Build new Joomla package ZIP.

#### Whenever Bootstrap/icons or fontawesome/icons have been updated while `npm update`
- `node build.js --svg` (to create embeddable icons in `media/svgs/`)

#### else
- `node build.js`

##### 
- New ZIP is in `/dist/`
- FYI: Packed files for this ZIP can be seen in `/package/`.

#### For Joomla update server
- Create new release with new tag.
- Get download link for new `dist/plg_blahaba_blubber...zip` **inside new tag branch** and add to release description and update server XML.

