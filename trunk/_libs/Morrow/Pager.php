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


namespace Morrow;

class Pager {
	/* These are defaults */
	public $TotalResults;
	public $CurrentPage = 1;
	public $PageVarName = "pager";
	public $ResultsPerPage = 20;
	public $LinksPerPage = 10;

	protected $input;

	public function __construct( $input ) {
		$this->input = $input;
		$this->CurrentPage = $this->getCurrentPage();
	}

	public function get() {
		$this->TotalPages = $this->getTotalPages();
		$this->ResultArray = array(
			"page_prev" => $this->getPrevPage(),
			"page_next" => $this->getNextPage(),
			"page_current" => $this->CurrentPage,
			"pages_total" => $this->TotalPages,
			"results_total" => $this->TotalResults,
			"mysql_limit" => $this->getLimit(),
			"mysql_limit1" => $this->getStartOffset(),
			"mysql_limit2" => $this->ResultsPerPage,
			"offset_start" => $this->getStartOffset(),
			"offset_end" => $this->getEndOffset(),
			"results_per_page" => $this->ResultsPerPage,
			"var" => $this->PageVarName,
		);
		return $this->ResultArray;
	}

	/* Start information functions */
	public function getTotalPages() {
		/* Make sure we don't devide by zero */
		$result = 1;
		if ($this->TotalResults != 0 && $this->ResultsPerPage != 0) {
			$result = ceil($this->TotalResults / $this->ResultsPerPage);
		}
		
		/* If 0, make it 1 page */
		if($result == 0) return 1;
		else return $result;
	}

	public function getStartOffset() {
		$offset = $this->ResultsPerPage * ($this->CurrentPage - 1);
		return $offset;
	}

	public function getEndOffset() {
		if ($this->getStartOffset() > ($this->TotalResults - $this->ResultsPerPage)) $offset = $this->TotalResults;
		elseif ($this->getStartOffset() != 0) $offset = $this->getStartOffset() + $this->ResultsPerPage - 1;
		else $offset = $this->ResultsPerPage;
		
		return $offset;
	}

	public function getCurrentPage() {
		if (isset($this->input[$this->PageVarName])) {
			$page = $this->input[$this->PageVarName];
			if (is_numeric($page) and $page > 0) $this->CurrentPage = $page;
		}
		return $this->CurrentPage;
	}

	public function getPrevPage() {
		if($this->CurrentPage > 1) return $this->CurrentPage - 1;
		else return false;
	}

	public function getNextPage() {
		if($this->CurrentPage < $this->TotalPages) return $this->CurrentPage + 1;
		else return false;
	}

	public function getStartNumber() {
		$links_per_page_half = $this->LinksPerPage / 2;
		/* See if curpage is less than half links per page */
		if($this->CurrentPage <= $links_per_page_half || $this->TotalPages <= $this->LinksPerPage) return 1;
		/* See if curpage is greater than TotalPages minus Half links per page */
		elseif($this->CurrentPage >= ($this->TotalPages - $links_per_page_half)) return $this->TotalPages - $this->LinksPerPage + 1;
		else return $this->CurrentPage - $links_per_page_half;
	}

	public function getEndNumber() {
		if($this->TotalPages < $this->LinksPerPage) return $this->TotalPages;
		else return $this->getStartNumber() + $this->LinksPerPage - 1;
	}

	public function getLimit() {
		return $this->getStartOffset() . ',' . $this->ResultsPerPage;
	}
}
