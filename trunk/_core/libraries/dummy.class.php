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


namespace Morrow\Core\Libraries;

class Dummy {
	protected $encoding;
	
	public $salutation;
	public $firstname;
	public $lastname;
	
	public function __construct( $encoding = 'utf-8' ) {
		$this->encoding = $encoding;
		
		$this->salutation['female'] = 'Frau';
		$this->salutation['male'] = 'Herr';
		
		$this->firstname['female'] = array('Hannah', 'Leoni', 'Lena', 'Anna', 'Lea', 'Lara', 'Mia', 'Laura', 'Lilly', 'Emily', 'Sarah', 'Emma', 'Neele', 'Marie', 'Sophie', 'Johanna', 'Julia', 'Maya', 'Lisa', 'Lina', 'Amelie',
			'Alina', 'Leni', 'Sophia', 'Louisa', 'Paula', 'Clara', 'Angelina', 'Josephine', 'Charlotte', 'Jana', 'Chiara', 'Annika', 'Jule', 'Yasmin', 'Zoe', 'Finja', 'Pia', 'Katharina', 'Emilia', 'Fiona',
			'Antonia', 'Victoria', 'Franziska', 'Vanessa', 'Celina', 'Emely', 'Melina', 'Isabel', 'Michelle');
		$this->firstname['male'] = array('Leon', 'Lucas', 'Luca', 'Finn', 'Tim', 'Felix', 'Jonas', 'Louis', 'Maximilian', 'Julian', 'Max', 'Paul', 'Niclas', 'Jan', 'Ben', 'Elias', 'Yannik', 'Philip',
			'Noah', 'Tom', 'Moritz', 'Nico', 'David', 'Nils', 'Simon', 'Fabian', 'Eric', 'Justin', 'Alexander', 'Jacob', 'Florian', 'Nick', 'Linus', 'Mika', 'Jason', 'Daniel',
			'Lennard', 'Marvin', 'Yannis', 'Tobias', 'Dominic', 'Marlon', 'Marc', 'Johannes', 'Jonathan', 'Julius', 'Collin', 'Joel', 'Kevin', 'Vincent');
		$this->lastname = array('Müller', 'Schmidt', 'Schneider', 'Fischer', 'Meyer', 'Weber', 'Becker', 'Wagner', 'Schulz', 'Herrmann', 'Schäfer', 'Bauer', 'Koch', 'Richter', 'Klein', 'Wolf', 'Schröder', 'Neumann',
			'Zimmermann', 'Krüger', 'Hoffmann', 'Braun', 'Schmitz', 'Schmitt', 'Hartmann', 'Lange', 'Krause', 'Schmid', 'Werner', 'Schwarz', 'Meier', 'Lehmann', 'Köhler', 'Schulze', 'Maier',
			'Walter', 'Huber', 'Mayer', 'Kaiser', 'Peters', 'Weiß', 'Möller', 'Peter', 'Frank', 'König', 'Sommer', 'Stein', 'Winter', 'Berger', 'Hansen');
		$this->city = array('Berlin', 'Hamburg', 'München', 'Köln', 'Frankfurt am Main', 'Essen', 'Dortmund', 'Stuttgart', 'Düsseldorf', 'Bremen', 'Hannover', 'Duisburg', 
			'Leipzig', 'Nürnberg', 'Dresden', 'Bochum', 'Wuppertal', 'Bielefeld', 'Mannheim', 'Bonn', 'Münster');
		$this->tld = array('de', 'com', 'info', 'net', 'org', 'ch', 'at');
	}
	
	public function get( $gender = null ) {
		if (!isset($gender)) {
			$genders = array('male', 'female');
			$gender = $genders[ array_rand($genders) ];
		}
		
		$data['salutation'] = $this->getSalutation( $gender );
		$data['firstname'] = $this->getFirstname( $gender );
		$data['lastname'] = $this->getLastname();
		$data['zip'] = $this->getZip();
		$data['city'] = $this->GetCity();
		$data['email'] = $this->getEmail( $data['firstname'], $data['lastname'] );
		$data['nickname'] = $this->getNickname( $data['firstname'], $data['lastname'] );
		
		return $data;
	}

	public function getSalutation( $gender ) {
		$gender = $this->salutation[ $gender ];
		return $this->_encode( $gender );
	}
	
	public function getFirstname( $gender ) {
		$count = count($this->firstname[ $gender ]);
		$nr = rand(0, $count-1);
		$data = $this->firstname[ $gender ][ $nr ];
		
		if (rand(1,5) == 5) {
			$nr = rand(0, $count-1);
			$data .= '-'.$this->firstname[ $gender ][ $nr ];
		}
		
		$data = $this->_encode( $data );
		return $data;
	}
	
	public function getLastname() {
		$count = count($this->lastname);
		$nr = rand(0, $count-1);
		$data = $this->lastname[ $nr ];
		
		if (rand(1,5) == 5) {
			$nr = rand(0, $count-1);
			$data .= '-'.$this->lastname[ $nr ];
		}

		if (rand(0,1)) $data = $this->_simplify( $data );
			
		$data = $this->_encode( $data );
		return $data;
	}
	
	public function getCity() {
		$count = count($this->city);
		$nr = rand(0, $count-1);
		$data = $this->city[ $nr ];
		if (rand(0,1)) $data = $this->_simplify( $data );

		$data = $this->_encode( $data );
		return $data;
	}

	public function getZip() {
		$data = rand(1000, 99999);
		$data = str_pad($data, 5, '0', STR_PAD_LEFT);
		return $data;
	}

	public function getEmail( $firstname, $lastname ) {
		$data = $this->getNickname($firstname, $lastname);
		$data = strtolower($data);
		
		// create the the second part
		$count = count($this->tld);
		$nr = rand(0, $count-1);
		$host = $this->_getMnemonicValue().'.'.$this->tld[ $nr ];
		$data .= '@'.$host;
		
		$data = $this->_simplify( $data );
		return $data;
	}
	
	protected function getNickname( $firstname, $lastname ) {
		// create variations of names
		$variation[] = $firstname . '_' . rand(0,99);
		$variation[] = $firstname . rand(0,99);
		$variation[] = $firstname . '_' . rand(1960,1995);
		$variation[] = $firstname . rand(1960,1995);

		$variation[] = $lastname . '_' . rand(0,99);
		$variation[] = $lastname . rand(0,99);
		$variation[] = $lastname . '_' . rand(1960,1995);
		$variation[] = $lastname . rand(1960,1995);

		$variation[] = $firstname . '.' . $lastname;
		$variation[] = $firstname{0} . '.' . $lastname;
		$variation[] = $firstname . '.' . $lastname{0};
		
		$count = count($variation);
		$data = $variation[ rand(0, $count-1) ];
		return $data		;
	}
	
	protected function _encode( $data ) {
		return iconv("ISO-8859-1", $this->encoding, $data);
	}

	protected function _simplify($data) {
		$replacements['in'] = array('ö', 'ä', 'ü', 'ß');
		$replacements['out'] = array('oe', 'ae', 'ue', 'ss');

		return str_replace($replacements['in'], $replacements['out'], $data);
	}
		
	protected function _getMnemonicValue() {
		$vowels = "aeiouy";
		$consonants = "bcdfghjklmnprstvwxz"; 
		for ($i=0; $i<4; $i++) {
			$password[] = $consonants [ rand(0, strlen($consonants)-1) ];
			$password[] = $vowels [ rand(0, strlen($vowels)-1) ];
		}
		return implode('', $password);
	}
}
