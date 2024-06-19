# Directory Bundle

![GitHub release (with filter)](https://img.shields.io/github/v/release/Pixel-Mairie/sulu-directorybundle?style=for-the-badge)
[![Dependency](https://img.shields.io/badge/sulu-2.5-cca000.svg?style=for-the-badge)](https://sulu.io/)

## Presentation
A Sulu bundle to manage a directory.

## Features
* Directory management
* List of cards (via smart content)
* Preview
* Translation
* Settings
* SEO
* Activity log
* Trash

## Requirement
* PHP >= 8.0
* Sulu >= 2.4
* Symfony >= 5.4
* Composer

## Installation
### Install the bundle

Execute the following [composer](https://getcomposer.org/) command to add the bundle to the dependencies of your
project:

```bash
composer require pixelmairie/sulu-directorybundle
```

### Enable the bundle

Enable the bundle by adding it to the list of registered bundles in the `config/bundles.php` file of your project:

 ```php
 return [
     /* ... */
     Pixel\DirectoryBundle\DirectoryBundle::class => ['all' => true],
 ];
 ```

### Update schema
```shell script
bin/console do:sch:up --force
```

## Bundle Config

Define the Admin Api Route in `routes_admin.yaml`
```yaml
directory.directories_api:
  type: rest
  prefix: /admin/api
  resource: pixel_directory.directories_route_controller
  name_prefix: card.

directory.settings_api:
  type: rest
  prefix: /admin/api
  resource: pixel_directory.settings_route_controller
  name_prefix: directory.
``` 

## Use
### Add/Edit a card
Go to the "Directory" section in the administration interface. Then, click on "Add".
Fill the fields that are needed for your use.

Here is the list of the fields:
* Name (mandatory)
* Type of card (mandatory)
* URL (mandatory and filled automatically according to the title)
* Logo
* Category (mandatory)
* Images
* PDF
* Youtube video ID
* Website
* Email
* Phone number
* Facebook
* Instagram
* Twitter
* LinkedIn
* Description
* Location

Once you finished, click on "Save"

Your card is not visible on the website yet. In order to do that, click on "Activate?". It should be now visible for visitors.

To edit a card, simply click on the pencil at the left of the card you wish to edit.

### Categories
As you may have seen in the previous section, a card needs a category and a type. These categories and types need to be created in a very specific way.

For the types:
* You **must** create a root category (which represents the different types) which **must** have its key named "types"
* Then, under this root category, you create all the types you need

For the categories:
* You **must** create a root category which **must** have its key named "categories"
* Then, under this root category, you create all the categories you need

### Remove/Restore a gallery

There are two ways to remove a card:
* Check every card you want to remove and then click on "Delete"
* Go to the detail of a card (see the "Add/Edit a news" section) and click on "Delete".

In both cases, the card will be put in the trash.

To access the trash, go to the "Settings" and click on "Trash".
To restore a card, click on the clock at the left. Confirm the restore. You will be redirected to the detail of the card you restored.

To remove permanently a card, check all the cards you want to remove and click on "Delete".

## Settings

This bundle comes with settings. To access the bundle settings, go to "Settings > Cards management".

Here is the list of the different settings:
* Map centering
* Default image

The map centering is very useful if you have a global map showing all the card you registered. With that, you can center your map in order to see all the card in a given area.

The default image is helpful when a card has no logo for example.

## Twig extension
This bundle comes with only one twig function:

**directory_settings()**: returns the settings of the bundle. No parameters are required.

Example of use:
```twig
{% set settings = directory_settings() %}
{% if settings.defaultImage is defined %}
    {% set defaultImage = sulu_resolve_media(settings.defaultImage.id, 'fr') %}
{% endif %}
```

## Contributing
You can contribute to this bundle. The only thing you must do is respect the coding standard we implements.
You can find them in the `ecs.php` file.
