Environment information
=======================

There are some constants provided from the framework that could be useful for you:


Constant           | Description
------------------ | ------------
`PUBLIC_PATH`      | The absolute path to the public folder (`.../main/App/public/`)
`APP_PATH`         | The absolute path to the App folder (`.../main/App/`)
`STORAGE_PATH`     | The absolute path to the storage folder (`.../main/App/storage/`)
`FW_PATH`          | The absolute path to the framework folder (`.../main/`)
`VENDOR_PATH`      | The absolute path to the vendor folder (`.../main/vendor/`)
`VENDOR_USER_PATH` | The absolute path to the vendor_user folder (`.../main/vendor_user/`)

As in these constants all classes expect folder paths to have a trailing slash.

The Page class
--------------

The \Morrow\Page class is provided by the framework and gives you info we think could be very helpful on generating templates.
Its output is automatically passed to the view handler.

Here is the content of the current page:

~~~

Array
(
    ['nodes'] => Array (2)
    (
        ['0'] = String(4) "page"
        ['1'] = String(11) "environment"
    )
    ['base_href'] = String(31) "http://ce/morrowtwo/trunk/docs/"
    ['alias'] = String(4) "page"
    ['path'] => Array (4)
    (
        ['relative'] = String(16) "page/environment"
        ['relative_with_query'] = String(16) "page/environment"
        ['absolute'] = String(47) "http://ce/morrowtwo/trunk/docs/page/environment"
        ['absolute_with_query'] = String(47) "http://ce/morrowtwo/trunk/docs/page/environment"
    )
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
