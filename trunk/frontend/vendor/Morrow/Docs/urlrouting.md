URL Routing
=============================

URL Routing is a nice thing if you want to internally redirect aliases to other aliases as the framework would normally use.
This is very useful if you want to build speaking or just clean URLs.

Configuration
--------------

**App/_default.php**
~~~{.php}
...
// routing rules
	'routing' = array(
	    '/' => 'home/',
	),
...
~~~

Each array entry defines a rule will be run through when an URL is requested. The key is the pattern that have to match the incoming url. The value defines the target of the redirection.

As you can see in the code snippet above, there is already one rule defined. This rule defines that the alias `home` is called when there is no path given. Change that if you want to use an other default URL path.

**Do not forget to define the rules for each language!**

Rules
------

There are a few things you have to consider:

Pattern | Description
--------| ------------
`:[a-z0-9_]` | Use a colon to define a parameter which will be passed to the input class and is available via `$this->input->get()`. Nice to create pretty URLs without GET parameters.
`*[a-z0-9_]` | Use a asterisk to define a bunch of parameters which will be passed to the input class and are available via `$this->input->get()`. Useful if you do not know how many parameters you will get. The asterisk pattern has to be at the end of the rule and may appear only once per pattern.


Example
--------

Imagine you want to have URL paths like `products/category/this-is-the-product-2067` rather than `products/?id=2067`

~~~{.php}
...
// routing rules
	'routing' = array(
		'products/:category/:product' => 'products/',
	),
...
~~~

In this example the controller `App/products.php` will be used and you have access to the category and the product via `$this->input->get('category')` and `$this->input->get('product')`.

Keep in mind that the pattern have to match the count of URL nodes. So `products/this-is-the-product` would not match.