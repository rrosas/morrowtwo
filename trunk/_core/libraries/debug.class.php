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





class Debug
	{
	private $config;

	// Members for dump
	public $maxdepth               = 8; // the maximum recursion level
	
	// Members for errorhandler
	private $lasterror;
	private $errorcounter = 0;
	private $errortype;
	
	
	public function __construct()
		{
		// read config from config class
		$config = Factory::load('config');
		$this->config = $config->get('debug');

		// error types
		$this->errortype[1]		= 'E_ERROR';
		$this->errortype[2]		= 'E_WARNING';
		$this->errortype[4]		= 'E_PARSE';
		$this->errortype[8]		= 'E_NOTICE';
		$this->errortype[16]	= 'E_CORE_ERROR';
		$this->errortype[32]	= 'E_CORE_WARNING';
		$this->errortype[64]	= 'E_COMPILE_ERROR';
		$this->errortype[128]	= 'E_COMPILE_WARNING';
		$this->errortype[256]	= 'E_USER_ERROR';
		$this->errortype[512]	= 'E_USER_WARNING';
		$this->errortype[1024]	= 'E_USER_NOTICE';
		$this->errortype[2048]	= 'E_STRICT';
		$this->errortype[4096]	= 'E_RECOVERABLE_ERROR';
		$this->errortype[8192]	= 'E_DEPRECATED';
		$this->errortype[16384]	= 'E_USER_DEPRECATED';
		}

	private function errorhandler_file($errstr, $backtrace, $errorcode)
		{
		$fn = FW_PATH.'/_logs/'.date("y-m-d").'.txt';

		$body  = '############################## '.$errorcode."\n";
		$body .= 'Datum:      '.date("d.m.Y - H:i:s")."\n";
		$body .= 'URL:        '.$_SERVER['REQUEST_URI']."\n";
		$body .= 'IP:         '.$_SERVER['REMOTE_ADDR']."\n";
		$body .= 'User-Agent: '.$_SERVER['HTTP_USER_AGENT']."\n";
		$body .= 'Meldung:    '.$errstr."\n";
		$body .= 'Backtrace:  '."\n\n";

		foreach ($backtrace as $value)
			{
			$body .= '       File: '.$value['file']." (Line ".$value['line'].")\n";
			$body .= '   Function: '.$value['class'].$value['type'].$value['function'].'()'."\n\n";
			}
		$body .= "\n";

		file_put_contents($fn, $body, FILE_APPEND);
		}

	private function errorhandler_firephp($errstr, $backtrace, $errordescription)
		{
		require_once(FW_PATH . '_libs/FirePHPCore-0.3.1/lib/FirePHPCore/FirePHP.class.php');
		$firephp = FirePHP::getInstance(true);

		$actual = &$backtrace[0];
		
		foreach ($backtrace as $key=>$item)
			{
			if ($key > 0)
				{
				if ($key===1) $firephp->group( 'Backtrace', array('Collapsed'=>false) );
				
				$firephp->info( $item['file'] . ' (line ' . $item['line'] . ')' );
				$firephp->log( $item['args'] );

				if ($key==count($backtrace)) $firephp->groupEnd();
				continue;
				}
			
			$title = utf8_decode($item['file']) . ' (line ' . $item['line'] . ')';
			
			$firephp->group( 'Error' );
			$firephp->error( $errstr );
			$firephp->info( $title );
			
			$show_lines = 10;
			$file = file($item['file']);
			if ($item['line']-$show_lines < 0) $linestart = 0; else $linestart = $item['line']-$show_lines;
			$file = array_slice($file, $linestart, $show_lines*2, true);

			foreach ($file as $key=>$line)
				{
				$method = ($item['line'] == $key+1) ? 'warn' : 'log';
				$firephp->$method( $key+1 . ': ' . $line );
				}
			
			$firephp->groupEnd();
			$firephp->info( $item['args'], 'Arguments' );
			}
		}

	private function errorhandler_screen($errstr, $backtrace, $errordescription)
		{
		// output css and js only at first call
		if ($this->errorcounter === 1)
			echo $this->errorhandler_screen_header();

		// output error
		$error  = '<div class="errorhandler">';
		$error .= '<div class="box">';
		$error .= '<div class="skyline">'.$errordescription.'</div>';
		$error .= '<div class="headline">'.$errstr.'</div><br />';

		$count = 0;
		foreach ($backtrace as $key=>$value)
			{
			// only if file is available
			if (!empty($value['file']) && is_file($value['file']))
				{
				$id_file = 'errorhandler_file_'.$this->errorcounter.'_'.$count;
				$id_args = 'errorhandler_args_'.$this->errorcounter.'_'.$count;
				if ($count === 0) $show = 'style="display: block;"';
				else              $show = '';

				if ($count>0)
					{
					$call = $value['class'].$value['type'].$value['function'].'()';
					$error .= '<div class="filename">'.$call.'</div>';
					}

				if (count($value['args'])>0 and $count > 0)
					{
					$dump = $this->dump($value['args']);
					$error .= '<a href="#" onclick="errorhandler_toggle(\''.$id_args.'\'); return false;">&raquo; Arguments ('.count($value['args']).')</a>';
					$error .= '<div class="args" id="'.$id_args.'" style="display: none;">'.$dump.'</div>';
					}

				// highlight the file name
				$file = preg_replace('=[^/]+$=', '<strong>$0</strong>', $value['file']);
				
				$error .= '<a href="#" onclick="errorhandler_toggle(\''.$id_file.'\'); return false;">&raquo; '.$file.' (Line '.$value['line'].')</a>';
				$error .= '<div class="file" id="'.$id_file.'" '.$show.'>'.$this->getContent($value['file'], $value['line']).'<div style="clear: both;"></div></div>';

				$count++;
				}
			}

		$error .= '</div>';
		$error .= '</div>'."\n";

		echo $error;		
		}
		
	private function errorhandler_screen_header()
		{
		return '
			<style type="text/css">
			.errorhandler { color: #333; }
			.errorhandler .lineerror { background-color: #fdd; display: inline; }
			.errorhandler .box { background-color: #f3f3f3; padding: 10px; margin: 10px; border: 1px solid #ccc; font: normal 11px/14px Verdana,Arial,Helvetica; }
			.errorhandler .file { display: none; padding: 2px; margin: 1px 0px 10px 0px; background-color: #fff; border: 1px solid #999; font-family: Courier New; overflow: hidden; }
			.errorhandler .args { display: none; padding: 2px; margin: 1px 0px 10px 0px; background-color: #fff; border: 1px solid #999; font-family: Courier New; overflow: hidden; white-space: pre; }
			.errorhandler .skyline { float: right; padding: 4px 5px 2px 5px; color: #fff; font-size: 9px; }
			.errorhandler .headline { background-color: #c44; padding: 4px 5px 4px 5px; color: #fff; font-weight: bold; font-size: 13px; line-height: 16px; }
			.errorhandler .filename { background-color: #ddd; margin: 10px 0px 2px 0px; padding: 4px 5px 4px 5px; font-weight: bold; font-size: 13px; }
			.errorhandler ol { background-color: #eee; margin-top: 0; margin-bottom: 1px; }
			.errorhandler ol li { background-color: #fff; padding: 0px 0px 0px 5px; margin: 0; }
			.errorhandler ol li code { margin: 0; padding: 0; background: none; border: 0px; display: inline; }
			.errorhandler a { display: block; text-decoration: none; color: #333; font-weight: normal; padding: 1px 0px 1px 10px; }
			.errorhandler .headline a { color: #fff; }
			</style>

			<script type="text/javascript">
			function errorhandler_toggle(id)
				{
				id = document.getElementById(id);
				if (id.style.display == "block") id.style.display = "none";
				else id.style.display = "block";
				}
			</script>
		';
		}
		
	public function errorhandler($exception)
		{
		$errstr = $exception->getMessage();
		$errcode = $exception->getCode();
		$backtrace = $exception->getTrace();
		
		// the same should not be outputted more than once
		if ($errstr == $this->lasterror) return;
		$this->lasterror = $errstr;

		// count errors to produce unique ids
		$this->errorcounter++;

		// if it is an error throw away the first backtrace item
		if ($exception instanceof ErrorException) array_shift($backtrace);
		
		// add actual error to backtrace
		$bt['file'] = $exception->getFile();
		$bt['line'] = $exception->getLine();
		array_unshift($backtrace, $bt);
		
		// clean array
		$backtrace_keys = array('file'=>'', 'line'=>'', 'class'=>'', 'object'=>'', 'type'=>'', 'function'=>'', 'args'=>array());
		foreach ($backtrace as $key=>$value)
			{
			$backtrace[$key] = array_merge($backtrace_keys, $value);
			}
		
		// cause of a bug in PHP 5.2 (http://bugs.php.net/bug.php?id=45895#c140511)
		$use_workaround = version_compare( phpversion(), '5.2', '>=' ) && version_compare( phpversion(), '5.3', '<=' );
		if ($exception instanceof ErrorException && $use_workaround)
			{
			for ($i = count($backtrace) - 1; $i > 0; --$i)
				{
				$backtrace[$i]['args'] = $backtrace[$i - 1]['args'];
				}
			}
		
		// set the error code string
		if ($exception instanceof ErrorException) $errordescription = $this->errortype[ $exception->getSeverity() ];
		elseif ($errcode == 0) $errordescription = 'EXCEPTION';
		else $errordescription = 'EXCEPTION (Code '.$errcode.')';

		// show in firephp console (default = 0)
		if (isset($this->config['console']) && $this->config['console'] == true)
			$this->errorhandler_firephp($errstr, $backtrace, $errordescription);

		// show error on screen (default = 1)
		if (!isset($this->config['screen']) OR $this->config['screen'] == true)
			$this->errorhandler_screen($errstr, $backtrace, $errordescription);

		// log error in flatfile (default = 0)
		if (isset($this->config['flatfile']) && $this->config['flatfile'] == 1)
			$this->errorhandler_file($errstr, $backtrace, $errordescription);
		
		return;
		}

	private function getContent($errfile, $errline, $file_or_string = 'file')
		{
		$show_lines = 10;

		// Ausschnitt aus der Datei holen
		if ($file_or_string === 'file')
			{
			$file = highlight_file($errfile, true);
			}
		else
			{
			$file = highlight_string($errfile, true);
			}
		
		$file = explode("<br />", $file);

		foreach($file as $key=>$value)
			{
			$temp = strip_tags($value);
			if (empty($temp)) $value = '&nbsp;';
			$value = '<span>'.$value.'</span>';
			if ($key == $errline-1) $value = '<div class="lineerror">'.$value.'</div>';
			$value = '<li><code>'.$value.'</code></li>';
			$file[$key] = $value;
			}

		if ($errline-$show_lines < 0) $linestart = 0; else $linestart = $errline-$show_lines;
		$file = array_slice($file, $linestart, $show_lines*2, true);
		$file = implode('', $file);
		return '<ol start="'.($linestart+1).'">'.$file.'</ol>';
		}

		
	/* main method
	********************************************************************************************/
	public function dump($input)
		{
		// show in firephp console (default = 0)
		if (isset($this->config['console']) && $this->config['console'] == true)
			{
			call_user_func_array( array($this, 'dump_firephp'), $input );
			}

		// show error on screen (default = 1)
		if (isset($this->config['screen']) && $this->config['screen'] == false) return;
		
		// get function call position
		$backtrace = debug_backtrace();
		$backtrace = $backtrace[1];

		// get calling file
		if (isset($backtrace['file']))
			{
			$file = file($backtrace['file']);
			$function = trim($file[$backtrace['line']-1]);
			}

		$output = '';
		foreach ($input as $arg)
			{
			// create headline
			$headline = '<b>'.$function.'</b><br />called in <b>'.$backtrace['file'].'</b> on line <b>'.$backtrace['line'].'</b>';
			$output .= $this->dump_php($arg, $headline);
			}
		
		return $output;
		}

	public function dump_firephp()
		{
		require_once(FW_PATH . '_libs/FirePHPCore-0.3.1/lib/FirePHPCore/FirePHP.class.php');
		$firephp = FirePHP::getInstance(true);

		$backtrace = debug_backtrace();
		$actual = $backtrace[3];
		
		$firephp->group( 'Dump' );
		$firephp->info( $actual['file'] . ' (line '.$actual['line'].')'  );
		
		$args = func_get_args();
		foreach ($args as $arg)
			{
			$firephp->log($arg);
			}
		
		$firephp->groupEnd();
		
		return '';
		}


	////////////////////////////////////////////////////////
	// Inspired from:     PHP.net Contributions
	// Description: Helps with php debugging

	protected function dump_php(&$var, $info = FALSE)
		{
		$scope = false;
		$prefix = 'unique';
		$suffix = 'value';

		if($scope) $vals = $scope;
		else $vals = $GLOBALS;

		$old = $var;
		$var = $new = $prefix.rand().$suffix; $vname = FALSE;
		foreach($vals as $key => $val) if($val === $new) $vname = $key;
		$var = $old;

		$output = "<pre style='margin: 0px 0px 10px 0px; display: block; background: white; color: black; font: normal 12px/15px \"Courier New\"; border: 1px solid #cccccc; padding: 5px;'>";
		if($info != FALSE) $output .= "<b style='color: red;'>$info:</b><br />";
		$output .= $this->dump_php_recursive($var, '$'.$vname);
		$output .= "</pre>";
		
		return $output;
		}

	protected function dump_php_recursive(&$var, $var_name = NULL, $indent = NULL, $reference = NULL, $depth = 0)
		{
		$colors = array(
			'String' => 'green',
			'Integer' => 'red',
			'Float' => '#0099c5',
			'Boolean' => '#92008d',
			'NULL' => 'black',
			'Resource' => 'black',
		);
		$grey = '#a2a2a2';
		
		$do_dump_indent = "<span style='color:#ccc;'>|</span> &nbsp;&nbsp; ";
		$reference = $reference.$var_name;
		$keyvar = 'the_do_dump_recursion_protection_scheme'; $keyname = 'referenced_object_name';
		
		$output = '';

		$depth++;

		if (is_array($var) && isset($var[$keyvar]))
			{
			$real_var = &$var[$keyvar];
			$real_name = &$var[$keyname];
			$type = ucfirst(gettype($real_var));
			$output .= "$indent$var_name <span style='color:$grey'>$type</span> = <span style='color:#e87800;'>&amp;$real_name</span><br />";
			}
		else
			{
			$var = array($keyvar => $var, $keyname => $reference);
			$avar = &$var[$keyvar];
			$type = ucfirst(gettype($avar));

			// (for historical reasons "double" is returned in case of a float, and not simply "float") 
			if($type == "Double") { $type = "Float"; }
			if($type == "Unknown type") { $type = "String"; }
			
			if(is_array($avar))
				{
				$count = count($avar);
				$output .= "$indent" . ($var_name ? "$var_name => ":"") . "<span style='color:$grey'>$type ($count)</span><br />$indent(<br />";
				$keys = array_keys($avar);
				foreach($keys as $name)
					{
					$value = &$avar[$name];
					if ($depth > $this->maxdepth ) $output .= "$indent$do_dump_indent<b style='color:red'>Too much recursion ...</b><br />";
					else $output .= $this->dump_php_recursive($value, "['$name']", $indent.$do_dump_indent, $reference, $depth);
					}
				$output .= "$indent)<br />";
				}
			elseif(is_object($avar))
				{
				$parent_class = get_parent_class($avar) ? ' extends '.get_parent_class($avar) : '';
				
				$output .= "$indent$var_name <span style='color:$grey'>$type(".get_class($avar).$parent_class.")</span><br />$indent{<br />";

				// output methods
				$reflectionClass = new ReflectionClass( get_class($avar) );
				foreach ($reflectionClass->getMethods() as $method)
					{
					$output .= "$indent$do_dump_indent";
					$output .= "<span style='color: #0099c5'>";
					$output .=  '-> '.$method->getName().'(';
					$params = array();
					foreach ($method->getParameters() as $param)
						{
						$temp = ($param->isPassedByReference() ? '&' : '') . '$'.$param->getName();
						if ($param->isOptional()) $temp = "[".$temp."]";
						$params[] = $temp;
						}
					$output .= implode(', ', $params).')</span><br />';
					}

				// output members
				$members = get_object_vars($avar);
				if (count($members) > 0) $output .= "$indent$do_dump_indent<br />";
				foreach($members as $name=>$value)
					{
					if ($depth > $this->maxdepth ) $output .= "$indent$do_dump_indent<b style='color:red'>Too much recursion ...</b><br />";
					else $output .= $this->dump_php_recursive($value, "$${name}", $indent.$do_dump_indent, $reference, $depth);
					}
				$output .= "$indent}<br />";
				}
			elseif(is_resource($avar))
				{
				$output .= "$indent$var_name <span style='color:$grey'>$type(".get_resource_type($avar).")</span><br />$indent{<br />";
				$meta_data = stream_get_meta_data($avar);
				foreach($meta_data as $key=>$value)
					{
					$output .= $this->dump_php_recursive($value, "['$key']", $indent.$do_dump_indent, $reference);
					}
				$output .= "$indent)<br />";
				}
			else // for boolean, string, integer, float
				{
				$output .= "$indent$var_name = <span style='color:$grey'>$type(".strlen((string)$avar).")</span> <span style='color: ${colors[$type]}'>";
				
				// rewrite output
				if(is_string($avar)) $output .= "\"".htmlspecialchars($avar)."\"</span><br />";
				elseif(is_bool($avar)) $output .= ($avar == 1 ? "TRUE":"FALSE")."</span><br />";
				elseif(is_null($avar)) $output .= "NULL</span><br />";
				else $output .= "$avar</span><br />";
				}
			
			$var = $var[$keyvar];
			}
		
		return $output;
		}
	}
