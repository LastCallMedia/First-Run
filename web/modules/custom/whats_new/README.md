INTRODUCTION
------------
 
 The Whats New module provides a conduit for developers to directly communicate with users regarding new site features, 
 module updates, or other useful information. A page is provided at /admin/whats-new that displays this developer 
 communicated information and a Whats New block is provided that displays the most recent updates. The number 
 of updates displayed by the block is adjustable. This communication is achieved with a single yaml file that the 
 developer updates to communicate new and relevant site changes.

REQUIREMENTS
------------

 No additional modules are required.

INSTALLATION
------------

 1. Install as you would normally install a contributed Drupal module. Visit:
 https://www.drupal.org/docs/8/extending-drupal-8/overview
 for further information.
   
 2. Create a whats_new.yml file at the Drupal root. Please see the example.whats_new.yml file for instructions and 
 examples for how to enter information on this file.

CONFIGURATION
-------------

 - Permissions
   * The 'access whats_new page' permission is required to access the /admin/whats-new page.

 - See the example.whats_new.yml file for examples on how to fill out the yml file.
