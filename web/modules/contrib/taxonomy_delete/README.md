CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers

INTRODUCTION
------------

A utility module which will delete all taxonomy terms in a vocabulary. Deleting
taxonomies can be a very frustrating issue specially when there are a lot to
delete for testing purposes.

The module provides an UI where you can select the Vocabulary from which the
taxonomy has to be deleted. Additionally for developers there is a Drush command
which will delete all taxonomy terms from a Vocabulary.

for more info visit:
https://www.drupal.org/project/taxonomy_delete

REQUIREMENTS
------------

This module does not have any dependency.

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. See:
  https://www.drupal.org/docs/extending-drupal/installing-modules
  for further information.

CONFIGURATION
-------------

* Go to "/admin/modules" and enable "Taxonomy delete" module;
* Go to "/admin/structure/taxonomy/taxonomy-delete" and select the vocubalary for which
  you want to delete the taxonomy terms.

DRUSH USAGE
-----------

 This modules supports drush to delete all taxonomy terms from a vocabulary or multiple vocabularies

   * drush taxonomy-delete:term-delete {vocabulary-name-1},{vocabulary-name-2} -  To delete all taxonomy terms from
     the specified vocabulary(ies)

     using alias
  *  drush tdel {vocabulary-name} 
  

MAINTAINERS
-----------

Current maintainers:
 * Malabya Tewari (malavya) - https://www.drupal.org/u/malavya
 * Viktor Holovachek (AstonVictor) - https://www.drupal.org/u/astonvictor
