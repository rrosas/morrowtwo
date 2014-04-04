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


namespace Morrow\Views;

use Morrow\Factory;

/**
 * With this view handler it is possible to output with plain PHP.
 * 
 * This handler uses the Serpent Template Engine which improves PHP a little bit to have more comfort when writing templates.
 * 
 * All public members of a view handler are changeable in the Controller by `\Morrow\View->setProperty($member, $value)`;
 *
 * Example
 * --------
 * 
 * ~~~{.php}
 * // ... Controller code
 * 
 *
 * // ... Controller code
 * ~~~
 */
class Serpent extends AbstractView {
	/**
	 * a
	 * @var string $template
	 */
	public $template		= '';

	/**
	 * The default extension that will Serpent use for template names.
	 * @var string $template_suffix
	 */
	public $template_suffix	= '.htm';

	/**
	 * a
	 * @var boolean $force_compile
	 */
	public $force_compile	= false;

	/**
	 * a
	 * @var array $mappings
	 */
	public $mappings		= array();

	/**
	 * a
	 * @var array $resources
	 */
	public $resources		= array();

	/**
	 * Initializes classes for the use in getOutput().
	 * @hidden
	 */
	public function __construct() {
		$this->page		= Factory::load('Page');
		$this->language	= Factory::load('Language');
	}

	/**
	 * You always have to define this method.
	 * @param   array $content Parameters that were passed to \Morrow\View->setContent().
	 * @param   handle $handle  The stream handle you have to write your created content to.
	 * @return  string  Should return the rendered content.
	 * @hidden
	 */
	public function getOutput($content, $handle) {
		// get default template
		if (empty($this->template)) {
			$this->template = $this->page->get('alias');
		}
		
		// assign template and frame_template to page
		$content['page']['template'] = $this->template;

		$compile_dir = APP_PATH .'temp/serpent_templates_compiled/';
		if (!is_dir($compile_dir)) mkdir($compile_dir); // create temp dir if it does not exist
		
		// init serpent
		$_engine = new \McSodbrenner\Serpent\Serpent($compile_dir, $this->charset, $this->force_compile);
		
		// handle mappings
		$mappings = array(
			'dump'			=> '\\Morrow\\Debug::dump',
			'url'			=> '\\Morrow\\Factory::load("Url")->create',
			'securl'		=> '\\Morrow\\Factory::load("Security")->createCSRFUrl',
			'cycle'			=> '\\Morrow\\Helpers\\View::cycle',
			'mailto'		=> '\\Morrow\\Helpers\\View::mailto',
			'hidelink'		=> '\\Morrow\\Helpers\\View::hidelink',
			'thumb'			=> '\\Morrow\\Helpers\\View::thumb',
			'image'			=> '\\Morrow\\Helpers\\View::image',
			'truncate'		=> '\\Morrow\\Factory::load("Helpers\\\String")->truncate',
			'strip'			=> 'ob_start(array("\\Morrow\\Helpers\\View::strip")) //',
			'endstrip'		=> 'ob_end_flush',
			'loremipsum'	=> '\\Morrow\\Helpers\\View::loremipsum',
			'formlabel'		=> '\\Morrow\\Factory::load("Formhtml")->getLabel',
			'formelement'	=> '\\Morrow\\Factory::load("Formhtml")->getElement',
			'formerror'		=> '\\Morrow\\Factory::load("Formhtml")->getError',
			'formupload'	=> '\\Morrow\\Factory::load("Formhtml")->getInputImage',
			'_'				=> '\\Morrow\\Factory::load("Language")->_',
		);
		// add user mappings
		foreach ($this->mappings as $key => $value) {
			$mappings[$key] = $value;
		}

		$_engine->addMappings($mappings);
		
		// handle resources
		$_engine->addResource('file',
			new \McSodbrenner\Serpent\ResourceFile(APP_PATH .'templates/', $this->template_suffix, $this->language->get())
		);
		
		foreach ($this->resources as $resource) {
			call_user_func_array(array($_engine, 'addResource'), $resource);
		}
		
		// write source to stream
		$_engine->pass($content);
		fwrite($handle, $_engine->render($this->template));
		
		return $handle;
	}
}
