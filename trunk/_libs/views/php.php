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


namespace Morrow\Core\Views;

class Php extends AbstractView {
	public $mimetype	= 'text/html';
	public $charset		= 'utf-8';

	public $template		= '_index';
	public $content_template	= '';
	public $template_suffix	= '.tpl';

	public function __construct($view) {
		// Template holen
		$this->page = \Morrow\Core\Factory::load('Libraries\page');
		$this->content_template = $this->page->get('alias');
		$this->language = \Morrow\Core\Factory::load('Libraries\language');
	}

	public function getOutput($content, $handle) {
		// get default template
		if (empty($this->content_template)) {
			$this->content_template = $this->page->get('alias');
		}
		
		// language specific templates
		$lang = $this->language->get();
		$lang_template = $this->template . '.' . $lang;
		if(is_file(PROJECT_PATH . '_templates/' . $lang_template . $this->template_suffix)) {
			$this->template = $lang_template;
		}

		// language specific content template
		$lang_template = $this->content_template . '.' . $lang;
		if(is_file(PROJECT_PATH . '_templates/' . $lang_template . $this->template_suffix)) {
			$this->content_template = $lang_template;
		}
		
		// add suffix
		$this->template .=  $this->template_suffix;
		$this->content_template .=  $this->template_suffix;

		// assign template and frame_template to page
		$content['page']['content_template'] = $this->content_template;
		$content['page']['template'] = $this->template;

		// assign vars
		foreach ($content as $key=>$value) {
			$$key = $value;
		}

		ob_start();
		include(PROJECT_PATH.'_templates/'.$this->template);
		$output = ob_get_contents();
		ob_end_clean();

		fwrite($handle, $output);
		
		return $handle;
	}
}
