<?php

	class Validate
	{
		public $data;
		public $rules;
		public $error;

		public $print_errors = true;
		public $print_field_title = true;

		function add_error($blah)
		{
			
		}
		
		function add_rules($rules)
		{
			if(is_array($rules))
			{
				if(is_array($this->rules))
				{
					$this->rules = array_merge($this->rules, $rules);
				}else{
					$this->rules = $rules;
				}
			}else{
				$this->add_error('Invalid rules passed to Validate->add_rules(). Array expected.');
				return FALSE;
			}
			return TRUE;
		}


		function run($data, $rules = FALSE)
		{
			if($rules !== FALSE)
			{
				if($this->add_rules($rules) === FALSE)
				{
					return FALSE;
				}
			}

			$this->data = $data;
			if(is_array($this->rules))
			{
				foreach($this->rules as $k => $v)
				{
					$this->CheckField($k);
				}
			}
			if(empty($this->error))
			{
				return TRUE;
			}else{
				return FALSE;
			}
		}


		function CheckField($field_name)
		{
			$this->data_copy = $this->data;
			$field = $this->data_copy[$field_name];
			if(is_array($this->data_copy[$field_name]))
			{
				// If it is a checkbox array
				foreach($this->data_copy[$field_name] as $k=>$v)
				{
					if(!empty($v))
					{
						$this->data_copy[$field_name] = trim($v);
					}
				}
			}else{
				// Regular string data
				$this->data_copy[$field_name] = trim($this->data_copy[$field_name]);
			}

			foreach($this->rules[$field_name] as $type => $error)
			{
				// Required
				if($type == 'reqd' && empty($field) && strlen($field) == 0)
				{
					$this->error[$field_name][] = $error;
				}
				// Max length
				if(preg_match('/max\[(\d+)\]/i', $type, $m))
				{
					if(strlen($field) > $m[1])
					{
						$this->error[$field_name][] = $error;
					}
				}
				// Min length
				if(preg_match('/min\[(\d+)\]/i', $type, $m))
				{
					if(strlen($field) < $m[1])
					{
						$this->error[$field_name][] = $error;
					}
				}
				// Exact length
				if(preg_match('/exact\[(\d+)\]/i', $type, $m))
				{
					if(strlen($field) != $m[1])
					{
						$this->error[$field_name][] = $error;
					}
				}
				// Alpha
				if($type == 'alpha' && !ctype_alpha(str_replace(' ', '', $field)))
				{
					$this->error[$field_name][] = $error;
				}
				// Numeric
				if($type == 'numeric' && !ctype_digit(str_replace('.', '', $field)))
				{
					$this->error[$field_name][] = $error;
				}

				// Money
				if($type == 'money' && !ctype_digit(str_replace('$', '', str_replace(',', '', str_replace('.', '', $field)))))
				{
					$this->error[$field_name][] = $error;
				}

				// Numeric with thousands
				if($type == 'numeric_thousands' && !ctype_digit(str_replace(',', '', $field)))
				{
					$this->error[$field_name][] = $error;
				}

				// Alphanumeric
				if($type == 'alphanumeric' && !ctype_alnum(str_replace(' ', '', $field)))
				{
					$this->error[$field_name][] = $error;
				}
				// No spaces
				if($type == 'no_space' && $field != str_replace(' ', '', $field))
				{
					$this->error[$field_name][] = $error;
				}
				// Email
				if($type == 'email' && !eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $field))
				{
					$this->error[$field_name][] = $error;
				}
				// Valid url
				if($type == 'valid_url' && strlen($this->GetPage($field)) <= 10)
				{
					$this->error[$field_name][] = $error;
				}

				// Date (5/10/09)(05/10/2009)
				if($type == 'date' && !eregi('^([0-1]{1})?([0-9]{1})/([0-3]{1})([0-9]{1})/([0-9]{2,4})$', $field))
				{
					$this->error[$field_name][] = $error;
				}

				// Time
				if($type == 'time' && !eregi('^([0-1]{1})?([0-9]{1}):([0-5]{1})([0-9]{1})( )?([AaPp][Mm])$', $field))
				{
					$this->error[$field_name][] = $error;
				}


				// Conditional
				if(substr($type, 0, 4) == 'cond' && empty($field))
				{
					if($type == 'cond')
					{
						$conditional = true;
					}else{
						$cond_statement = preg_match('/cond\[(.*)=(.*)\]/i', $type, $m);
						if($cond_statement)
						{
							if($this->data[$m['1']] != $m['2'])
							{
								$conditional = true;
							}else{
								$this->error[$field_name][] = $error;
							}
						}
					}
				}
			}
			if($conditional == true)
			{
				unset($this->error[$field_name]);
			}
		}


		function print_field($field_name, $field_label, $field_type, $extra = '')
		{
			$this->data[$field_name] = stripslashes($this->data[$field_name]);

			$o = '';
			if($field_type == 'text')
			{
				$o .= '<div class="field_wrapper">';
				// If we need to print the field title
				if($this->print_field_title)
				{
					$o .= "<p class=\"field_name\">{$field_label}</p>";
				}

				if(is_array($this->error[$field_name]))
				{
					// If we need to print the errors
					if($this->print_errors)
					{
						$o .= "<div class=\"validation_error\">";
						foreach($this->error[$field_name] as $line)
						{
							$o .= "<p>{$line}</p>";
							break;
						}
						$o .= "</div>";
					}
					$hilight = ' class="validation_error_border" ';
				}
				$o .= '<div class="field_input">';
				$o .= "<input type=\"text\" name=\"{$field_name}\" id=\"{$field_name}\" value=\"{$this->data[$field_name]}\" {$extra} {$hilight} />";
				$o .= '</div>';
				$o .= '</div>';


			}else if($field_type == 'password')
			{
				$o .= '<div class="field_wrapper">';
				// If we need to print the field title
				if($this->print_field_title)
				{
					$o .= "<p class=\"field_name\">{$field_label}</p>";
				}

				if(is_array($this->error[$field_name]))
				{
					// If we need to print the errors
					if($this->print_errors)
					{
						$o .= "<div class=\"validation_error\">";
						foreach($this->error[$field_name] as $line)
						{
							$o .= "<p>{$line}</p>";
							break;
						}
						$o .= "</div>";
					}
					$hilight = ' class="validation_error_border" ';
				}
				$o .= '<div class="field_input">';
				$o .= "<input type=\"password\" name=\"{$field_name}\" id=\"{$field_name}\" value=\"{$this->data[$field_name]}\" {$extra} {$hilight} />";
				$o .= '</div>';
				$o .= '</div>';
				
			}else if($field_type == 'textarea'){


				$o .= '<div class="field_wrapper">';
				// If we need to print the field title
				if($this->print_field_title)
				{
					$o .= "<p class=\"field_name\">{$field_label}</p>";
				}

				if(is_array($this->error[$field_name]))
				{
					// If we need to print the errors
					if($this->print_errors)
					{
						$o .= "<div class=\"validation_error\">";
						foreach($this->error[$field_name] as $line)
						{
							$o .= "<p>{$line}</p>";
							break;
						}
						$o .= "</div>";
					}
					$hilight = ' class="validation_error_border" ';
				}
				$o .= '<div class="field_input">';
				$o .= "<textarea name=\"{$field_name}\" id=\"{$field_name}\" {$extra} {$hilight}>{$this->data[$field_name]}</textarea>";
				$o .= '</div>';
				$o .= '</div>';
			}
			$o .= "\n";
			return $o;
		}


		function print_select($field_name, $field_label, $values, $extra = '')
		{
			$field_name_copy = $field_name;
			$field_name = str_replace('[]', '', $field_name);
			$selected_values = $this->data[$field_name];
			if(!is_array($selected_values))
			{
				unset($selected_values);
				$selected_values[] = $this->data[$field_name];
			}
			$o = '';
			$o .= '<div class="field_wrapper">';
			// If we need to print the field title
			if($this->print_field_title)
			{
				$o .= "<p class=\"field_name\">{$field_label}</p>";
			}

			if(is_array($this->error[$field_name]))
			{
				// If we need to print the errors
				if($this->print_errors)
				{
					$o .= "<div class=\"validation_error\">";
					foreach($this->error[$field_name] as $line)
					{
						$o .= "<p>{$line}</p>";
						break;
					}
					$o .= "</div>";
				}
				#$hilight = ' class="validation_error_border" ';
			}
			$o .= '<div class="field_input">';

			$o .= "<select name=\"{$field_name_copy}\" id=\"{$field_name}\" {$extra} {$hilight} />";

			foreach($values as $k => $v)
			{
				$selected = '';
				if(in_array(html_entity_decode($k), $selected_values))
				{
					$selected = ' selected ';
				}
				$o .= '<option value="' . $k . '" ' . $selected . '>';
				$o .= $v;
				$o .= '</option>';
			}
			$o .= "</select>";
			$o .= '</div>';
			$o .= '</div>';
			$o .= "\n";
			return $o;
		}







		function print_checkbox($field_name, $field_label, $values, $extra = '')
		{
			$o = '';
			$o .= '<div class="field_wrapper">';
			// If we need to print the field title
			if($this->print_field_title)
			{
				$o .= "<p class=\"field_name\">{$field_label}</p>";
			}

			if(is_array($this->error[$field_name]))
			{
				// If we need to print the errors
				if($this->print_errors)
				{
					$o .= "<div class=\"validation_error\">";
					foreach($this->error[$field_name] as $line)
					{
						$o .= "<p>{$line}</p>";
						break;
					}
					$o .= "</div>";
				}
				$hilight = ' class="validation_error_border_padded" ';
			}
			$o .= '<div class="field_input" ' . $hilight . '>';
			$o .= "<div {$hilight}>";
			foreach($values as $k => $v)
			{
				$selected = '';
				if(is_array($this->data[$field_name]) && in_array($k, $this->data[$field_name]))
				{
					$selected = ' checked ';
				}
				$o .= '<p><input type="checkbox" name="'.$field_name.'[]" value="' . $k . '" ' . $selected . ' '.$extra.'/> ' . $v . '</p>';
				//$o .= $v;
				//$o .= '</option>';
			}
			$o .= '</div>';
			$o .= '</div>';
			$o .= '</div>';
			$o .= "\n";
			return $o;
		}




		function print_radio($field_name, $field_label, $values, $extra = '')
		{
			$o = '';
			$o .= '<div class="field_wrapper">';
			// If we need to print the field title
			if($this->print_field_title)
			{
				$o .= "<p class=\"field_name\">{$field_label}</p>";
			}

			if(is_array($this->error[$field_name]))
			{
				// If we need to print the errors
				if($this->print_errors)
				{
					$o .= "<div class=\"validation_error\">";
					foreach($this->error[$field_name] as $line)
					{
						$o .= "<p>{$line}</p>";
						break;
					}
					$o .= "</div>";
				}
				$hilight = ' class="validation_error_border_padded" ';
			}
			$o .= '<div class="field_input" ' . $hilight . '>';
			$o .= "<div {$hilight}>";
			foreach($values as $k => $v)
			{
				$selected = '';
				if($k == $this->data[$field_name])
				{
					$selected = ' checked ';
				}
				$o .= '<p><input type="radio" name="'.$field_name.'" value="' . $k . '" ' . $selected . ' '.$extra.'/> ' . $v . '</p>';
				//$o .= $v;
				//$o .= '</option>';
			}
			$o .= '</div>';
			$o .= '</div>';
			$o .= '</div>';
			$o .= "\n";
			return $o;
		}





		function set_values($data)
		{
			if(is_array($data) && is_array($this->data))
			{
				$this->data = array_merge($this->data, $data);
			}else{
				$this->data = $data;
			}
		}


		function initialize_values($data)
		{
			if(is_array($data))
			{
				$this->data = $data;
			}
		}

		function GetPage($url)
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_TIMEOUT, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$output = curl_exec($ch);
			curl_close($ch);
			return $output;
		}


		function states($show_blank = TRUE)
		{
			$states = array (
			'AL' => 'ALABAMA',
			'AK' => 'ALASKA',
			'AZ' => 'ARIZONA',
			'AR' => 'ARKANSAS',
			'CA' => 'CALIFORNIA',
			'CO' => 'COLORADO',
			'CT' => 'CONNECTICUT',
			'DE' => 'DELAWARE',
			'FL' => 'FLORIDA',
			'GA' => 'GEORGIA',
			'GU' => 'GUAM',
			'HI' => 'HAWAII',
			'ID' => 'IDAHO',
			'IL' => 'ILLINOIS',
			'IN' => 'INDIANA',
			'IA' => 'IOWA',
			'KS' => 'KANSAS',
			'KY' => 'KENTUCKY',
			'LA' => 'LOUISIANA',
			'ME' => 'MAINE',
			'MD' => 'MARYLAND',
			'MA' => 'MASSACHUSETTS',
			'MI' => 'MICHIGAN',
			'MN' => 'MINNESOTA',
			'MS' => 'MISSISSIPPI',
			'MO' => 'MISSOURI',
			'MT' => 'MONTANA',
			'NE' => 'NEBRASKA',
			'NV' => 'NEVADA',
			'NH' => 'NEW HAMPSHIRE',
			'NJ' => 'NEW JERSEY',
			'NM' => 'NEW MEXICO',
			'NY' => 'NEW YORK',
			'NC' => 'NORTH CAROLINA',
			'ND' => 'NORTH DAKOTA',
			'OH' => 'OHIO',
			'OK' => 'OKLAHOMA',
			'OR' => 'OREGON',
			'PW' => 'PALAU',
			'PA' => 'PENNSYLVANIA',
			'PR' => 'PUERTO RICO',
			'RI' => 'RHODE ISLAND',
			'SC' => 'SOUTH CAROLINA',
			'SD' => 'SOUTH DAKOTA',
			'TN' => 'TENNESSEE',
			'TX' => 'TEXAS',
			'UT' => 'UTAH',
			'VT' => 'VERMONT',
			'VI' => 'VIRGIN ISLANDS',
			'VA' => 'VIRGINIA',
			'WA' => 'WASHINGTON',
			'WV' => 'WEST VIRGINIA',
			'WI' => 'WISCONSIN',
			'WY' => 'WYOMING',
			);
			if($show_blank)
			{
				$ret = array('' => ' ');
				return array_merge($ret, $states);
			}else{
				return $states;
			}
		}
	}

