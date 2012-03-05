Plugin Manager 1.0

Developed by:
simpilot - David Clark
www.simpilotgroup.com
www.david-clark.net

Developed using phpVMS ver 2.1.934-164

Released under the following license:
Creative Commons Attribution-Noncommercial-Share Alike 3.0 Unported License

---------------------------------------------------------------------------------
Features
-------------------------------

Creates admin interface for uploading, installing, un-installing, and deleting phpVMS Plugin's.

---------------------------------------------------------------------------------
Installation
-------------------------------
1 - Download the zip package

2 - Unzip the package and upload all the contents of the admin folder to the admin folder of your site.

3 - Goto your admin panel and there will be a link for the Plugin Manager under Addon's.

---------------------------------------------------------------------------------
Plugin Structure for developers
-------------------------------
config.txt
-------------------------------

A plugin must have a config.txt file with the basic structure from below;

plugin=Your Plugin Name
author=Your Name
email=Your Email
version=1.0
published=3/1/2012
license=Your License Type
link=Link To Your Site

-------------------------------
sql file
-------------------------------

Any sql database file must be included in the root of the plugin folder.

-------------------------------
readme file
-------------------------------

A readme.txt file can be structured something similar to below and is placed in the root of the folder.

AirMail 3.0

phpVMS module to create a messaging system your phpVMS based virtual airline.

Released under the following license:
Creative Commons Attribution-Noncommercial-Share Alike 3.0 Unported License

Developed by:
simpilot
www.simpilotgroup.com

Developed on:
phpVMS v2.1.934-158
php 5.3.4
mysql 5.0.7
apache 2.2.17

This system is not compatible with any earlier versions of AirMail

New Features:

-Delete All function in inbox and all message folders
-Individual pilot setting to have email sent to pilot when new message is received
-Threaded messages

Install:

-Download the attached package.
-unzip the package and place the files as structured in your root phpVMS install.
-use the airmail.sql file to create the tables needed in your sql database using phpmyadmin or similar.

To Use the "You Have Mail" function place the following code where you would like the notice to appear, it will only appear if the pilot is logged in.

<?php MainController::Run('Mail', 'checkmail'); ?>

-Create a link on your site for your pilots to access their AIRMail

<a href="<?php echo url('/Mail'); ?>">AIRMail</a>

-------------------------------
phpVMS files
-------------------------------

All the module files for the plugin must be in a file tree just as the system is structured

an example:

My_New_module

-my_new_module.sql
-readme.txt
-config.txt
-license.txt
-core
--common
----My_New_ModuleData.class.php
--modules
----My_New_Module
------My_New_Module.php
--templates
----my_modules_templates
------index.tpl
------example.tpl

zip this structure into one one folder ie - My_new_module. Be sure not to create a folder within a folder.