# Versatile Front [WIP]

This Laravel package adds frontend views, routes and assets to a Versatile project.

It comes with a basic structure for frontend layouts (eg. header, footer, etc) and theme assets using the [Foundation](https://foundation.zurb.com) framework.

---

Disclaimer (pt_BR)
==========
Este repositório ainda está em desenvolvimento. Contém partes do pacote [pvtl/voyager-frontend](https://github.com/pvtl/voyager-frontend).

---

## Prerequisites

- PHP >= 7.1.3
- Node & NPM
- Composer
- [Laravel Requirements](https://laravel.com/docs/installation)

---

## Installation

__1. Install Laravel + Versatile__
_(Replace the $VARs with your own values)_

```bash
# 1.0 Install Laravel
composer create-project --prefer-dist laravel/laravel $DIR_NAME

# 1.1 Require Versatile
cd $DIR_NAME && composer require versatilecms/core

# 1.2 Copy .env.example to .env and update the DB & App URL config
cp .env.example .env

# 1.3 Generate a Laravel key
php artisan key:generate

# 1.4 Run the Versatile Installer
php artisan versatile:install

# 1.5 Create a Versatile Admin User
php artisan versatile:admin $YOUR_EMAIL --create
```

__2. Install Versatile Frontend__

```bash
# 2.0 Require this Package in your fresh Laravel/Versatile project
composer require versatilecms/front

# 2.1 Run the Installer
composer dump-autoload && php artisan versatile-frontend:install

# 2.3 Build the front-end theme assets
npm run dev

# 2.4 Set the Laravel search driver in your .env
echo "SCOUT_DRIVER=tntsearch" >> .env
```

_Any issues? See [the troubleshooting section](#toubleshooting) below._

### 'Got Cron'?

This is a just a reminder to setup the standard Laravel cron on your server. The Versatile Frontend package has a few scheduled tasks, so relies on the cron running.

```
* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
```

---

## Thumbnails / Image Resizing

This package comes with an automatic image resize function. When you reference an image in your front-end blade templates, simply call something like:

```html
{{ image_url($pathToImage, $width, $height, $config = ['crop' => false, 'quality' => 100] ) ?: '/default.png' }}

<!-- For example (where $blockData->image = 'blocks/3hkkskd8.jpg'): -->
<img src="{{ image_url($blockData->image, 300) ?: '/default.png' }}" />
```

### "CDN" your images

The function will output an absolute URL, where the hostname will be `APP_URL` - however you can add a `ASSET_URL` variable to your `.env` file to use a different hostname.

---

## Search

#### Generating Indices
This module contains a scheduled job to regenerate indices which will run automatically once you setup jobs for Laravel. If you need to test and re-generate search indices you can manually run the command `php artisan versatile-frontend:generate-search-indices`.

#### Configuring Search (Using Laravel Scout)
By default this module includes "searching" the "Pages" and "Posts" Models out-of-the-box once you have defined the following variable in your `.env` file - [check out the Laravel Scout documentation](https://laravel.com/docs/5.5/scout):

```
SCOUT_DRIVER=tntsearch
```
 
 You can however extend and define your own "Searchable" Models to include in your search results by attaching the "Searchable" trait to them.

```php
class Page extends BaseModel
{
    use Searchable;

    public $asYouType = false;

    /**
     * Get the indexed data array for the model.
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();

        // customise the searchable array
        
        return $array
    }
}
```

Then you'll be able to hook into the search config and _merge_ your "Searchable" Models in with the config key (preferably using a Servie Provider): `scout.tntsearch.searchableModels`.
```php
$this->mergeConfigFrom($this->packagePath . 'path/to/config/scout.php', 'scout.tntsearch.searchableModels');
```

Your configuration file should contain values similar to this modules scout.php configuration:
```php
<?php

return [
    '\My\Searchable\Models\Namespace',
];
```
---
