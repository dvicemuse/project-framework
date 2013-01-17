<?php

	class ORM_Wrapper implements Iterator
	{
		private $position = 0;
		private $array = array();
		
		
		
		public function __construct()
		{
			$this->position = 0;
		}
		
		
		
		function rewind()
		{
			$this->position = 0;
		}
		
		
		
		function current()
		{
			if($this->valid())
			{
				return $this->array[$this->position];
			}
			return NULL;
		}
		
		
		
		function key()
		{
			return $this->position;
		}
		
		
		
		function next()
		{
			++$this->position;
		}
		
		
		
		function valid()
		{
			return isset($this->array[$this->position]);
		}
		
		
		
		function count()
		{
			return count($this->array);
		}
		
		
		
		function first()
		{
			$this->rewind();
			if($this->valid())
			{
				return $this->current();
			}
			return NULL;
		}
		
		
		
		function push($object)
		{
			$this->array[] = $object;
		}
	}

?>