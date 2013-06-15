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

class Serpent extends AbstractView {
	public $mimetype	= 'text/html';
	public $charset		= 'utf-8';

	public $default_resource	= 'file';
	public $default_compiler	= 'serpent';
	public $template		= '';
	public $template_suffix	= '.htm';
	public $force_compile	= false;
	public $mappings		= array();
	public $autoescape		= true;
	public $plugin_config	= false;

	public function __construct() {
		// Template holen
		$this->page = Factory::load('Page');
		$this->content_template = $this->page->get('alias');
		$this->language = Factory::load('Language');
		
		require_once( __DIR__ . '/../../serpent_1.3/source/serpent.class.php' );
	}

	public function getOutput($content, $handle) {
		// get default template
		if (empty($this->template)) {
			$this->template = $this->page->get('alias');
		}
		
		// assign template and frame_template to page
		$content['page']['template'] = $this->template;

		$_engine = new \Serpent();
		$_engine->compile_dir	= PROJECT_PATH.'temp/_serpent_templates_compiled/';
		$_engine->force_compile	= $this->force_compile;
		$_engine->default_resource = $this->default_resource;
		$_engine->default_compiler = $this->default_compiler;
		$_engine->setCharset($this->charset);

		// create temp dir if it does not exist
		if (!is_dir($_engine->compile_dir)) mkdir($_engine->compile_dir);
		
		// handle mappings
		$mappings = array(
			'dump'			=> '\\Morrow\\Debug::dump',
			'url'			=> '\\Morrow\\Factory::load("Url")->create',
			'securl'		=> '\\Morrow\\Factory::load("Security")->createCSRFUrl',
			'file'			=> 'PROJECT_RELPATH.',
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
		foreach ($this->mappings as $key => $value) {
			$mappings[$key] = $value;
		}

		// set compiler config
		$_engine->addPluginConfig('compiler', 'serpent', array('mappings' => $mappings));
		
		// set resource config
		$_engine->addPluginConfig('resource', 'file', array('template_dir' => PROJECT_PATH.'_templates/', 'suffix' => $this->template_suffix, 'language' => $this->language->get()));
		
		// set additional config
		$conf =& $this->plugin_config;
		if ($conf !== false) {
			$_engine->addPluginConfig($conf[0], $conf[1], $conf[2]);
		}
		
		$_engine->pass($content);
		fwrite($handle, $_engine->render($this->template));
		
		return $handle;
	}
}
