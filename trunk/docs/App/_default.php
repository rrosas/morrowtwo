<?php

namespace App;
use Morrow\Factory;
use Morrow\Debug;

class DefaultController extends Factory {
	public function setup() {
		$this->view->setHandler('serpent');

		// add markdown mapping
		$this->view->setProperty('mappings', array(
			'markdown' => '\\App\\DefaultController::markdown',
		));

		// toggle enduser view and developer view
		$show_protected_and_private = $this->session->get('show_protected_and_private');
		if (is_null($show_protected_and_private)) $show_protected_and_private = false;

		//if (!isset)

		// get all classes
		$classes_root = realpath(FW_PATH . '../frontend/vendor/Morrow/') . '/';
		$classes = $this->_scandir_recursive($classes_root);
		Debug::dump($classes);
	}

	// edit rendered markdown blocks
	public static function markdown($content) {
		$content = \Michelf\MarkdownExtra::defaultTransform($content);
		
		// change table formatting
		$content = str_replace('<table>', '<table class="table table-striped table-condensed">', $content);

		// add syntax highlighter
		$content = preg_replace('|<pre><code class="([a-z]+?)">|s', '<pre><code class="language-$1">', $content);

		return $content;
	}

	protected function _scandir_recursive($path) {
		$returner = array();
		foreach (scandir($path) as $file) {
			if ($file{0} === '.') continue;
			$full = $path . $file;
			if (is_file($full)) {
				$returner[] = $full;
			} else if (is_dir($full)) {
				$returner = array_merge($returner, $this->_scandir_recursive($full . '/'));
			}
		}
		return $returner;
	}
}
