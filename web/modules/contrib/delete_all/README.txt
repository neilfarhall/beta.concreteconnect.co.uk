CONTENTS OF THIS FILE
---------------------
 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Drush commands
 * Maintainers


INTRODUCTION
------------

The Delete All module allows a user to delete all content and/or users from a
site. This is mainly a developer tool, which can come in handy in several cases
listed below.

The usual way to do this is to go to Administer > Content then select all the
nodes and delete them. This works if you have a handful of nodes only. If you
have hundreds or more of nodes, then it is not a practical solution.

Another option is to directly delete the nodes from the node table in the
database. This does not work properly, since there are also comments, and many
tables for add on modules that need to be cleaned.

This module allows an easy, administrative user-interface solution to these
problems.

Note that any nodes, comments, and all additions to nodes that contributed
modules may have added will be deleted. For users, any additional module data
will also be deleted.


REQUIREMENTS
------------

This module requires no modules outside of the Drupal core.


INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
   for further information.


CONFIGURATION
-------------

The module has no menu or modifiable settings. There is no configuration. When
enabled, the module adds new menu items to Configuration > Development
(admin/config/development):

 * Batch Delete Content (/admin/content/delete_content)
 * Batch Delete Account (/admin/people/delete_people)


DRUSH COMMANDS
--------------

Alternatively, this module adds drush commands:

 * delete-all-delete-content (dadc): deletes all content
 * delete-all-delete-entities (dade): deletes entities
 * delete-all-delete-users (dadu): deletes users


MAINTAINERS
-----------

 * Dipak Yadav (dipakmdhrm) - https://www.drupal.org/u/dipakmdhrm
 * Hammad Ghani (hammad-ghani) - https://www.drupal.org/u/hammad-ghani
 * Khalid Baheyeldin (kbahey) - https://www.drupal.org/u/kbahey
 * Brian Gilbert (realityloop) - https://www.drupal.org/u/realityloop
 * Kevin O'Brien (coderintherye) - https://www.drupal.org/u/coderintherye
 * Git Migration - https://www.drupal.org/u/git-migration
 * Doug Green (douggreen) - https://www.drupal.org/u/douggreen
