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
 * Adds a little bit of security to your app or webpage.
 *
 * Example
 * -------
 *
 * ~~~{.php}
 * // Controller code
 *
 * // add CSP security rules
 * // all rules which are identical to default-src can be left out.
 * $this->security->setCsp(array(
 *     'default-src'	=> "'self'",
 *     //'script-src'	=> "'self'",
 *     //'img-src'		=> "'self'",
 *     'style-src'		=> "'self' http://fonts.googleapis.com",
 *     //'media-src'	=> "'self'",
 *     //'object-src'	=> "'self'",
 *     //'frame-src'	=> "'self'",
 *     'font-src'		=> "'self' http://themes.googleusercontent.com",
 * ));
 *
 * // Do not allow to show this site in a frameset (prevents clickjacking)
 * $this->security->setFrameOptions('DENY');
 * ~~~
 */
class Security {
	public function __construct() {
		$session	= Factory::load('Session');
		$view		= Factory::load('View');

		// hide PHP version
		header_remove("X-Powered-By");

		// set new token
		if (is_null($session->get('csrf_token'))) $session->set('csrf_token', md5(uniqid(rand(), TRUE)));
	}

	/**
	 * Sets the value for the CSP header to prevent XSS attacks.
	 * Should be written as the official specs says (<http://www.w3.org/TR/CSP/>).
	 * For a detailed description take a look at:
	 * <https://developer.mozilla.org/en-US/docs/Security/CSP/CSP_policy_directives>.
	 *
	 * @param	array	$options	The options as associative array. Use the rule name as key and the option as value.
	 * @return  `null`
	 */
	public function setCsp($options) {
		$csp_gecko	= '';
		$csp		= '';

		$options['options'] = '';

		// handle some differences between the browsers
		// Firefox need other syntax for unsafe-inline and unsafe-eval
		if (isset($options['script-src']) && stripos($options['script-src'], "'unsafe-inline'")) {
			$options['options'] .= 'inline-script ';
		}

		if (isset($options['script-src']) && stripos($options['script-src'], "'unsafe-eval'")) {
			$options['options'] .= 'eval-script ';
		}

		foreach ($options as $key=>$value) {
			if ($value == '') continue;
			$key = strtolower($key);
			// handle some differences between the browsers
			// and create the csp string
			$csp_gecko	.= $key . ' ' . $value . ';';

			if ($key != 'options') {
				$csp	.= $key . ' ' . $value . ';';
			}
		}

		// skip syntax Firefox doesn't know
		$csp_gecko = str_replace(array("'unsafe-inline'", "'unsafe-eval'"), '', $csp_gecko);

		$view = Factory::load('view');

		$view->setHeader('X-Content-Security-Policy', $csp_gecko); // for Firefox
		$view->setHeader('X-WebKit-CSP', $csp); // for Chrome
		$view->setHeader('Content-Security-Policy', $csp); // standard implementations
		// IE doesn't support it
	}
	
	/**
	 * Sets whether or not a browser should be allowed to render the page in a <frame> or <iframe>.
	 * This can be used to avoid clickjacking attacks, by ensuring that their content is not embedded into other sites.
	 *
	 * The following options are possible:
	 *
	 * Value | Description
	 * ------|-------------
	 * `DENY` | The page cannot be displayed in a frame, regardless of the site attempting to do so.
	 * `SAMEORIGIN` | The page can only be displayed in a frame on the same origin as the page itself.
	 * `ALLOW-FROM` _uri_ | The page can only be displayed in a frame on the specified origin.
	 * 
	 * @param	string	$option	The option as described
	 */
	public function setFrameOptions($option) {
		Factory::load('view')->setHeader('X-Frame-Options', $option);
	}

	/**
	 * Gets the CSRF token for the current user.
	 * @return	`string`
	 */
	public function getCSRFToken() {
		$session = Factory::load('session');
		return $session->get('csrf_token');
	}
	
	/**
	 * Creates an URL like URL::create() but adds the CSRF token as GET parameter.
	 * You have to check the token yourself via verifyCSRFToken().
	 *
	 * For the parameters see: Url::create()
	 */
	public function createCSRFUrl($path, $query = array(), $rel2abs = false, $sep = '&amp;') {
		$query['csrf_token'] = Factory::load('session')->get('csrf_token');
		return Factory::load('url')->create($path, $query, $rel2abs, $sep);
	}

	/**
	 * A function to verify a valid CSRF token.
	 * @return	boolean	Returns `true` if a valid token was sent otherwise `false`.
	 */
	public function checkCSRFToken() {
		$input = Factory::load('input');
		$session = Factory::load('session');

		if ($input->get('csrf_token') != $session->get('csrf_token')) {
			return false;
		}
		return true;
	}
	
	/**
	 * Creates a 102 characters long hash by using the crypt function.
	 * @param	string	$string	The input string (e.g. a password) to hash.
	 * @param	string	$salt	The salt to use.
	 * @return	string	The hash.
	 */
	public static function createHash($string, $salt = null) {
		if ($salt === null) return crypt($string);
		return crypt($string, $salt);
	}

	/**
	 * Checks if the hash is valid.
	 * @param	string	$string	The input string (e.g. a password) to hash.
	 * @param	string	$hash	The hash to check against.
	 * @return	`bool`
	 */
	public static function checkHash($string, $hash) {
		if (crypt($string, $hash) == $hash) return true;
		return false;
	}
}
