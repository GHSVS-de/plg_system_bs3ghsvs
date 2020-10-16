# plg_system_bs3ghsvs
Joomla system plugin to register and override JHtml helpers. And more.

Plugin for ghsvs.de templates.

Don't use it if you don't need it.

## npm/composer. Create new Joomla extension installation package
- Clone repository into your server environment (WSL or whatever).

- `cd /mnt/z/git-kram/plg_system_bs3ghsvs`

- Check/edit `/package.json` and add plugin `version` and further settings like `minimumPhp` and so on. Will be copied during build process into manifest XML.
- Check also versions of dependencies, devDependencies. `npm run g-npm-update-check` and `npm run g-ncu-override-json`
- Check/adapt versions in `/src/composer.json`. Something to bump in `vendor/`?

```
cd src/

composer outdated

OR

composer show -l
```
- both commands accept the parameter `--direct` to show only direct dependencies in the listing

### "Download" PHP packages into `/src/vendor/`

```
cd src/
composer install
```

OR
(whenever libraries in vendor/ shall be updated)

```
cd src/
composer update
```

### "Download" JS/CSS packages into `/node_modules`

- I you want to check first: `npm run g-npm-update-check`
- If you want to adapt package.json automatically first: `npm run g-ncu-override-json`


- `cd ..`
- `npm install`

OR

- `npm update`

. IGNORE: `npm WARN bootstrap@ requires a peer of popper.js@ but none is installed. You must install peer dependencies yourself.`

#### Only if you want to include conflicting, other versions parallel to current ones:

Let's say you have already a Bootstrap 4 dependency in root `/package.json` but want also to download BS3 for later copy actions:

- Edit `/others/package.json`
- `cd others`
- `npm install`
- `cd ..`
- Edit `/build.js` to also copy these "downloaded" files to `/src/media/` during build step.
 
### Build new Joomla package ZIP.

- `nvm use 12` or `nvm use 13` to get rid of f'ing messages of NodeJs 14 that nobody understands but the creators and JS professors.

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

