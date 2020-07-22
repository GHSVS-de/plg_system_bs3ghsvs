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

`node build.js`

new ZIP is in `/dist/`

Create release with tag.

Get download link inside tag release and add to release description.

