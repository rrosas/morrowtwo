<?php
/*////////////////////////////////////////////////////////////////////////////////
    MorrowTwo - a PHP-Framework for efficient Web-Development
    Copyright (C) 2009  Christoph Erdmann, R.David Cummins

    This file is part of MorrowTwo <http://code.google.com/p/morrowtwo/>

    MorrowTwo is free software:  you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
////////////////////////////////////////////////////////////////////////////////*/


namespace Morrow;

/**
* This class provides some useful methods for dealing with URLs. Especially create() and redirect() are important, since they provide correct handling of Morrow paths in the context of language handling.
* 
* A Morrow URL has this structure:
* `language`/`node 1`/`node 2`
* 
* `language` is optional.
* So if you want to link to a page with the default language you can omit the language parameter.
* 
* Examples
* ---------
*
* ~~~{.php}
* // extend an absolute URL with an additional GET parameter
* $url = $this->url->create('http://chuck:norris@example.com:80/home?foo=bar#42', array('foo2' => 'bar2'));
* Debug::dump($url);
* 
* // URLs without a scheme also work
* $url = $this->url->create('//example.com/home', array('foo' => 'bar'));
* Debug::dump($url);
*
* // create an URL to the actual page
* $url = $this->url->create();
* Debug::dump($url);
* ~~~
*
* ### Change the language
*
* If you just want to change the language just use the `language` query parameter which is handled different to other query parameters.
*
* ~~~{.php}
* // create an absolute URL to the homepage and change the language
*
* $url = $this->url->create('home', array('language' => 'de'), true);
* // is the same as
* $url = $this->url->create('de/home', array(), true);
*
* Debug::dump($url);
* ~~~
*
* ### Redirect
* 
* ~~~{.php}
* // redirect to the not-found page
* $this->url->redirect('not-found/', array(), 404);
* ~~~
*/
class Url {
	/**
	 * Contains the currently active language.
	 * @var	array $_language_actual
	 */
	protected $_language_actual;

	/**
	 * Contains all valid language keys.
	 * @var	array $_language_possible
	 */
	protected $_language_possible;

	/**
	 * Contains the full path of the current page.
	 * @var	array $_fullpath
	 */
	protected $_fullpath;

	/**
	 * All parameters passed are used for create(). You don't have to do this yourself in Morrow.
	 *
	 * @param	array	$language_actual	Contains the currently active language.
	 * @param	array	$language_possible	Contains all valid language keys.
	 * @param	array	$_fullpath	Contains the full path of the current page.
	 */
	public function __construct($language_actual, $language_possible, $fullpath) {
		$this->_language_actual		= $language_actual;
		$this->_language_possible	= $language_possible;
		$this->_fullpath			= $fullpath;
	}

	/**
	 * Like PHP's parse_url() but returns an array with all of the keys even if they are empty.
	 * It also adds some keys which are necessary to build a fully qualified URL.
	 * So it is possible to rebuild the parsed URL with an implode() on the result.
	 *
	 * @param	string	$url	The URL to parse.
	 * @return	array	An array with all the keys.
	 */
	public function parse($url) {
		$template = array(
			'scheme'			=> '',
			'scheme_divider'	=> '',
			'user'				=> '',
			'pass_divider'		=> '',
			'pass'				=> '',
			'auth_divider'		=> '',
			'host'				=> '',
			'port_divider'		=> '',
			'port'				=> '',
			'path'				=> '',
			'query_divider'		=> '',
			'query'				=> '',
			'fragment_divider'	=> '',
			'fragment'			=> '',
		);
		$parts = array_merge($template, parse_url($url));

		if ($parts['scheme']	!== '') $parts['scheme_divider'] = '://';
		if ($parts['pass']		!== '') $parts['pass_divider'] = ':';
		if ($parts['user']		!== '') $parts['auth_divider'] = '@';
		if ($parts['port']		!== '') $parts['port_divider'] = ':';
		if ($parts['query']		!== '') $parts['query_divider'] = '?';
		if ($parts['fragment']	!== '') $parts['fragment_divider'] = '#';

		// parse_url does not work with missing schemes until PHP 5.4.7
		if (isset($parts['path']{1}) && $parts['path']{1} === '/') {
			$parts['path']				= ltrim($parts['path'], '/');
			$parts['host']				= strstr($parts['path'], '/', true);
			$parts['path']				= strstr($parts['path'], '/');
			$parts['scheme_divider']	= '//';
		}

		return $parts;
	}

