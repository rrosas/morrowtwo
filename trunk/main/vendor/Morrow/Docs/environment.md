Environment information
=======================

The \Morrow\Page class is provided by the framework and gives you info we think could be very helpful on generating templates.
Its output is automatically passed to the view handler.

Here is the content of the current page:

~~~
Array
(
    [nodes] => Array
        (
            [0] => page
            [1] => environment
        )

    [nodes_redirected] => Array
        (
            [0] => page
        )

    [base_href] => http://domain.com/
    [alias] => page
    [controller] => /var/www/morrowtwo/docs/app/page.php
    [path] => page/environment/
    [fullpath] => page/environment/
)
~~~

In the controller you have access to the page array by the \Morrow\Page class.

~~~{.php} 
<?php
namespace App;
use Morrow\Factory;
use Morrow\Debug;

class PageController extends DefaultController {
    public function run() {
        // Dump the contents of the page class
        Debug::dump($this->page->get());
    }
}
?>
~~~
