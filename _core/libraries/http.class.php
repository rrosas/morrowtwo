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




class Http
	{
	public $debug			= false;
	public $error			= '';
	public $timeout			= 3;
	public $stream_timeout	= 5;
	public $responseHeader	= '';
	public $responseHeaders	= '';
	public $responseBody	= '';
	
	private $boundary		= '';
	private $custom_headers = false;

	public function __construct()
		{
		$this->boundary = '---------------------' . substr(md5(rand(0,32000)),0,10);
		}

	public function addHeader($header, $value)
		{
		$this->custom_headers[$header] = $value;
		}

	protected function _addValue($fieldname, $value)
		{
		$returner = '';
		$returner.= '--' . $this->boundary . "\r\n";
		$returner.= 'Content-Disposition: form-data; name="' . $fieldname . '"'."\r\n";
		$returner.= "\r\n" . $value . "\r\n";
		return $returner;
		}
	
	protected function _addFile($fieldname, $path, $mimetype)
		{
		if(is_file($path))
			{
			$data = file_get_contents($path);

			$returner = '';
			$returner.= "--" . $this->boundary . "\r\n";
			$returner.= "Content-Disposition: form-data; name=\"" . $fieldname . "\"; filename=\"" . basename($path) . "\"\r\n";
			$returner.= "Content-Type: " . $mimetype . "\r\n";
			$returner.= "Content-Transfer-Encoding: binary\r\n\r\n";
			$returner.= $data."\r\n";
			
			return $returner;
			}
		}

	protected function _parseHeaders( $response_headers )
		{
		$response_headers = explode("\r\n", trim($response_headers) );
		$head = explode(' ', trim(array_shift($response_headers)), 3);
		
		$returner['http_version']	= $head[0];
		$returner['status_code']	= $head[1];
		$returner['reason_phrase']	= (isset($head[2])) ? $head[2] : '';
		
		foreach ($response_headers as $key=>$value)
			{
			$values = explode(':', $value, 2);
			array_map('trim', $values);
			$returner[ strtolower( $values[0]) ]	= trim($values[1]);
			}
		
		$values = explode(';', $returner['content-type']);
		$returner['mimetype'] = strtolower($values[0]);
		$returner['charset'] = '';
		if (isset($values[1]))
			{
			$values = explode('=', $values[1]);
			$returner['charset'] = strtolower($values[1]);
			}

		return $returner;
		}
	
	protected function _parseUrl( $url )
		{
		$url        = parse_url( trim($url) );
		
		$url['connect_host'] = $url['host'];
		if ($url['scheme'] == 'https')
			{
			$url['connect_host'] = "ssl://" . $url['host'];
			if ( !isset($url['port'])) $url['port'] = 443;
			}
		if ( !isset($url['port'])) $url['port'] = 80;

		if (!isset($url['path'])) $url['path'] = '/';
		$url['request'] = $url['path'];
		if (isset($url['query'])) $url['request'] .= '?' . $url['query'];
		if (isset($url['fragment'])) $url['request'] .= '#' . $url['fragment'];
		
		return $url;
		}
	
	protected function _writeAndRead($url, $headers)
		{
		$fp = fsockopen($url['connect_host'], $url['port'], $errno, $errstr, $this->timeout);
		stream_set_timeout($fp, $this->stream_timeout);
	
		$response = '';
		fputs($fp, $headers);
		while(!feof($fp)) $response .= fread($fp, 1024);
		$info = stream_get_meta_data($fp);

		fclose($fp);
		
		if ($info['timed_out']) throw new Exception('Connected, but reading timed out.');
		
		return $response;
		}

	public function Head($url)
		{
		$this->error = '';

		// explode url
		$url = $this->_parseUrl( $url );
		
		// Build the header
		$headers = array();
		$headers[] = "HEAD " . $url['request'] . " HTTP/1.0";
		$headers[] = "Host: " . $url['host'];
		$headers[] = "Connection: Close";

		// Add custom headers
		if(is_array($this->custom_headers) && count($this->custom_headers) > 0)
			{
			foreach ($this->custom_headers as $header => $value)
				{
				$headers[] = $header . ": " . $value;
				}
			}

		$headers = implode( "\r\n", $headers ) . "\r\n\r\n";
		
		// Open the connection
		try
			{
			$response = $this->_writeAndRead( $url, $headers);
			}
		catch (Exception $e)
			{
			$this->error = $e->getMessage();
			return false;
			}
			
		if(preg_match('=^HTTP=i', $response))
			{
			$this->responseHeader = $response;
			$this->responseHeaders = $this->_parseHeaders( $response );
			$this->responseBody = '';
			}
		else return false;

		if($this->debug == true)
			{
			dump($headers);
        	dump($response);
			}

		return true;
		}

	public function Get($url)
		{
		$this->error = '';

		// explode url
		$url = $this->_parseUrl( $url );
		
		// Build the header
		$headers = array();
		$headers[] = "GET " . $url['request'] . " HTTP/1.0";
		$headers[] = "Host: " . $url['host'];
		$headers[] = "Connection: Close";

		// Add custom headers
		if(is_array($this->custom_headers) && count($this->custom_headers) > 0)
			{
			foreach ($this->custom_headers as $header => $value)
				{
				$headers[] = $header . ": " . $value;
				}
			}

		$headers = implode( "\r\n", $headers ) . "\r\n\r\n";
		
		// Open the connection
		try
			{
			$response = $this->_writeAndRead( $url, $headers);
			}
		catch (Exception $e)
			{
			$this->error = $e->getMessage();
			return false;
			}
		
		if(preg_match('=^HTTP=i', $response))
			{
			$res = preg_split("=(\r\n\r\n|\r\r|\n\n)=", $response, 2);
			$this->responseHeader = $res[0];
			$this->responseHeaders = $this->_parseHeaders( $res[0] );
			$this->responseBody = $res[1];
			}
		else return false;

		if($this->debug == true)
			{
			dump($headers);
        	dump($response);
			}

		return true;
		}

	public function Post($url, $data = array(), $files = array())
		{
		$this->error = '';

		// explode url
		$url = $this->_parseUrl( $url );

		// Build the header
		$headers = array();
		$headers[] = "POST " . $url['request'] . " HTTP/1.0";
		$headers[] = "Host: " . $url['host'];
		$headers[] = "Content-type: multipart/form-data; boundary=" . $this->boundary;
		$headers[] = "Connection: Close";

		// Add custom headers
		if(is_array($this->custom_headers) && count($this->custom_headers) > 0)
			{
			foreach ($this->custom_headers as $header => $value)
				{
				$headers[] = $header . ": " . $value;
				}
			}

		$data = '';
		
		// Compose data
		if(isset($data) && is_array($data))
			{
			foreach($data AS $fieldname => $value)
				{
	            $data .= $this->_addValue($fieldname, $value);
				}
			}

        // Compose files
        if(isset($files) && is_array($files))
			{
        	foreach($files as $fieldname => $file)
				{
        		$data .= $this->_addFile($fieldname, $file['path'], $file['mimetype']);
				}
			}

		// Close post data and headers
		$data   .= "--" . $this->boundary . "--\r\n";
		$headers[] = "Content-length: " . strlen($data);
		$headers = implode( "\r\n", $headers ) . "\r\n\r\n";

		// Open the connection
		try
			{
			$response = $this->_writeAndRead( $url, $headers);
			}
		catch (Exception $e)
			{
			$this->error = $e->getMessage();
			return false;
			}

		if(preg_match('=^HTTP=i', $response))
			{
			$res = preg_split("=(\r\n\r\n|\r\r|\n\n)=", $response, 2);
			$this->responseHeader = $res[0];
			$this->responseHeaders = $this->_parseHeaders( $res[0] );
			$this->responseBody = $res[1];
			}
		else return false;

		if($this->debug == true)
			{
			dump($headers . $data);
			dump($response);
			}

		return true;
		}
	}
	
