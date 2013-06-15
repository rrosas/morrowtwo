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


namespace Morrow\Helpers;

class File {
	public static function safeFileName($filename) {
		$pathinfo = pathinfo($filename);
		$pattern = $pathinfo['filename'];
		$pattern = preg_replace("=[^\w\*\?]=i", '', $pattern);
		$pattern = strtolower($pattern);
		$pattern = preg_replace('=([\*|\?])=', '.$1', $pattern);
		if(isset($pathinfo['extension'])) $pattern . "." . $pathinfo['extension'];
		return $pattern;
	}

	public static function fileSize($a, $dec_point = '.', $thousands_sep = ',') {
		$unim = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
		$c = 0;
		while ($a>=1024) {
			$c++;
			$a = $a/1024;
		}
		return number_format($a, ($c ? 2 : 0), $dec_point, $thousands_sep).' '.$unim[$c];
	}

	public static function dirlist($dir, $endings = null) {
		if(!is_null($endings) && !is_array($endings)) $endings = array($endings);
		$d = dir($dir);
		$list = array();
		while (false !== ($entry = $d->read())) {
			if($entry{0} == '.') continue;
			$pi = pathinfo($entry);
			if(!is_dir($dir . "/" . $entry) && ($endings != null && (!isset($pi['extension']) || !in_array($pi['extension'], $endings)))) continue;
			$k = 'files';
			if(is_dir($dir . "/" . $entry)) $k = 'dirs';
			$list[$k][] = $entry;
		}
		$d->close();
		return $list;
	}

	public static function getMimeType($file) {
		$mime_types = array(
			'ai' => 'application/postscript',
			'aif' => 'audio/x-aiff',
			'aiff' => 'audio/x-aiff',
			'asf' => 'video/x-ms-asf',
			'asx' => 'video/x-ms-asf',
			'au' => 'audio/basic',
			'avi' => 'video/x-msvideo',
			'axs' => 'application/olescript',
			'bas' => 'text/plain',
			'bin' => 'application/octet-stream',
			'bmp' => 'image/bmp',
			'c' => 'text/plain',
			'cdf' => 'application/x-cdf',
			'class' => 'application/octet-stream',
			'clp' => 'application/x-msclip',
			'crd' => 'application/x-mscardfile',
			'css' => 'text/css',
			'dcr' => 'application/x-director',
			'dir' => 'application/x-director',
			'dll' => 'application/x-msdownload',
			'dms' => 'application/octet-stream',
			'doc' => 'application/msword',
			'dot' => 'application/msword',
			'dvi' => 'application/x-dvi',
			'dxr' => 'application/x-director',
			'eps' => 'application/postscript',
			'exe' => 'application/octet-stream',
			'flr' => 'x-world/x-vrml',
			'gif' => 'image/gif',
			'gtar' => 'application/x-gtar',
			'gz' => 'application/x-gzip',
			'h' => 'text/plain',
			'hlp' => 'application/winhlp',
			'hqx' => 'application/mac-binhex40',
			'hta' => 'application/hta',
			'htc' => 'text/x-component',
			'htm' => 'text/html',
			'html' => 'text/html',
			'htt' => 'text/webviewhtml',
			'ico' => 'image/x-icon',
			'iii' => 'application/x-iphone',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'js' => 'application/x-javascript',
			'latex' => 'application/x-latex',
			'lha' => 'application/octet-stream',
			'lzh' => 'application/octet-stream',
			'm3u' => 'audio/x-mpegurl',
			'mdb' => 'application/x-msaccess',
			'mid' => 'audio/mid',
			'mov' => 'video/quicktime',
			'movie' => 'video/x-sgi-movie',
			'mp2' => 'video/mpeg',
			'mp3' => 'audio/mpeg',
			'mpeg' => 'video/mpeg',
			'mpg' => 'video/mpeg',
			'ms' => 'application/x-troff-ms',
			'mvb' => 'application/x-msmediaview',
			'pbm' => 'image/x-portable-bitmap',
			'pdf' => 'application/pdf',
			'pgm' => 'image/x-portable-graymap',
			'png' => 'image/png',
			'pot' => 'application/vnd.ms-powerpoint',
			'pps' => 'application/vnd.ms-powerpoint',
			'ppt' => 'application/vnd.ms-powerpoint',
			'ps' => 'application/postscript',
			'pub' => 'application/x-mspublisher',
			'qt' => 'video/quicktime',
			'ra' => 'audio/x-pn-realaudio',
			'ram' => 'audio/x-pn-realaudio',
			'rgb' => 'image/x-rgb',
			'rmi' => 'audio/mid',
			'rtf' => 'application/rtf',
			'rtx' => 'text/richtext',
			'scd' => 'application/x-msschedule',
			'sct' => 'text/scriptlet',
			'sh' => 'application/x-sh',
			'sit' => 'application/x-stuffit',
			'snd' => 'audio/basic',
			'spl' => 'application/futuresplash',
			'stm' => 'text/html',
			'svg' => 'image/svg+xml',
			'tar' => 'application/x-tar',
			'tcl' => 'application/x-tcl',
			'tex' => 'application/x-tex',
			'texi' => 'application/x-texinfo',
			'texinfo' => 'application/x-texinfo',
			'tgz' => 'application/x-compressed',
			'tif' => 'image/tiff',
			'tiff' => 'image/tiff',
			'tsv' => 'text/tab-separated-values',
			'txt' => 'text/plain',
			'vcf' => 'text/x-vcard',
			'vrml' => 'x-world/x-vrml',
			'wav' => 'audio/x-wav',
			'wcm' => 'application/vnd.ms-works',
			'wdb' => 'application/vnd.ms-works',
			'wks' => 'application/vnd.ms-works',
			'wmf' => 'application/x-msmetafile',
			'wps' => 'application/vnd.ms-works',
			'wri' => 'application/x-mswrite',
			'wrl' => 'x-world/x-vrml',
			'wrz' => 'x-world/x-vrml',
			'xaf' => 'x-world/x-vrml',
			'xbm' => 'image/x-xbitmap',
			'xla' => 'application/vnd.ms-excel',
			'xlc' => 'application/vnd.ms-excel',
			'xlm' => 'application/vnd.ms-excel',
			'xls' => 'application/vnd.ms-excel',
			'xlt' => 'application/vnd.ms-excel',
			'xlw' => 'application/vnd.ms-excel',
			'xof' => 'x-world/x-vrml',
			'xpm' => 'image/x-xpixmap',
			'z' => 'application/x-compress',
			'zip' => 'application/zip'
		);
		
		$file = pathinfo($file);
		if (isset($file['extension'])) $ext = $file['extension'];
		else $ext = 'unknown';
		if (isset($mime_types[$ext])) return $mime_types[$ext];
		else return 'application/x-'.$ext;
	}