	/**
	 * Does a clean redirect.
	 *
	 * @param	string	$path	The URL or the Morrow path to redirect to.
	 * @param	array	$query	Query parameters to adapt the URL.
	 * @param	integer	$http_response_code	The HTTP code which should be submitted to the client.
	 */
	public function redirect($path, $query = array(), $http_response_code = 302) {
		$url = $this->create($path, $query, true, '&');
		header('Location: '.$url, true, $http_response_code);
		die('');
	}


	/**
	 * Creates a URL for use with Morrow. It handles automatically languages in the URL. 
	 *
	 * @param	string	$path	The URL or the Morrow path to work with. Leave empty if you want to use the current page.
	 * @param	array	$query	Query parameters to adapt the URL.
	 * @param	boolean	$absolute	If set to true the URL will be a fully qualified URL.
	 * @param	boolean	$separator	The string that is used to divide the query parameters.
	 * @return	string	The created URL.
	 */
	public function create($path = '', $query = array(), $absolute = false, $separator = '&amp;') {
		// if the passed path is empty use the path of the current page
		if (empty($path)) $path = $this->_fullpath;

		// parse input url
		$parts = $this->parse($path);
		
		// combine query parameters
		parse_str($parts['query'], $parts['query']);
		$parts['query'] = array_merge($parts['query'], $query);
		if (count($parts['query']) > 0) {
			$parts['query_divider'] = '?';
		}

		// only for URLs without a scheme
		if (empty($parts['scheme_divider'])) {

			$nodes				= explode('/', trim($parts['path'], '/'));

			// **********************  language handling
			$lang = $this->_language_actual;

			// remove the language from the path
			if (in_array($nodes[0], $this->_language_possible)) {
				$lang = array_shift($nodes);
			}

			// lang as GET param has precedence
			if (isset($query['language']) && in_array($query['language'], $this->_language_possible)) {
				$lang = $query['language'];
				unset($parts['query']['language']);
				if (count($parts['query']) === 0) {
					$parts['query_divider'] = '';
				}
			}

			// now we have to add the language in some cases
			// add the lang to the path if it is not the current language and not the default language
			if ($lang !== $this->_language_actual || $this->_language_possible[0] !== $this->_language_actual) {
				array_unshift($nodes, $lang);
			}
			// **********************

			// put it back together
			$parts['path'] = implode('/', $nodes);

			// create complete url with domain
			if ($absolute) {
				$base			= $this->parse($this->getBaseHref());
				$base['path']	= $base['path'] . $parts['path'];
				$base['query']	= $parts['query'];
				$parts			= $base;
			}
		}

		// create query string
		$parts['query'] = http_build_query($parts['query'], '', $separator);

		return implode('', $parts);
	}

	/**
	 * To get the base href for the actual Morrow installation.
	 *
	 * @return	string	The base href.
	 */
	public function getBasehref() {
		$path = dirname($_SERVER['SCRIPT_NAME']).'/';
		
		// If it is the root the return value of dirname is slash
		if ($path == '//') $path = '/';
		$scheme = isset($_SERVER['HTTPS']) || isset($_SERVER['HTTP_X_SSL_ACTIVE']) || (isset($_SERVER['SSL_PROTOCOL']) && !empty($_SERVER['SSL_PROTOCOL'])) ? 'https://' : 'http://';
		return $scheme . $_SERVER['HTTP_HOST'] . $path;
	}
}