/*

	$rules['name']	= array('reqd' => 'Name is required.', 'alpha' => 'Name must contain only alpha characters.', 'no_space' => 'Name cannot contain spaces.');
	$rules['blah']	= array('reqd' => 'Blah is required.', 'alphanumeric' => 'Blah must be alphanumeric.', 'min[5]' => 'Blah must be at least 5 characters.');
	$rules['email']	= array('reqd' => 'Email is required.', 'email' => 'Email is invalid.');
	$rules['url']	= array('cond' => 'Blah is required.', 'valid_url' => 'URL is invalid.');


	$v = new Validate;
	$v->AddRules($rules);

	if($_POST)
	{
		$v->RunValidation($_POST);
	}



<form action="" method="post">
	<?= $v->PrintField('name', 'Name Field', 'text'); ?>
	<?= $v->PrintField('blah', 'Blah', 'text'); ?>
	<?= $v->PrintField('email', 'Email', 'text'); ?>
	<?= $v->PrintField('url', 'URL', 'text'); ?>
	<input type="submit" />
</form>


<style type="text/css">
	.error {
		border: 1px solid #CC3300;
		background: #FFCCCC;
		margin: 10px 0;
		padding: 6px;
	}
	body {
		margin: 50px;
	}
	* {
		margin: 0;
		padding: 0;
	}
</style>

*/

?>