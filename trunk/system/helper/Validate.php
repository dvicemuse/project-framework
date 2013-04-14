<?php

	class Validate extends Framework
	{
		public $data;						// Data storage
		public $rules;						// Rule storage
		public $error;						// Error storage
		
		// Field type
		protected $_field_type = NULL;
		
		// Titles
		protected $_hide_titles = FALSE;
		protected $_hide_titles_once = NULL;

		// Errors
		protected $_hide_errors = FALSE;
		protected $_hide_errors_once = NULL;



		/**
		 * Run validation
		 * @param array $data
		 * @param array $rules
		 * @return bool
		 */
		public function run($data, $rules)
		{
			// Set rules
			$this->rules = is_array($rules) ? $rules : array();

			// Set data to validate
			$this->data = is_array($data) ? $data : array();

			// Clear errors
			$this->error = array();
			
			// Check fields
			foreach($this->rules as $key => $rule)
			{
				$this->check_field($key);
			}

			// Check if any errors were set
			return empty($this->error);
		}



		/**
		 * Run validation on a field
		 * @param string $field_name
		 * @return bool
		 */
		private function check_field($field_name)
		{
			// Set non conditional flag
			$this->conditional = FALSE;	

			// Get field value
			$field_value = $this->_get_field_value_strip_array($field_name);
			
			// Loop through each rule
			foreach($this->rules[$field_name] as $type => $error)
			{
				// Look for rule[parameter]
				if(preg_match('/([_a-z]{1,})\[(.*)\]/i', $type, $m))
				{
					$validation_method = "_rule_{$m[1]}";
					$this->$validation_method($field_name, $field_value, $error, $m[2]);
				}else{
					// Does not have parameter
					$validation_method = "_rule_{$type}";
					$this->$validation_method($field_name, $field_value, $error);
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
		 * Return header wrapper
		 * @param string $field_name
		 * @param string $field_label
		 * @param string $field_type
		 * @param string $hilight
		 * @return string
		 */
		protected function _build_wrapper_top($field_name, $field_label, &$attribute_array)
		{
			// Check for no wrap flag
			if(isset($attribute_array['validate_no_wrap']))
			{
				return '';
			}
			
			// Start field wrapper
			$o = '<div class="field_wrapper">';

			// Check if field title needs to be printed
			if($this->_hide_titles === FALSE && strlen($field_label) > 0)
			{
				// Output field name
				$o .= "<p class=\"field_name\">{$field_label}</p>";
			}

			// Check for errors
			if(isset($this->error[$field_name]) && is_array($this->error[$field_name]))
			{
				// If we need to print the errors
				if($this->_hide_errors === FALSE && current($this->error[$field_name]) !== '')
				{
					$o .= "<div class=\"validation_error\">";
					$o .= "<p>".current($this->error[$field_name])."</p>";
					$o .= "</div>";
				}
				
				// Set error class
				$attribute_array['class'][] = 'validation_error_border';
			}
			
			// Turn errors back on if one time flag was set
			if($this->_hide_errors_once === TRUE)
			{
				$this->show_errors();
			}

			// Turn titles back on if one time flag was set
			if($this->_hide_titles_once === TRUE)
			{
				$this->show_titles();
			}
			
			// Start input wrapper
			$o .= '<div class="field_input'.((isset($attribute_array['validate_padded_error']) && @in_array('validation_error_border', $attribute_array['class'])) ? ' validation_error_border_padded' : '').'">';

			// Return buffer
			return $o;
		}



		/**
		 * Return footer wrapper
		 * @param string $field_name
		 * @param string $field_label
		 * @param string $field_type
		 * @return string
		 */
		protected function _build_wrapper_bottom($attribute_array)
		{
			// Check for no wrap flag
			if(isset($attribute_array['validate_no_wrap']))
			{
				return '';
			}

			// Close input wrapper
			$o = '</div>';

			// Close field wrapper
			$o .= "</div>\n";

			// Return buffer
			return $o;
		}



		/**
		 * Return a checkbox field
		 * @param string $field_name
		 * @param string $field_label
		 * @param array $field_value
		 * @param array $attribute_array
		 * @param bool $single_value
		 * @return string
		 */
		public function print_checkbox($field_name, $field_label, $field_value, $attribute_array = array(), $single_value = FALSE)
		{
			// Trigger padded field validation errors
			$attribute_array['validate_padded_error'] = 'true';
			
			// Normalize attribute array
			$attribute_array = $this->_process_attribute($attribute_array);
			
			// Wrapper top
			$o = $this->_build_wrapper_top($field_name, $field_label, $attribute_array);

			// Wrap with ID
			$o .= "<div id=\"{$field_name}\">";

			// Loop through checkbox values
			if(is_array($field_value))
			{
				foreach($field_value as $key => $value)
				{
					// Copy the attribute array
					$attribute_array_copy = $attribute_array;
					
					// See if the input field should be checked
					if(isset($this->data[$field_name]))
					{
						if(!empty($key) && is_string($this->data[$field_name]) && $key == $this->data[$field_name])
						{
							// Set checked attribute
							$attribute_array_copy['checked'] = 'checked';
						}else if(is_array($this->data[$field_name]) && in_array($key, $this->data[$field_name])){
							// Set checked attribute
							$attribute_array_copy['checked'] = 'checked';
						}
					}
	
					// Save the data array to a temp variable and then overwrite (multi checkbox handling)
					$data_tmp = (isset($this->data[$field_name]) ? $this->data[$field_name] : '');
					$this->data[$field_name] = $key;
				
					// Build field
					$o .= '<p class="checkbox_wrapper">';
					$o .= $this->_build_field('checkbox', $field_name . ($single_value ? '' : '[]'), $attribute_array_copy);
					$o .= (!empty($value) ? " {$value}" : '') . '</p>';
					
					// Set data value back
					$this->data[$field_name] = $data_tmp;
				}
			}

			// Close ID wrapper
			$o .= "</div>";

			// Wrapper bottom
			$o .= $this->_build_wrapper_bottom($attribute_array);

			// Return buffer
			return $o;
		}



		/**
		 * Return a radio field
		 * @param string $field_name
		 * @param string $field_label
		 * @param string $field_value
		 * @param array $attribute_array
		 * @param bool $single_value
		 * @return string
		 */
		public function print_radio($field_name, $field_label, $field_value, $attribute_array = array())
		{
			// Trigger padded field validation errors
			$attribute_array['validate_padded_error'] = 'true';
			
			// Normalize attribute array
			$attribute_array = $this->_process_attribute($attribute_array);
			
			// Wrapper top
			$o = $this->_build_wrapper_top($field_name, $field_label, $attribute_array);
			
			// Wrap with ID
			$o .= "<div id=\"{$field_name}\">";
			
			// Loop through checkbox values
			if(is_array($field_value))
			{
				foreach($field_value as $key => $value)
				{
					// Copy the attribute array
					$attribute_array_copy = $attribute_array;
	
					// See if the input field should be checked
					if($key == $this->_get_field_value($field_name))
					{
						// Set checked attribute
						$attribute_array_copy['checked'] = 'checked';
					}
	
					// Save the data array to a temp variable and then overwrite (multi radio handling)
					$data_tmp = $this->_get_field_value($field_name);
					$this->data[$field_name] = $key;
	
					// Build field
					$o .= '<p class="radio_wrapper">';
					$o .= $this->_build_field('radio', $field_name, $attribute_array_copy);
					$o .= (!empty($value) ? " {$value}" : '') . '</p>';
					
					// Set data value back
					$this->data[$field_name] = $data_tmp;
				}
			}

			// Close ID wrapper
			$o .= "</div>";

			// Wrapper bottom
			$o .= $this->_build_wrapper_bottom($attribute_array);

			// Return buffer
			return $o;
		}



		/**
		 * Return a hidden field
		 * @param string $field_name
		 * @param array $attribute_array
		 * @return string
		 */
		public function print_hidden($field_name, $attribute_array = array())
		{
			// Set the no wrap flag
			$attribute_array = $this->_process_attribute($attribute_array);
			$attribute_array['validate_no_wrap'] = 'true';
			
			// Set type and pass to print_text()
			return $this->_set_field_type('hidden')->print_text($field_name, '', $attribute_array);
		}



		/**
		 * Return a password field
		 * @param string $field_name
		 * @param string $field_label
		 * @param array $attribute_array
		 * @return string
		 */
		public function print_password($field_name, $field_label, $attribute_array = array())
		{
			// Set type and pass to print_text()
			return $this->_set_field_type('password')->print_text($field_name, $field_label, $attribute_array);
		}



		/**
		 * Return a textarea field
		 * @param string $field_name
		 * @param string $field_label
		 * @param array $attribute_array
		 * @return string
		 */
		public function print_textarea($field_name, $field_label, $attribute_array = array())
		{
			// Set type and pass to print_text()
			return $this->_set_field_type('textarea')->print_text($field_name, $field_label, $attribute_array);
		}



		/**
		 * Return a text field
		 * @param string $field_name
		 * @param string $field_label
		 * @param array $attribute_array
		 * @return string
		 */
		public function print_text($field_name, $field_label, $attribute_array = array())
		{
			// Normalize attribute array
			$attribute_array = $this->_process_attribute($attribute_array);
			
			// Wrapper top
			$o = $this->_build_wrapper_top($field_name, $field_label, $attribute_array);

			// Build field
			$o .= $this->_build_field($this->_get_field_type('text'), $field_name, $attribute_array);

			// Wrapper bottom
			$o .= $this->_build_wrapper_bottom($attribute_array);

			// Return buffer
			return $o;
		}



		/**
		 * Return a dropdown field
		 * @param string $field_name
		 * @param string $field_label
		 * @param string $values
		 * @param string $extra
		 * @return string
		 */
		public function print_select($field_name, $field_label, $field_value, $attribute_array = array())
		{
			// Normalize attribute array
			$attribute_array = $this->_process_attribute($attribute_array);

			// Wrapper top
			$o = $this->_build_wrapper_top($field_name, $field_label, $attribute_array);

			// Start select
			$o .= $this->_build_field('select', $field_name . (isset($attribute_array['multiple'][0]) ? '[]' : ''), $attribute_array);

			if(is_array($field_value))
			{
				// Convert values to array (multiple selection)
				$tmp_data = $this->_get_field_value($field_name);
				if(is_string($tmp_data))
				{
					$tmp_data = array($tmp_data);
				}
				
				// Loop through values
				foreach($field_value as $key => $value)
				{
					$o .= '<option value="' . $key . '" ' . (in_array($key, $tmp_data) ? 'selected' : '') . '>';
					$o .= $value;
					$o .= '</option>';
				}
			}
			$o .= "</select>";

			// Wrapper bottom
			$o .= $this->_build_wrapper_bottom($attribute_array);

			// Return buffer
			return $o;
		}


		
		/**
		 * Set the field type
		 * @param string $field_type
		 * @return Validate
		 */
		protected function _set_field_type($field_type)
		{
			$this->_field_type = $field_type;
			return $this;
		}



		/**
		 * Get the field type, return default if no type is set
		 * @param string $default_type
		 * @return string
		 */
		protected function _get_field_type($default_type)
		{
			// Check if override type is set
			if($this->_field_type !== NULL)
			{
				// Use override
				$default_type = $this->_field_type;
				$this->_field_type = NULL;
			}
			
			// Return type
			return $default_type;
		}


		
		/**
		 * Get a field value, initialize if not set
		 * @param string $field_name
		 * @return string|array
		 */
		protected function _get_field_value($field_name)
		{
			// Multiple value support
			$field_name = str_replace('[]', '', $field_name);
			
			// Initialize value
			if(!isset($this->data[$field_name]))
			{
				$this->data[$field_name] = '';
			}
			
			// Return value
			return $this->data[$field_name];
		}



		/**
		 * Get a field value, initialize if not set
		 * If value is an array return first non empty value
		 * @param string $field_name
		 * @return string|array
		 */
		protected function _get_field_value_strip_array($field_name)
		{
			$field_value = $this->_get_field_value($field_name);
			if(is_array($field_value))
			{
				// Data is an array, use first non empty value
				foreach($field_value as $key => $value)
				{
					if(!empty($value))
					{
						$field_value = trim($value);
						break;
					}
				}
			}

			// Return value
			return $field_value;
		}


		
		/**
		 * Generate fields
		 * @param string $field_type
		 * @param string $field_name
		 * @param array $attribute_array
		 * @return string
		 */
		protected function _build_field($field_type, $field_name, $attribute_array)
		{
			// Check parameters
			if(!in_array($field_type, array('text', 'password', 'hidden', 'textarea', 'checkbox', 'radio', 'select'))){ throw new Exception('Invalid field type.'); }

			// Select
			if($field_type == 'select')
			{
				return "<select name=\"{$field_name}\" id=\"".str_replace('[]','', $field_name)."\" {$this->_build_attribute($attribute_array)}>";
			}

			// Textarea
			if($field_type == 'textarea')
			{
				return "<textarea name=\"{$field_name}\" id=\"{$field_name}\" {$this->_build_attribute($attribute_array)}>{$this->_get_field_value($field_name)}</textarea>";
			}
			
			// All other input
			return "<input type=\"{$field_type}\" name=\"{$field_name}\" ". (!in_array($field_type, array('radio', 'checkbox')) ? "id=\"{$field_name}\"" : "") ." value=\"{$this->_get_field_value($field_name)}\" {$this->_build_attribute($attribute_array)} />";
		}


		
		/**
		 * Normalize attribute array
		 * @param array $attribute_array
		 * @return array
		 */
		protected function _process_attribute($attribute_array)
		{
			// Check parameter
			if(!is_array($attribute_array)){ throw new Exception('Invalid attribute array.'); }
			
			// Initialize return
			$return = array();
			
			// Loop through and generate normalized array
			foreach($attribute_array as $key => $value)
			{
				if(is_array($value))
				{
					foreach($value as $value_2)
					{
						if(is_string($value_2))
						{
							$return[$key][] = $value_2;
						}
					}
				}else if(is_string($value)){
					$return[$key][] = $value;
				}
			}
			
			// Done
			return $return;
		}



		/**
		 * Build attribute string
		 * @param $attribute_array
		 * @return string
		 */
		protected function _build_attribute($attribute_array)
		{
			// Check parameter
			if(!is_array($attribute_array)){ throw new Exception('Invalid attribute array.'); }
			
			// Remove attributes that should not be set
			unset($attribute_array['id'], $attribute_array['type'], $attribute_array['name'], $attribute_array['value'], $attribute_array['validate_padded_error'], $attribute_array['validate_no_wrap']);
			
			// Initialize return
			$return = '';
			
			// Loop through and combine
			foreach($attribute_array as $key => $value)
			{
				// Reset buffer
				$buffer = '';
				
				// Array found
				if(is_array($value))
				{
					// Add eachitem to the attribute value
					foreach($value as $key_2 => $value_2)
					{
						$buffer .= "{$value_2} ";
					}
				}

				// Wrap buffer with attribute name
				$return .= $key.'="'.trim($buffer).'"';
			}
			return $return;
		}



		/**
		 * Do not show field errors
		 * @param bool $hide_once
		 * @return Validate
		 */
		public function hide_errors($hide_once = FALSE)
		{
			// Set titles to hidden
			$this->_hide_errors = TRUE;
			
			// Only hide for the next field
			if($hide_once === TRUE)
			{
				$this->_hide_errors_once = TRUE;
			}
			
			// Return
			return $this;
		}



		/**
		 * Show field errors
		 * @return Validate
		 */
		public function show_errors()
		{
			// Set titles to hidden
			$this->_hide_errors = FALSE;
			$this->_hide_errors_once = NULL;
			
			// Return
			return $this;
		}



		/**
		 * Do not show field titles
		 * @param bool $hide_once
		 * @return Validate
		 */
		public function hide_titles($hide_once = FALSE)
		{
			// Set titles to hidden
			$this->_hide_titles = TRUE;
			
			// Only hide for the next field
			if($hide_once === TRUE)
			{
				$this->_hide_titles_once = TRUE;
			}
			
			// Return
			return $this;
		}



		/**
		 * Show field titles
		 * @return Validate
		 */
		public function show_titles()
		{
			// Set titles to hidden
			$this->_hide_titles = FALSE;
			$this->_hide_titles_once = NULL;
			
			// Return
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



		/**
		 * Array of state names (abbreviation => full name)
		 * @param bool $show_blank
		 * @return string
		 */
		public function states($show_blank = TRUE)
		{
			$states = $this->config()->locale->states;
			if($show_blank)
			{
				$ret = array('' => ' ');
				return array_merge($ret, $states);
			}else{
				return $states;
			}
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
			if(!ctype_digit(str_replace(array('-', '.'), '', $value)))
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



	}

?>