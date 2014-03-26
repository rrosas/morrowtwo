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

// http://akrabat.com/zend-framework-2/sending-an-html-with-text-alternative-email-with-zendmail/

namespace Morrow;

use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class Mail {
	protected $message;
	protected $transport;
	
	public function __construct($config) {
		$this->transport	= $this->_initTransports($config);
		$this->message		= $this->_initMessage($config);
	}
	
	public function getTransport() {
		return $this->transport;
	}

	public function getMessage() {
		return $this->message;
	}
	
	public function send() {
		$this->transport->send($this->message);
	}
	
	protected function _initTransports($config) {
		$class = strtolower($config['transport']['class']);
		
		// init Sendmail transport
		if ($class == 'sendmail') {
			$transport = new \Zend\Mail\Transport\Sendmail();
		// init Smtp transport
		} elseif ($class == 'smtp') {
			$transport	= new \Zend\Mail\Transport\Smtp();
			$options			= new \Zend\Mail\Transport\SmtpOptions($config['transport']['smtp']);
			$transport->setOptions($options);
		}

		return $transport;
	}
	
	protected function _initMessage($config) {
		$message = new \Zend\Mail\Message();
		$message
			-> setEncoding("UTF-8")
			-> addFrom($config['from'][0], $config['from'][1])
			-> setSender($config['from'][0], $config['from'][1])
		;
		return $message;
	}
}
	
