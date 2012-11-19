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
				echo "nope... {$m[1]}";
			}
			return $this;
		}
		
		

		private function _type_char($parts)
		{
			return $this->_type_varchar($parts);
		}
		
		
		
		private function _type_varchar($parts)
		{
			// Field max length
			$length = $parts[2];
			
			// Basic validation
			$rules['reqd'] = 'Field is required.';
			$rules['max['.$length.']'] = "Max length of {$length} characters exceeded.";
			
			// Done
			$this->_rules = $this->_make_rule_array_string($rules);
			$this->_field_print = '<?= $this->Validate->print_field(\''.$this->_name().'\', \''.$this->_friendly_name().'\', \'text\'); ?>'."\n";
		}



		private function _type_text($parts)
		{
			// Basic validation
			$rules['reqd'] = 'Field is required.';
			
			// Done
			$this->_rules = $this->_make_rule_array_string($rules);
			$this->_field_print = '<?= $this->Validate->print_field(\''.$this->_name().'\', \''.$this->_friendly_name().'\', \'textarea\'); ?>'."\n";
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

			// Basic validation
			$rules['reqd'] = 'Field is required.';
			$rules['numeric'] = 'Field must be numeric.';
			
			// Only positive values
			if(strpos($extra, 'unsigned') !== FALSE)
			{
				$rules['positive'] = 'Field must be a positive value.';
			}
			
			// Done
			$this->_rules = $this->_make_rule_array_string($rules);
			$this->_field_print = '<?= $this->Validate->print_field(\''.$this->_name().'\', \''.$this->_friendly_name().'\', \'text\'); ?>'."\n";
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



		private function _friendly_name()
		{
			return ucwords(str_replace('_', ' ', $this->_raw['Field']));
		}
	}

?>