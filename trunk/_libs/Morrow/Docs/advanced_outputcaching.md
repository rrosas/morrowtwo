Output Caching {#outputcaching}
=============================

ETAG by default

The output of every view handler can be cached via HTTP headers. The caching control is the job of the view class which is accessable via $this->view in your controller. It works with Expiration HTTP headers as defined in RFC 2616.

Because there is no HTTP request to the server you do not have control over the cache until the cache expires.

Example
--------

In the following example the output will be cached for five seconds.

~~~{.php}
// ... Controller code
 
$this->view->setCache('+5 seconds');
 
// ... Controller Code
~~~

The passed string defines the lifetime of the cache, given as a string strtotime() recognizes. 
