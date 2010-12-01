<?php

	class Paginate
	{
		// Default options
		public $options = array(
			'current_page'		=> '0',
			'total_results'		=> '0',
			'results_per_page'	=> '0',
			'maximum_links'		=> '10',
			'url_format'		=> '?page=[x]',
			'wrapper_class'		=> 'pagination',

			'first_page_link'	=> '&laquo; First',
			'last_page_link'	=> 'Last &raquo;',
			'page_info_box'		=> 'PAGE [x] OF [y]',
		);
		public $buffer;
		public $page_array;


		/*
		 * Return string containing the paging links.
		 * Pass in an array of options, overwriting defaults.
		 */
		function show_page_links($options_array)
		{
			// Merge the array of options over top of the default options
			if(is_array($options_array))
			{
				$this->options = array_merge($this->options, $options_array);
			}

			// There is no zero page
			if($this->options['current_page'] == 0)
			{
				$this->options['current_page'] = 1;
			}
			// Show the page links even if there is no value
			if($this->options['total_results'] == 0)
			{
				$this->options['total_results'] = 1;
			}

			// Check that the total results, and results per page are greater than zero
			if(is_numeric($this->options['total_results']) && is_numeric($this->options['results_per_page']) && $this->options['total_results'] > 0 && $this->options['results_per_page'] > 0)
			{
				// Handle the math for the paging links
				$this->build_pagination();
				// Build the html content
				$this->buffer = "<div class=\"{$this->options['wrapper_class']}\">\n";
				$this->buffer .= $this->print_page_info();
				$this->buffer .= $this->first_page_link();
				$this->buffer .= $this->print_numbers();
				$this->buffer .= $this->last_page_link();
				$this->buffer .= "</div>\n";
			}

			// Return the html in the buffer
			return $this->buffer;
		}



		/*
		 * Calculate which links to show, and save
		 * to an array to be accessed later.
		 */
		function build_pagination()
		{
			// The number of links that have been printed (used in the loop later)
			$links_printed = 0;

			// Get the total number of pages
			$this->options['total_pages'] = ceil($this->options['total_results'] / $this->options['results_per_page']);

			// Guess where the paging links should start
			$page_start = $this->options['current_page'] - floor($this->options['maximum_links'] / 2);

			// Add links to the beginning if we will hit the end of the pages before the maximum number of links
			$end_links = ($page_start + $this->options['maximum_links']) - $this->options['total_pages'];
			if($end_links > 0)
			{
				$page_start -= $end_links;
			}

			// Create an array containing all of the pages that should be linked
			for($x = $page_start; $x <= $this->options['total_pages']; $x++)
			{
				// Make sure it is greater than 0, and we have not printed too many links
				if($x >= 1)
				{
					// See if we have hit our maximum link limit
					if($links_printed <= $this->options['maximum_links'])
					{
						$arr[$x] = $x;
						$links_printed++;
					}else{
						// End the loop early (we already have all of our links)
						$x = $this->options['total_pages']+1;
					}
				}
			}

			// Save the array for use later
			$this->page_array = $arr;
		}



		/*
		 * Return a string of HTML containing how many pages
		 * there are, and the current page.
		 */
		function print_page_info()
		{
			$info = "\t<span class=\"current\">" . $this->options['page_info_box'] . "</span>\n";
			$info = str_replace('[x]', $this->options['current_page'], $info);
			$info = str_replace('[y]', $this->options['total_pages'], $info);
			return $info;
		}



		/*
		 * Return an HTML string containing a link to the first page
		 * if the first page link is not currently being shown.
		 */
		function first_page_link()
		{
			// If we are not printing a link to page 1, show the first link
			if(empty($this->page_array[1]))
			{
				// Create the url for the link
				$link = str_replace('[x]', 1, $this->options['url_format']);
				return "\t<a href=\"{$link}\" class=\"first\">{$this->options['first_page_link']}</a>\n";
			}
		}



		/*
		 * Return an HTML string containing a link to the last page
		 * if the last page link is not currently being shown.
		 */
		function last_page_link()
		{
			// If we are not printing a link to the last page, show the last link
			if(empty($this->page_array[$this->options['total_pages']]))
			{
				// Create the url for the link
				$link = str_replace('[x]', $this->options['total_pages'], $this->options['url_format']);
				return "\t<a href=\"{$link}\" class=\"first\">{$this->options['last_page_link']}</a>\n";
			}
		}



		/*
		 * Return an HTML string containing a page links
		 */
		function print_numbers()
		{
			// Loop through and print the page links determined in the build function
			foreach($this->page_array as $x)
			{
				// Create the url for the link
				$link = str_replace('[x]', $x, $this->options['url_format']);
				// Buffer
				if($this->options['current_page'] != $x)
				{
					$return .= "\t<a href=\"{$link}\">{$x}</a>\n";
				}else{
					$return .= "\t<span class=\"current\">{$x}</span>\n";
				}
			}
			return $return;
		}




		// Compatibility function
		function paginate_url_id($options_array)
		{
			$options = array(
				'current_page' => '0',
				'total_results' => '0',
				'results_per_page' => '0',
				'query_string' => '',
			);
			if(is_array($options_array))
			{
				$options = array_merge($options, $options_array);
			}
			if(is_numeric($options['total_results']) && $options['total_results'] > 0)
			{
				for($x=0; $x<=$options['total_results']; $x+=$options['results_per_page'])
				{
					$j++;
					echo "<a href=\"\">{$j}</a> ";
				}
			}
		}
	}



	/*
	//	EXAMPLE USAGE

	$p = new Paginate;
	$options = array(
		'current_page' 		=> $_GET['page'],
		'total_results' 	=> '100',
		'results_per_page' 	=> '11',
		'maximum_links'		=> '8',
		'url_format' 		=> '?page=[x]',
	);
	echo $p->show_page_links($options);

	<style type="text/css">
	
		body {
			font: 9pt Arial, Helvetica, sans-serif;
		}
	
		.pagination {
			background: #555;
			width: 750px;
			padding: 40px 20px;
			text-align: center;
		}
	
		.pagination a, .pagination .current {
			padding: 6px 10px;
			border: 1px solid #999;
			background: #222;
			color: #FFF;
			text-decoration: none;
		}
	
		.pagination .current, .pagination .first {
			font-weight: bold;
		}
	
		.pagination a:hover {
			text-decoration: underline;
		}
	
	</style>
	*/

?>