	public static function rmdir_recurse($path) {
		if (!file_exists($path)) return;
		
		$path = rtrim($path, '/').'/';
		$handle = opendir($path);
		for (; false !== ($file = readdir($handle));) {
			if($file == "." or $file == ".." ) continue;
			
			$fullpath = $path.$file;
			if (!is_link($fullpath) && is_dir($fullpath)) {
				self::rmdir_recurse($fullpath);
			} else {
				unlink($fullpath);
			}
		}
		closedir($handle);
		rmdir($path);
	}
	
	public static function copy_recurse($src, $dst, $file_permissions, $dir_permissions, $excludes = array()) {
		// clean the excludes
		foreach ($excludes as $key=>$value) {
			$excludes[$key] = trim($value, '/');
		}

		if (is_string($file_permissions)) $file_permissions = octdec($file_permissions);
		if (is_string($dir_permissions)) $dir_permissions = octdec($dir_permissions);
		
		$src = self::cleanPath( $src );
		$dst = self::cleanPath( $dst );
		
		$dir = opendir($src);
		
		if (!file_exists($dst)) {
			mkdir($dst);
			chmod($dst, $dir_permissions);
		}
		
		while (false !== ( $file = readdir($dir)) ) {
			if ($file{0} == '.') continue;

			if (is_link($src . $file)) {
				$target = $src . $file;
				$target = realpath($target);
				symlink( $target, $dst . $file );
			} elseif (is_dir($src . $file)) {
				foreach ($excludes as $e) {
					if ($file == $e) continue;
					self::copy_recurse($src . $file, $dst . $file, $file_permissions, $dir_permissions, $excludes);
				}
			} else {
				copy($src . $file, $dst . $file);
				chmod($dst . $file, $file_permissions);
			}
		}
		closedir($dir);
	} 
}
