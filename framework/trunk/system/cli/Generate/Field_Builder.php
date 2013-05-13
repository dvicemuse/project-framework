<?php

	class Field_Builder
	{
		// Framework object
		private $_fw;
		
		// Store the raw info array
		private $_raw = array();
		private $_table_name = '';
		
		// Validation rules and field print code
		private $_rules = '';
		private $_field_print = '';



		/**
		 * Load framework
		 */ 
		public function __construct($framework, $table_name, $info_array)
		{
			$this->_fw = $framework;
			$this->_raw = $info_array;
			$this->_table_name = $table_name;
		}
		
		
		
		/**
		 * Parse the field
		 * @return Form_Builder
		 */
		public function process()
		{
			// Ignore primary key
			if($this->_raw['Key'] == 'PRI')
			{
				return $this;
			}
			
			// Determine field type
			$method_name = "_type_{$this->_raw['Type']}";
			if(preg_match('/^([a-z]{1,})\((.*?)\)(.*)/', $this->_raw['Type'], $m))
			{
				$method_name = "_type_{$m[1]}";
			}
			
			// Check if there is a method to handle this type
			if(method_exists($this, $method_name))
			{
				$this->$method_name($m);
			}else{
				// Default to char field
				$this->_type_varchar($m);
			}
			return $this;
		}
		
		
		
		private function _type_date($parts)
		{
			// Basic validation
			$rules['reqd'] = 'Field is required.';
			$rules['date'] = 'Invalid date.';
		
			// Done
			$this->_rules = $this->_make_rule_array_string($rules);
			$this->_field_print = '<?= $this->Validate->print_text(\''.$this->_name().'\', \''.$this->_friendly_name().'\'); ?>'."\n";
		}		
		
				

		private function _type_char($parts)
		{
			return $this->_type_varchar($parts);
		}



		private function _type_varchar($parts)
		{
			// Basic validation
			$rules['reqd'] = 'Field is required.';

			// Field max length
			if(isset($parts[2]))
			{
				$length = $parts[2];
				$rules['max['.$length.']'] = "Max length of {$length} characters exceeded.";
			}
			
			// Unique value
			if($this->_raw['Key'] == 'UNI')
			{
				$rules["unique[{$this->_table_name()}.{$this->_name()}]"] = 'Field unique constraint fails.';
			}
			
			// Done
			$this->_rules = $this->_make_rule_array_string($rules);
			$this->_field_print = '<?= $this->Validate->print_text(\''.$this->_name().'\', \''.$this->_friendly_name().'\'); ?>'."\n";
		}



		private function _type_text($parts)
		{
			// Basic validation
			$rules['reqd'] = 'Field is required.';

			// Unique value
			if($this->_raw['Key'] == 'UNI')
			{
				$rules["unique[{$this->_table_name()}.{$this->_name()}]"] = 'Field unique constraint fails.';
			}

			// Done
			$this->_rules = $this->_make_rule_array_string($rules);
			$this->_field_print = '<?= $this->Validate->print_textarea(\''.$this->_name().'\', \''.$this->_friendly_name().'\'); ?>'."\n";
		}



		private function _type_enum($parts)
		{
			// Field max length
			$length = $parts[2];
			
			// Basic validation
			$rules['reqd'] = 'Field is required.';
			
			// Done
			$this->_rules = $this->_make_rule_array_string($rules);
			$this->_field_print = '<?= $this->Validate->print_select(\''.$this->_name().'\', \''.$this->_friendly_name().'\', $this->load_helper(\'Db\')-> get_enum(\''.$this->_table_name().'\', \''.$this->_name().'\')); ?>'."\n";
		}		


		
		private function _type_int($parts)
		{
			return $this->_type_numeric($parts);
		}



		private function _type_decimal($parts)
		{
			return $this->_type_numeric($parts);
		}


		private function _type_double($parts)
		{
			return $this->_type_numeric($parts);
		}


		private function _type_numeric($parts)
		{
			// Field max length
			$length = $parts[2];
			$extra = $parts[3];

			// Default field output
			$this->_field_print = '<?= $this->Validate->print_text(\''.$this->_name().'\', \''.$this->_friendly_name().'\'); ?>'."\n";

			// Basic validation
			$rules['reqd'] = 'Field is required.';
			$rules['numeric'] = 'Field must be numeric.';

			// Only positive values
			if(strpos($extra, 'unsigned') !== FALSE)
			{
				$rules['positive'] = 'Field must be a positive value.';
			}

			// Unique value
			if($this->_raw['Key'] == 'UNI')
			{
				$rules["unique[{$this->_table_name()}.{$this->_name()}]"] = 'Field unique constraint fails.';
			}

			// Foreign key check
			$sql = "
				SELECT 
					concat(referenced_table_name, '.', referenced_column_name) AS `reference`,
					referenced_table_name AS `table`,
					referenced_column_name AS `column`
				FROM
					information_schema.key_column_usage
				WHERE
					referenced_table_name IS NOT NULL
					AND table_name = '{$this->_table_name()}'
					AND column_name = '{$this->_name()}'
			";
			$fk_check = $this->_fw->load_helper('Db')->get_row($sql);
			if($fk_check !== FALSE)
			{
				// Foreign key rule
				$rules['exists['.$fk_check['reference'].']'] = 'Field key constraint fails.';
				
				// Does foreign table have a name field?
				$dropdown_column_name = $fk_check['column'];
				$dropdown_label_name = $this->_friendly_name();
				if($this->_fw->load_helper('Db')->column_exists($fk_check['table'], "{$fk_check['table']}_name"))
				{
					$dropdown_column_name = "{$fk_check['table']}_name";
					$dropdown_label_name = $this->_friendly_name($dropdown_column_name);
				}
				
				// Show dropdown of values
				$this->_field_print = '<?= $this->Validate->print_select(\''.$this->_name().'\', \''.$dropdown_label_name.'\', $this->load_model(\''.$this->_table_name_to_model_name($fk_check['table']).'\')->dropdown(\''.$dropdown_column_name.'\')); ?>'."\n";
			}
			
			// Done
			$this->_rules = $this->_make_rule_array_string($rules);
		}


		















		
		public function rule_string()
		{
			return $this->_rules;
		}


		public function field_string()
		{
			return $this->_field_print;
		}





		public function _make_rule_array_string($rules)
		{
			// Param check
			if(!is_array($rules))
			{
				return "";
			}
			
			// Start the array string
			$return = "'{$this->_name()}' => array(\n";
			foreach($rules as $type => $rule)
			{
				$return .= "\t'{$type}' => '{$rule}',\n";
			}
			
			// Close array
			$return .= "),\n";
			
			// Return string
			return $return;
		}
		
		
		
		private function _name()
		{
			return $this->_raw['Field'];
		}


		
		private function _table_name()
		{
			return $this->_table_name;
		}



		private function _friendly_name($override = NULL)
		{
			$string = $this->_raw['Field'];
			if($override !== NULL)
			{
				$string = $override;
			}
			
			return ucwords(str_replace('_', ' ', $string));
		}
		
		
		
		private function _table_name_to_model_name($table_name)
		{
			$table_name = ucwords(str_replace('_', ' ', $table_name));
			return str_replace(' ', ' ', $table_name);
		}
	}

?>