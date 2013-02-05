Debugging {#debugging}
=============================

Debugging is of course one of the most interesting topics while developing. Morrow gives you a simple and clean way to debug your applications.

Debug::Dump
-----------

The most interesting tool is Morrow's system wide replacement for print_r() and var_dump(). It returns a nice layout with a lot more of information than other tools. For example where you did the call. Never forget anymore where you have placed your debugging call. Just try out.

~~~{.php
// ... controller code
 
Debug::dump($_SERVER);
 
// ... controller code
~~~

If you are in an other namespace (e.g. if you are working in a Model class) than `Morrow` you have to call the fully namespaced class path:

~~~{.php
\Morrow\Debug::dump($_SERVER);
~~~

Or use the `use` function to import the Debug class in your current namespace.

~~~{.php
use Morrow\Debug
~~~


Errors & Exceptions
-------------------

Morrow's preferred way is to work with exceptions. For that reason errors throw an exception, so you can catch them as you would do with normal exceptions. Furthermore we integrated a state-of-the-art-top-level-exception-handler.

~~~{.php
// ... controller code
 
try {
    echo $undefined; // yes, the variable undefined was not defined before
} catch (Exception $e) {
    die($e->getMessage());
}
 
// ... controller code
~~~

Sometimes you want to do something if ANY error occurs.
Use the following construct to define actions which should take place after the default exception handling.
The best place for this snippet is the first line in the setup() method of your DefaultController. Otherwise all code which throws exceptions before this line would not trigger your actions.

~~~{.php}
$this->debug->setAfterException(function($exception) {
	// your actions
	$this->url->redirect('error/');	
});
~~~


Configuration
--------------

~~~{.php
return array(

// debug
	'debug.screen'			=> true,
	'debug.flatfile'		=> false,
	'debug.die_on_error'	=> true,

);
~~~

Time Handling
--------------

Sometimes it is useful to check several time phases of a project, e.g. for a raffle.
It is helpful to instantiate a DateTime object in the DefaultController so you can imitate every date in your project.

~~~{.php}
Factory::prepare('\DateTime', '2012-03-15');

// Output the current fake date formatted
$now_formatted = Factory::load('datetime')->format('Y-m-d H:i:s');
Debug::dump($now_formatted);

// add a day to the fake date
Factory::load('datetime')->modify('+1 day');

// get the timestamp for the fake date +1 day
$tomorrow_timestamp = Factory::load('datetime')->getTimestamp();
Debug::dump($tomorrow_timestamp);
~~~
