<?php

	class Benchmark
	{
		private $marker = array();


		
		/**
		 * Set a benchmark marker
		 * @param string $name
		 * @return void
		 */
		public function mark($name)
		{
			$this->marker[$name] = microtime();
		}



		/**
		 * Calculates the time difference between two marked points.
		 * @param string $point1
		 * @param string $point2
		 * @param integer $decimals
		 * @return mixed
		 */
		public function elapsed_time($point1 = '', $point2 = '', $decimals = 4)
		{
			if($point1 == '')
			{
				return '{elapsed_time}';
			}

			if(!isset($this->marker[$point1]))
			{
				return '';
			}

			if(!isset($this->marker[$point2]))
			{
				$this->marker[$point2] = microtime();
			}

			list($sm, $ss) = explode(' ', $this->marker[$point1]);
			list($em, $es) = explode(' ', $this->marker[$point2]);

			return number_format(($em + $es) - ($sm + $ss), $decimals);
		}



	}

?>