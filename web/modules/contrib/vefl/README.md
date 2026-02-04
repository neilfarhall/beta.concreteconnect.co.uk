CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Credits
 * Maintainers


INTRODUCTION
------------
Provides an ability to output Views exposed filters in layouts.

 * For a full description of the module, visit the project page:
   https://drupal.org/project/vefl

 * To submit bug reports and feature suggestions, or to track changes:
   https://drupal.org/project/issues/vefl


FEATURES
--------
 * Provides Default and supports Panels layouts.
 * You can define custom layouts with regions.
 * You can override exposed form template as usual.
 * Works with **Exposed form in block**.
 * Supports Better exposed filters module.


INSTALLATION
------------
Install as you would normally install a contributed drupal module.
See: https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
for further information.
Enable **Better exposed filters layout** module if you want use layouts with
**Better exposed filters**
(https://www.drupal.org/project/better_exposed_filters) module.


CONFIGURATION
-------------
* For site-builders:
  -On views edit page find Exposed form section.
  -Choose Basic (with layout) or Better Exposed Filters (with layout)
   Exposed form style.
  -Exposed form settings form find Layout settings fieldset.
  -Choose Layout and click Change. Do you need more default layouts?
  -Set in which region each exposed filter will be outputted.
  -Click Apply and have fun.
* For developers:
  -You can define custom layouts.
  -You can override exposed form template as usual:
  -In your theme define views-exposed-form.html.twig,
   use $region_widgets variable to output widgets by regions.
  -views-exposed-form--VIEWNAME.html.twig or
   views-exposed-form--VIEWNAME--DISPLAYNAME.html.twig also work.

MAINTAINERS
-----------
Current maintainers:
 * Sergey Korzh (skorzh) - https://drupal.org/user/813560
 * Dima Iluschenko (dima.iluschenko) - https://www.drupal.org/u/dimailuschenko
