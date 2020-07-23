# plg_system_bs3ghsvs
Joomla system plugin to register and override JHtml helpers. And more.

Plugin for ghsvs.de templates.

Don't use it if you don't need it.

## npm/composer. Create new release.

check `package.json` and add plugin `version` and further settings like `minimumPhp` and so on. 

```
cd src
composer install
```
(if not done yet and `/vendor/` doesn't exist yet)

or

`composer update`
(for `/vendor/` updates)

`cd ..`

`npm install` (if not done yet)

### Only if you want to include conflicting, other versions parallel to current ones.

Let's say you have already a Bootstrap 4 dependency in root `package.json` but want also to download BS3:

- Edit `/others/package.json`
- `cd others`
- `npm install`
- `cd ..`
- Edit /build.js to copy the downloaded files to /src/media/.
 
### Go on:

`node build.js`

New ZIP is in `/dist/`

Create release with tag.

Get download link inside tag release and add to release description.

