<?php

namespace Morrow;

class Docblock {
	protected $_reflection;

	public function __construct($class) {
		$this->_reflection = new \ReflectionClass($class);
	}

	public function get() {
		$r = $this->_reflection;

		$data = array(
			'namespace'	=> $r->getNamespaceName(),
			'name'		=> $r->getShortname(),
			'doc'		=> $this->parseDocComment($r->getDocComment()),
			'interface'	=> $r->isInterface(),
			'abstract'	=> $r->isAbstract(),
			'final'		=> $r->isFinal(),
			'parent'	=> $r->getParentClass() ? $r->getParentClass()->getName() : null,

			'constants'	=> $this->getConstants(),
			'members'	=> $this->getMembers(),
			'methods'	=> $this->getMethods(),
		);

		return $data;
	}

	public function getConstants() {
		return $this->_reflection->getConstants();
	}

	public function getMembers() {
		$members	= array();

		foreach ($this->_reflection->getProperties() as $p) {

			$member = array(
				'name' => $p->getName(),
				'visibility' => $p->isPublic() ? 'public' : ($p->isProtected() ? 'protected' : 'private'),
				'static' => $p->isStatic(),
				//'_r' => $p,
			);

			// parse doc comments
			$doc = $this->parseDocComment($p->getDocComment());
			$member = array_merge($member, $doc);

			$members[$member['name']] = $member;
		}

		// add default properties
		$default_properties = $this->_reflection->getDefaultProperties();
		foreach ($default_properties as $name=>$p) {
			$members[$name]['default'] = $p;
		}

		return $members;
	}

	public function getMethods() {
		$methods	= array();
		$class		= $this->_reflection->getName();

		foreach ($this->_reflection->getMethods() as $m) {
			// do not show functions of the extended class
			if ($class !== $m->class) continue;

			// get overall information
			$method = array(
				'name' => $m->getName(),
				'visibility' => $m->isPublic() ? 'public' : ($m->isProtected() ? 'protected' : 'private'),
				'static' => $m->isStatic(),
				'abstract' => $m->isAbstract(),
				'final' => $m->isFinal(),
				//'_r' => $m,
			);

			// get parameter information
			$method['parameters'] = array();
			
			foreach ($m->getParameters() as $p) {
				$parameter = array(
					'name' => $p->getName(),
					'optional' => $p->isOptional(),
					'default' => !$p->isOptional() ? null : $p->getDefaultValue(),
				);
				$method['parameters'][$p->getName()] = $parameter;
			}

			// parse doc comments
			$doc = $this->parseDocComment($m->getDocComment());
			$method = array_merge($method, $doc);
			
			// add docblock info to parameters
			if (isset($doc['tags']['param']))
			foreach ($doc['tags']['param'] as $d) {
				$name = substr($d['variable'], 1);

				if (!isset($method['parameters'][$name])) {
					throw new \Exception('Found parameter "'.$name.'" for method "'.$m->class.'::'.$m->getName().'" in DocBlock, but there is no parameter with that name.');
				}

				$method['parameters'][$name]['type']		= $d['type'];
				$method['parameters'][$name]['description']	= $d['description'];
			}

			$methods[$method['name']] = $method;
		}

		return $methods;
	}

	public function parseDocComment($string) {

		// unify linebreaks
		$string = preg_replace("-(\r\n|\r|\n)-", "\n", $string);

		// remove asterisks
		$string = preg_replace('|^/\*\*\n(.+)\*/$|s', '$1', $string);

		// find asterisk depth to content
		preg_match('|^(\s*)\*(\s+)|', $string, $match);
		$string = trim(preg_replace('~(\n|^)\s*'.preg_quote($match[1]) . '\*(' . preg_quote($match[2]).')?~', "\n", $string));

		// strip empty asterisk lines
		$string = preg_replace("-\n\*(\s+|$)-", "\n", $string);

		// now we have the comment part removed and are able to parse the actual content
		$parts		= explode("\n@", $string, 2);

		$content	= $this->_parseContent($parts[0]);
		$tags		= isset($parts[1]) ? $this->_parseTags('@' . $parts[1]) : array();

		$returner = array(
			'content'	=> $content,
			'tags'		=> $tags,
		);

		return $returner;
	}

	protected function _parseContent($string) {
		// strip whitespace between linebreaks
		$parts = explode("\n", $string, 2);
		$parts['title']			= $parts[0];

		if (isset($parts[1])) {
			$parts = array_map('trim', $parts);
			$parts['description']	= $parts[1];
		}

		return $parts;
	}

	protected function _parseTags($string) {
		$returner = array();

		$patterns = array(
			'deprecated'	=> '@deprecated',
			'ignore'		=> '@ignore',
			'hidden'		=> '@hidden',
			'param'			=> '@param\s+(?P<type>\S+)\s+(?P<variable>\S+)\s+(?P<description>.+)',
			'return'		=> '@return\s+(?P<type>\S+)(\s+(?P<description>.+))?',
			'var'			=> '@var\s+(?P<type>\S+)',
		);

		foreach ($patterns as $name=>$p) {
			if (preg_match_all('|'.$p.'|', $string, $matches, PREG_SET_ORDER)) {
				$returner[$name] = $matches;
			}
		}

		return $returner;
	}

}