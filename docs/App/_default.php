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
		$spap = $this->input->get('show_protected_and_private');
		if (isset($spap)) {
			$this->session->set('show_protected_and_private', $spap);
		}

		if ($this->session->get('show_protected_and_private') == null) {
			$this->session->set('show_protected_and_private', '');
		}

		$this->view->setContent('show_protected_and_private', $this->session->get('show_protected_and_private'));

		// get all classes
		$classes_root = realpath(FW_PATH . '../frontend/vendor') . '/';
		$classes = $this->_scandir_recursive($classes_root . 'Morrow/');

		// strip non php files and create relative paths
		foreach ($classes as $i=>$class) {
			if (!preg_match('|\.php$|', $class)) unset($classes[$i]);
			else {
				$classes[$i] = str_replace($classes_root, '', $classes[$i]);
				$classes[$i] = preg_replace('|\.php$|', '', $classes[$i]);
			}
		}
		$this->view->setContent('classes', $classes);
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

	/* get all files recursive and sort folders to the end */
	protected function _scandir_recursive($path) {
		$returner	= array();
		$folders	= array();
		
		foreach (scandir($path) as $file) {
			if ($file{0} === '.') continue;
			$full = $path . $file;
			if (is_file($full)) {
				$returner[] = $full;
			} else if (is_dir($full)) {
				$folders[] = $full . '/';
			}
		}

		foreach ($folders as $folder) {
			$returner = array_merge($returner, $this->_scandir_recursive($folder));
		}

		return $returner;
	}
}
