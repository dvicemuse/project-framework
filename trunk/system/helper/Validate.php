<?php

	class Validate extends Framework
	{
		public $data;						// Data storage
		public $rules;						// Rule storage
		public $error;						// Error storage
		public $print_errors = true;		// Toggle using $this->print_errors(bool);
		public $print_field_title = true;	// Toggle using $this->print_fields(bool);



		/**
		 * Set validation rules
		 * Will merge over previously set rules
		 * @param array $rules
		 * @return bool
		 */
		public function add_rules($rules)
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
				return FALSE;
			}
			return TRUE;
		}



		/**
		 * Run validation
		 * @param array $data
		 * @param array $rules
		 * @return bool
		 */
		public function run($data, $rules = NULL)
		{
			// Optional rules
			if($rules !== NULL)
			{
				// Add rules
				if($this->add_rules($rules) === FALSE)
				{
					// Could not add rules
					return FALSE;
				}
			}
			// Set data to validate
			$this->data = $data;
			
			// Clear errors
			$this->error = array();
			
			// Rules are set
			if(is_array($this->rules))
			{
				// Check fields
				foreach($this->rules as $k => $v)
				{
					$this->check_field($k);
				}
			}

			// Check if any errors were set
			return empty($this->error);
		}



		/**
		 * Run field validation
		 * @param string $field_name
		 * @return bool
		 */
		private function check_field($field_name)
		{
			$this->data_copy = $this->data;

			if(!isset($this->data_copy[$field_name]))
			{
				$this->data_copy[$field_name] = '';
			}
			
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

			// Not conditional
			$this->conditional = FALSE;			
			
			// Loop through each rule
			foreach($this->rules[$field_name] as $type => $error)
			{
				// Required
				if(strpos($type, '['))
				{
					// Has parameter
					$parts = preg_match('/([_a-z]{1,})\[(.*)\]/i', $type, $m);
					if($parts)
					{
						$validation_method = "_rule_{$m[1]}";
						$this->$validation_method($field_name, $this->data_copy[$field_name], $error, $m[2]);
					}else{
						throw new Exception('Invalid rule match pattern.');
					}
				}else{
					// Does not have parameter
					$validation_method = "_rule_{$type}";
					$this->$validation_method($field_name, $this->data_copy[$field_name], $error);
				}
			}

			// Conditional rules
			if($this->conditional === TRUE)
			{
				unset($this->error[$field_name]);
			}
			return TRUE;
		}



		/**
		 * Return form field header wrapper
		 * @param string $field_name
		 * @param string $field_label
		 * @param string $field_type
		 * @param string $hilight
		 * @return string
		 */
		private function field_wrapper_top($field_name, $field_label, $field_type, &$hilight)
		{
			// Unslash the field name
			if(isset($this->data[$field_name]))
			{
				$this->data[$field_name] = stripslashes($this->data[$field_name]);
			}

			// Start field wrapper
			$o = '<div class="field_wrapper">';

			// Check if field title needs to be printed
			if($this->print_field_title && strlen($field_label) > 0)
			{
				// Output field name
				$o .= "<p class=\"field_name\">{$field_label}</p>";
			}

			// Check for errors
			if(isset($this->error[$field_name]) && is_array($this->error[$field_name]))
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
				// Set highlight
				$hilight = ' class="validation_error_border" ';
			}
			// Start input wrapper
			$o .= '<div class="field_input">';

			// Return buffer
			return $o;
		}



		/**
		 * Return form field header footer
		 * @param string $field_name
		 * @param string $field_label
		 * @param string $field_type
		 * @return string
		 */
		private function field_wrapper_bottom($field_name, $field_label, $field_type)
		{
			// Start buffer
			$o = '';

			// Close input wrapper
			$o .= '</div>';

			// Close field wrapper
			$o .= "</div>\n";

			// Return buffer
			return $o;
		}



		/**
		 * Return a form text field
		 * @param string $field_name
		 * @param string $field_label
		 * @param string $field_type
		 * @param string $extra
		 * @return string
		 */
		public function print_field($field_name, $field_label, $field_type, $extra = '')
		{
			// Start buffer
			$o = '';

			// Field highlight
			$hilight = '';

			// Wrapper top
			$o .= $this->field_wrapper_top($field_name, $field_label, $field_type, $hilight);

			// Field types
			if($field_type == 'text')
			{
				// Text
				$o .= "<input type=\"text\" name=\"{$field_name}\" id=\"{$field_name}\" value=\"{$this->data[$field_name]}\" {$extra} {$hilight} />";
			}else if($field_type == 'password'){
				// Password
				$o .= "<input type=\"password\" name=\"{$field_name}\" id=\"{$field_name}\" value=\"{$this->data[$field_name]}\" {$extra} {$hilight} />";
			}else if($field_type == 'textarea'){
				// Textarea
				$o .= "<textarea name=\"{$field_name}\" id=\"{$field_name}\" {$extra} {$hilight}>{$this->data[$field_name]}</textarea>";
			}

			// Wrapper bottom
			$o .= $this->field_wrapper_bottom($field_name, $field_label, $field_type);

			// Return buffer
			return $o;
		}



		/**
		 * Return a form dropdown field
		 * @param string $field_name
		 * @param string $field_label
		 * @param string $values
		 * @param string $extra
		 * @return string
		 */
		public function print_select($field_name, $field_label, $values, $extra = '')
		{
			// Multiple selected element fix
			$field_name_copy = $field_name;
			$field_name = str_replace('[]', '', $field_name);
			$selected_values = $this->data[$field_name];
			if(!is_array($selected_values))
			{
				unset($selected_values);
				$selected_values[] = $this->data[$field_name];
			}

			// Start buffer
			$o = '';

			// Field highlight
			$hilight = '';

			// Wrapper top
			$o .= $this->field_wrapper_top($field_name, $field_label, 'select', $hilight);

			// Start select
			$o .= "<select name=\"{$field_name_copy}\" id=\"{$field_name}\" {$extra} {$hilight} />";

			if(is_array($values))
			{
				// Loop through values
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
			}
			$o .= "</select>";

			// Wrapper bottom
			$o .= $this->field_wrapper_bottom($field_name, $field_label, 'select');

			// Return buffer
			return $o;
		}



		/**
		 * Return form checkbox fields
		 * @param string $field_name
		 * @param string $field_label
		 * @param string $values
		 * @param string $extra
		 * @return string
		 */
		public function print_checkbox($field_name, $field_label, $values, $extra = '')
		{
			// Start buffer
			$o = '';

			// Field highlight
			$hilight = '';

			// Wrapper top
			$o .= $this->field_wrapper_top($field_name, $field_label, $field_type, $hilight);

			// Start padded error border
			$o .= "<div class=\"{$hilight}_padded\">";

			if(is_array($values))
			{
				// Loop through values
				foreach($values as $k => $v)
				{
					$selected = '';
					if(is_array($this->data[$field_name]) && in_array($k, $this->data[$field_name]))
					{
						$selected = ' checked ';
					}
					$o .= '<p><input type="checkbox" name="'.$field_name.'[]" value="' . $k . '" ' . $selected . ' '.$extra.'/> ' . $v . '</p>';
				}
			}

			// Close padded error border
			$o .= '</div>';
			
			// Wrapper bottom
			$o .= $this->field_wrapper_bottom($field_name, $field_label, $field_type);

			// Return buffer
			return $o;
		}



		/**
		 * Return form radio fields
		 * @param string $field_name
		 * @param string $field_label
		 * @param string $values
		 * @param string $extra
		 * @return string
		 */
		public function print_radio($field_name, $field_label, $values, $extra = '')
		{
			// Start buffer
			$o = '';

			// Field highlight
			$hilight = '';

			// Wrapper top
			$o .= $this->field_wrapper_top($field_name, $field_label, $field_type, $hilight);

			// Start padded error border
			$o .= "<div class=\"{$hilight}_padded\">";

			if(is_array($values))
			{
				foreach($values as $k => $v)
				{
					$selected = '';
					if($k == $this->data[$field_name])
					{
						$selected = ' checked ';
					}
					$o .= '<p><input type="radio" name="'.$field_name.'" value="' . $k . '" ' . $selected . ' '.$extra.'/> ' . $v . '</p>';
				}
			}
			// Close padded error border
			$o .= '</div>';

			// Wrapper bottom
			$o .= $this->field_wrapper_bottom($field_name, $field_label, $field_type);

			// Return buffer
			return $o;
		}



		/**
		 * Show/hide field error messages
		 * @param <type> $bool
		 * @return object self
		 */
		public function print_errors($bool)
		{
			// Boolean param
			if(is_bool($bool))
			{
				$this->print_errors = $bool;
			}
			// Return self
			return $this;
		}



		/**
		 * Show/hide field titles
		 * @param <type> $bool
		 * @return object self
		 */
		public function print_titles($bool)
		{
			// Boolean param
			if(is_bool($bool))
			{
				$this->print_field_title = $bool;
			}
			// Return self
			return $this;
		}


		
		/**
		 * Set the error array
		 * @param array $error_array
		 * @return Validate
		 */
		public function set_error($error_array)
		{
			if(is_array($error_array))
			{
				$this->error = $error_array;
				return $this;
			}
			throw new Exception('Array expected.');
		}



		/**
		 * Set the error array
		 * @param array $error_array
		 * @return Validate
		 */
		public function set_data($data_array)
		{
			if(is_array($data_array))
			{
				$this->data = $data_array;
				return $this;
			}
			throw new Exception('Array expected.');
		}






















		protected function _rule_reqd($field_name, $value, $error)
		{
			if(empty($value))
			{
				$this->error[$field_name][] = $error;
			}
			return $this;
		}



		protected function _rule_max($field_name, $value, $error, $param = NULL)
		{
			if($param !== NULL)
			{
				if(strlen($value) > $param)
				{
					$this->error[$field_name][] = $error;
				}
				return $this;
			}
			throw new Exception('Maximum length value not set.');
		}



		protected function _rule_min($field_name, $value, $error, $param = NULL)
		{
			if($param !== NULL)
			{
				if(strlen($value) < $param)
				{
					$this->error[$field_name][] = $error;
				}
				return $this;
			}
			throw new Exception('Minimum length value not set.');
		}



		protected function _rule_exact($field_name, $value, $error, $param = NULL)
		{
			if($param !== NULL)
			{
				if(strlen($value) != $param)
				{
					$this->error[$field_name][] = $error;
				}
				return $this;
			}
			throw new Exception('Exact length value not set.');
		}



		protected function _rule_match($field_name, $value, $error, $param = NULL)
		{
			if($param !== NULL)
			{
				// Initialize match value if it does not exist
				$match_value = (isset($this->data[$param])) ? $this->data[$param] : '';
				
				if($value != $match_value)
				{
					$this->error[$field_name][] = $error;
				}
				return $this;
			}
			throw new Exception('Match field not set.');
		}



		protected function _rule_alpha($field_name, $value, $error)
		{
			if(!ctype_alpha(str_replace(' ', '', $value)))
			{
				$this->error[$field_name][] = $error;
			}
			return $this;
		}



		protected function _rule_numeric($field_name, $value, $error)
		{
			if(!ctype_digit(str_replace('.', '', $value)))
			{
				$this->error[$field_name][] = $error;
			}
			return $this;
		}



		protected function _rule_positive($field_name, $value, $error)
		{
			if(doubleval($value) < 0)
			{
				$this->error[$field_name][] = $error;
			}
			return $this;
		}



		protected function _rule_money($field_name, $value, $error)
		{
			if(!ctype_digit(str_replace('$', '', str_replace(',', '', str_replace('.', '', $value)))))
			{
				$this->error[$field_name][] = $error;
			}
			return $this;
		}



		protected function _rule_numeric_thousands($field_name, $value, $error)
		{
			if(!ctype_digit(str_replace(',', '', $value)))
			{
				$this->error[$field_name][] = $error;
			}
			return $this;
		}



		protected function _rule_alphanumeric($field_name, $value, $error)
		{
			if(!ctype_alnum(str_replace(' ', '', $value)))
			{
				$this->error[$field_name][] = $error;
			}
			return $this;
		}



		protected function _rule_no_space($field_name, $value, $error)
		{
			if($value != str_replace(' ', '', $value))
			{
				$this->error[$field_name][] = $error;
			}
			return $this;
		}



		protected function _rule_email($field_name, $value, $error)
		{
			if(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", $value))
			{
				$this->error[$field_name][] = $error;
			}
			return $this;
		}



		protected function _rule_date($field_name, $value, $error)
		{
			if(!preg_match('/^([0-9]{1,4})([-\/]{1})([0-9]{1,2})([-\/]{1})([0-9]{1,4})$/', $value) || strtotime($value) === FALSE)
			{
				$this->error[$field_name][] = $error;
			}
			return $this;
		}



		protected function _rule_time($field_name, $value, $error)
		{
			list($hour) = explode(':', $value);
			if(!preg_match('/^([0-1]{1})?([0-9]{1}):([0-5]{1})([0-9]{1})( )?([AaPp][Mm])$/', $value) || $hour > 12)
			{
				$this->error[$field_name][] = $error;
			}
			return $this;
		}



		protected function _rule_unique($field_name, $value, $error, $param = NULL)
		{
			if($param !== NULL)
			{
				if(preg_match('/([_a-zA-Z]{1,}).([_a-zA-Z]{1,})/i', $param, $m))
				{
					$table = $m[1];
					$column = $m[2];
					
					// Get primary key
					$key = $this->load_helper('Db')->get_primary_key($table);
					
					// Check if key is in data array
					$where = '';
					if($key != FALSE && isset($this->data[$key]) && !empty($this->data[$key]))
					{
						$where = " AND `{$key}` != '{$this->load_helper('Db')->escape($this->data[$key])}' ";
					}
					
					if($this->load_helper('Db')->get_row("SELECT `{$column}` FROM `{$table}` WHERE `{$column}` = '{$this->load_helper('Db')->escape($value)}' {$where} ") !== FALSE)
					{
						$this->error[$field_name][] = $error;
					}
					return $this;
				}
			}
			throw new Exception('Parameter table.column not specified.');
		}



		protected function _rule_exists($field_name, $value, $error, $param = NULL)
		{
			if($param !== NULL)
			{
				if(preg_match('/([_a-zA-Z]{1,}).([_a-zA-Z]{1,})/i', $param, $m))
				{
					$table = $m[1];
					$column = $m[2];
					
					if($this->load_helper('Db')->get_row("SELECT `{$column}` FROM `{$table}` WHERE `{$column}` = '{$this->load_helper('Db')->escape($value)}' ") === FALSE)
					{
						$this->error[$field_name][] = $error;
					}
					return $this;
				}
			}
			throw new Exception('Parameter table.column not specified.');
		}



		protected function _rule_cond($field_name, $value, $error, $param = NULL)
		{
			if(empty($value))
			{
				if($param === NULL)
				{
					$this->conditional = TRUE;
				}else{
					$cond_statement = preg_match('/(.*)=(.*)/i', $param, $m);
					if($cond_statement)
					{
						if($this->data[$m['1']] != $m['2'])
						{
							$this->conditional = TRUE;
						}else{
							$this->error[$field_name][] = $error;
						}
					}
				}
			}
		}



		/**
		 * Array of state names (abbreviation => full name)
		 * @param bool $show_blank
		 * @return string
		 */
		public function states($show_blank = TRUE)
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

?>