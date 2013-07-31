Extending MorrowTwo
====================

Sometimes you will reach a point where you need more power. No problem.
You can integrate your own classes, replace any file of the MorrowTwo framework or integrate other libraries via [Composer](http://getcomposer.org/).
If you don't have experience in using Composer, take a look at [Composer - Getting started](http://getcomposer.org/doc/00-intro.md).

Beside your `App` folder you will find those two folders:

* `vendor/` Maintained by Composer. Don't change anything here manually.
	* `composer.json` The Composer control file.
* `vendor_user/` The folder to replace Morrows core components or load Not-Composer-Libraries.



Working with Composer libraries
-------------------------------
We assume that you know what Composer and Packagist is and how to work with it.

If you have added `"michelf/php-markdown": "1.3.*"` to your composer.json file and have updated Composer, you can use it instantly like this:

~~~{.php}
<?php


namespace App;
use Morrow\Factory;
use Morrow\Debug;

class PageController extends DefaultController {
	public function run() {
		
		// use it directly
		$content = ...;
		$content = \Michelf\MarkdownExtra::defaultTransform($content);

		// or use the Factory to create an instance
		$content = ...;
		$content = Factory::load('\Michelf\MarkdownExtra')->defaultTransform($content);
	}
}
?>
~~~


Working with Not-Composer-Libraries
------------------------------------
Morrows Autoloader is PSR-0 compatible and first checks the `vendor_user` folder for the requested library and then the `vendor` folder.



This example integrates the PEAR Text_Diff into Morrow.
It shows how to integrate a PEAR class in a system where the PEAR installer is not available. The PEAR files in this example are located in `vendor_user/`.

We create a new file textdiff.class.php that corresponds to the naming and folder conventions of Morrow and extend the original class in that file.

**PROJECT_PATH/_libs/textdiff.class.php**

~~~{.php}
// PEAR needs its path set in the PHP include path. So we have to set the include_path manually.
 
set_include_path(PROJECT_PATH.'_libs/pear' . PATH_SEPARATOR . get_include_path() );
 
// include the PEAR class "Text_Diff"
require_once 'Text/Diff.php';
 
class textdiff extends Text_Diff {
	public function __construct($engine, $array1, $array2 ) {
		parent::Text_Diff($engine, array($array1, $array2));
	}
}
~~~

**Our controller file looks like this:**

~~~{.php}
class PageController extends GlobalController {
	public function run() {
		$this->load('textdiff','auto', $array1, $array2);
		// now do with your class what you want to
		...
	}
}
~~~

Integrate view handlers
------------------------

Imagine you need a new view handler to display for example RSS Feeds, automated SQL export scripts, automatically computed graphs and so on. This is really simple to implement in Morrow.

There is a naming convention you have to follow. A view handler class has to start with the string view.

Let us take a look at the viewPlain handler which has the smallest code base. At the moment this handler returns just a scalar variable that was passed via $this->view->setContent().

~~~{.php}
class Viewplain extends ViewAbstract {
	public $mimetype        = 'text/plain';
	public $charset         = 'iso-8859-1';
 
	public function getOutput($content) {
		if (!is_scalar($content['content'])) {
			trigger_error(__class__.': The content variable for this handler has to be scalar.', e_user_error); return false;
		}
		return $content['content'];
	}
}
~~~

You always have to extend Viewabstract which is the base for all view handlers. It also makes sure that the following method exists.

All members you declare as public are changeable via $this->view->setProperty() in the controller.

### getOutput()

~~~{.php}
array function getOutput( array $content )
~~~

Returns the final output of this handler. The array $content contains all variables which was passed via $this->view->setContent(). If you have not passed a key for setContent in your controller, then you will find your content in the standard key "content".

Integrate filters
-----------------

There is a naming convention you have to follow. A filter class has to start with the string filter.
The following construct is the base for all new filters.

~~~{.php} 
class FilterYourname extends FilterAbstract {
	protected $params = array();    

	public function __contruct($params) {
		$this->params = $params:
	}
	
	public function get($content) {
		
		// change the content here

		return $content;
	}
}
~~~

To set the filter, call the following function in your controller.

~~~{.php}
$this->view->setFilter('yourname', $your_params);
~~~
