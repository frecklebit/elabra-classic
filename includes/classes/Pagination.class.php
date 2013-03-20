<?php

class Paginator {
	var $items_per_page;
	var $items_total;
	var $current_page;
	var $num_pages;
	var $mid_range;
	var $low;
	var $high;
	var $limit;
	var $return;
	var $default_ipp = 30;
	var $querystring;

	function Paginator()
	{
		if ( ! isset($_GET['ipp'])) $_GET['ipp'] = null;
		if ( ! isset($_GET['page'])) $_GET['page'] = null;
		$this->current_page = 1;
		$this->mid_range = 7;
		$this->items_per_page = (!empty($_GET['ipp'])) ? $_GET['ipp']:$this->default_ipp;
	}

	function paginate()
	{	
		if($_GET['ipp'] == 'All')
		{
			$this->num_pages = ceil($this->items_total/$this->default_ipp);
			$this->items_per_page = $this->default_ipp;
		}
		else
		{
			if(!is_numeric($this->items_per_page) OR $this->items_per_page <= 0) $this->items_per_page = $this->default_ipp;
			$this->num_pages = ceil($this->items_total/$this->items_per_page);
		}
		$this->current_page = (int) $_GET['page']; // must be numeric > 0
		if($this->current_page < 1 Or !is_numeric($this->current_page)) $this->current_page = 1;
		if($this->current_page > $this->num_pages) $this->current_page = $this->num_pages;
		$prev_page = $this->current_page-1;
		$next_page = $this->current_page+1;

		if($_GET)
		{
			$args = explode("&",$_SERVER['QUERY_STRING']);
			foreach($args as $arg)
			{
				$keyval = explode("=",$arg);
				if($keyval[0] != "page" And $keyval[0] != "ipp") $this->querystring .= "&" . $arg;
			}
		}

		if($_POST)
		{
			foreach($_POST as $key=>$val)
			{
				if($key != "page" And $key != "ipp") $this->querystring .= "&$key=$val";
			}
		}

		if($this->num_pages > 10)
		{
			$this->return = ($this->current_page != 1 And $this->items_total >= 10) ? "<li class=\"previous\"><a href=\"$_SERVER[PHP_SELF]?page=$prev_page&ipp=$this->items_per_page$this->querystring\">&laquo; Previous</a></li> ":"<li class=\"previous-off\" href=\"#\">&laquo; Previous</li> ";

			$this->start_range = $this->current_page - floor($this->mid_range/2);
			$this->end_range = $this->current_page + floor($this->mid_range/2);

			if($this->start_range <= 0)
			{
				$this->end_range += abs($this->start_range)+1;
				$this->start_range = 1;
			}
			if($this->end_range > $this->num_pages)
			{
				$this->start_range -= $this->end_range-$this->num_pages;
				$this->end_range = $this->num_pages;
			}
			$this->range = range($this->start_range,$this->end_range);

			for($i=1;$i<=$this->num_pages;$i++)
			{
				if($this->range[0] > 2 And $i == $this->range[0]) $this->return .= "<li class=\"continued\"> ... </li>";
				// loop through all pages. if first, last, or in range, display
				if($i==1 Or $i==$this->num_pages Or in_array($i,$this->range))
				{
					$this->return .= ($i == $this->current_page And $_GET['page'] != 'All') ? "<li><a title=\"Go to page $i of $this->num_pages\" class=\"active\" href=\"#\">$i</a></li> ":"<li><a title=\"Go to page $i of $this->num_pages\" href=\"$_SERVER[PHP_SELF]?page=$i&ipp=$this->items_per_page$this->querystring\">$i</a></li> ";
				}
				if($this->range[$this->mid_range-1] < $this->num_pages-1 And $i == $this->range[$this->mid_range-1]) $this->return .= "<li class=\"continued\"> ... </li>";
			}
			$this->return .= (($this->current_page != $this->num_pages And $this->items_total >= 10) And ($_GET['page'] != 'All')) ? "<li class=\"next\"><a href=\"$_SERVER[PHP_SELF]?page=$next_page&ipp=$this->items_per_page$this->querystring\">Next &raquo;</a></li>":"<li class=\"next-off\" href=\"#\">Next &raquo;</li>";
			$this->return .= ($_GET['page'] == 'All') ? "<li><a class=\"active\" style=\"margin-left:10px\" href=\"#\">All</a></li> ":"<li><a style=\"margin-left:10px\" href=\"$_SERVER[PHP_SELF]?page=All&ipp=All$this->querystring\">All</a></li> ";
		}
		else
		{
			$this->return = ($this->current_page != 1 And $this->items_total >= 10) ? "<li class=\"previous\"><a href=\"$_SERVER[PHP_SELF]?page=$prev_page&ipp=$this->items_per_page$this->querystring\">&laquo; Previous</a></li> ":"<li class=\"previous-off\" href=\"#\">&laquo; Previous</li> ";
			
			if($_GET['page'] != 'All') {
				for($i=1;$i<=$this->num_pages;$i++)
				{
					$this->return .= ($i == $this->current_page And $_GET['page'] != 'All') ? "<li><a class=\"active\" href=\"#\">$i</a></li> ":"<li><a href=\"$_SERVER[PHP_SELF]?page=$i&ipp=$this->items_per_page$this->querystring\">$i</a></li> ";
				}
			}else{
				$this->return .= "<li><a href=\"$_SERVER[PHP_SELF]?page=1&ipp=$this->items_per_page$this->querystring\">1</a></li>";
			}
			
			$this->return .= (($this->current_page != $this->num_pages And $this->items_total >= 10) And ($_GET['page'] != 'All')) ? "<li><a href=\"$_SERVER[PHP_SELF]?page=$next_page&ipp=$this->items_per_page$this->querystring\">Next &raquo;</a></li>":"<li class=\"next-off\">Next &raquo;</li>";
			
			$this->return .= ($_GET['page'] == 'All') ? "<li><a class=\"active\" style=\"margin-left:10px\" href=\"#\">All</a></li> ":"<li><a style=\"margin-left:10px\" href=\"$_SERVER[PHP_SELF]?page=All&ipp=All$this->querystring\">All</a></li> ";
		}
		$this->low = ($this->current_page-1) * $this->items_per_page;
		$this->high = ($_GET['ipp'] == 'All') ? $this->items_total:($this->current_page * $this->items_per_page)-1;
		$this->limit = ($_GET['ipp'] == 'All') ? "":" LIMIT $this->low,$this->items_per_page";
	}

	function display_items_per_page()
	{
		$items = '';
		$ipp_array = array($this->default_ipp,$this->default_ipp*2,$this->default_ipp*3,'All');
		foreach($ipp_array as $ipp_opt)	$items .= ($ipp_opt == $this->items_per_page) ? "<option selected=\"selected\" value=\"$ipp_opt\">$ipp_opt</option>":"<option value=\"$ipp_opt\">$ipp_opt</option>";
		return "<select onchange=\"window.location='$_SERVER[PHP_SELF]?page=1&ipp='+this[this.selectedIndex].value+'$this->querystring';return false\">$items</select>";
	}

	function display_jump_menu()
	{
		for($i=1;$i<=$this->num_pages;$i++)
		{
			$option .= ($i==$this->current_page) ? "<option value=\"$i\" selected>$i</option>":"<option value=\"$i\">$i</option>";
		}
		return "<span>Page:</span><select onchange=\"window.location='$_SERVER[PHP_SELF]?page='+this[this.selectedIndex].value+'&ipp=$this->items_per_page$this->querystring';return false\">$option</select>";
	}

	function display_pages()
	{
		return $this->return;
	}
	
}