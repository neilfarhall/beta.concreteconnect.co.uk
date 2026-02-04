CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

This module Integrates the Font Awesome Iconpicker with Drupal.

 * For a full description of the module visit:
   https://www.drupal.org/project/fontawesome_iconpicker

 * To submit bug reports and feature suggestions, or to track changes visit:
   https://www.drupal.org/project/issues/fontawesome_iconpicker


REQUIREMENTS
------------

This module requires the following libraries:

 * Font Awesome Library - http://fontawesome.io
 * Vanilla icon picker Library - https://github.com/AppoloDev/vanilla-icon-picker

INSTALLATION
------------

## Composer Installaion

1. Adjust your root composer.json to include vanilla-icon-picker library as shown in the highlighted section of this gist https://gist.github.com/d34dman/a4b480a3471afc86a7951aadc7a42dd1#file-composer-json-L16-L27
2. Make sure you have `druapl-library` installer path configured to point to your libraries folder. For this you would need `composer/installers` plugin and configure your composer.json as shown in the highlighted section here https://gist.github.com/d34dman/a4b480a3471afc86a7951aadc7a42dd1#file-composer-json-L67-L69
3. Run `composer require drupal/fontawesome_iconpicker ^3`
4. The above Add the Drupal module and the vanilla-icon-picker library in the correct location.

Install the Font Awesome Iconpicker module as you would normally install a contributed Drupal module.
Visit https://www.drupal.org/node/1897420 for further information.

CONFIGURATION
-------------

    1. Navigate to Administration > Extend and enable the module.
    2. Font Awesome Iconpicker can be used on "Text (formatted)" fields or "Text
       (plain)" fields.
    3. After attaching either of the above fields to an entity, the user is
       able to use the "Fontawesome Icon Picker" widget for those fields under
       the "Manage form display" section. The same should be done for "Manage
       Display".
    4. Save configurations.


MAINTAINERS
-----------

 * Shibin Das (D34dMan) - https://www.drupal.org/u/d34dman

Supporting organizations:

 * Factorial GmbH - https://www.drupal.org/factorial-gmbh
 * Digitwings - https://www.drupal.org/digitwing
