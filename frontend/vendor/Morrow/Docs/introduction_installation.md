Installation {#installation}
============

Requirements
-------------

* Apache Webserver (mod_rewrite required)
* PHP >= 5.3 

Download
--------

First download the framework via SVN repository. You should know how. :)

Extract
-------

Extract the package to a folder of your choice below the document root of your webserver. You should have the following file structure:

* _config/ Configuration files of the framework.
* _core/ Important files of the framework. Here you should not change anything!
* _libs/ (opt.) External classes made by yourself.
* _logs/ Log files and error log files.
* main/ The default project of the framework.
    * _config/ Configuration files of the project.
    * _controller/ Controller files.
    * _forms/ Form definitions for the Form class.
    * _i18n/ Configuration and translations for the used languages.
    * _templates/ Templates for (X)HTML output.
    * _model/ (opt.) your models for the project
    * _libs/ (opt.) your libs for the project
    * images/ (opt.) Your images for the project.
    * temp/ Temporary files for the project.
    * xtras/ (opt.) All files which do not fit into another folder (like JS files, SWF files ...). 


Set permissions
---------------

 **main/temp** (and all subfolders) have to be readable & writable by the web server user If you have more than one project you have to repeat this step for each of them.

