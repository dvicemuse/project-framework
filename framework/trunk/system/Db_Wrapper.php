<?php
/**
 * @file Db_Wrapper.php
 * @package    ProjectFramework
 *
 * @license    see LICENSE.txt
 */

/**
 * @class Db_Wrapper
 * @brief Wrapper Database class. Wrapper class for raw database resultsets
 *
 * @package  ProjectFramework
 * @since    1.0.0
 */
class Db_Wrapper implements Countable
{
	/**
	 * @var $_data
	 * @brief Raw associative array resultset
	 */
	private $_data = array();

	/**
	 * @brief Populate the data array
	 * 
	 * @param array $array - raw result set from query
	 * @return object Db_Wrapper
	 */
	public function set($array)
	{
		if(is_array($array))
		{
			$this->_data = $array;
		}
		return $this;
	}

	/**
	 * @brief Get first result from data array
	 * 
	 * @return mixed - result from first element or boolean false if no data
	 */
	public function result()
	{
		$retval = false;
		
		if(isset($this->_data[0]) && is_array($this->_data[0]))
		{
			$retval = $this->_data[0];
		}
		
		return $retval;
	}

	/**
	 * @brief Get all results from data array
	 * 
	 * @return mixed - array or results or boolean false if no data
	 */
	public function results()
	{
		$retval = false;
		
		if(isset($this->_data) && is_array($this->_data) && count($this->_data) > 0)
		{
			$retval =  $this->_data;
		}
		
		return $retval;
	}

	/**
	 * @brief Counts the number of items in data array
	 * 
	 * @return integer - number of items in array or 0 if empty
	 */
	public function count()
	{
		$retval = 0;
		if(is_array($this->_data) && count($this->_data) > 0)
		{
			$retval = count($this->_data);
		}
		
		return $retval;
	}

}


?>