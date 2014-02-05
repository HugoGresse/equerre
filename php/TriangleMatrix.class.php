<?php
	
	require_once "Triangle.class.php";

	/**
	* A triangle matrix class : store triangle
	* Author : Hugo Gresse 
	* Date : 03/02/2014
	* Note : This class should use iterator
	*/
	class TriangleMatrix {

		/**
		* Array of triangle
		*/
		private $matrix;

		/**
		* Number of triangle stored
		*/
		private $numberOfElements = 0;

		/**
		* New TriangleMatrix blank
		*/
		public function __construct(){
			$this->matrix = array();
		}

		/**
		* Add the given triangle to the matrix
		*/
		public function add(Triangle $t){
			$this->matrix[] = $t;
			$this->numberOfElements ++;
		}

		/**
		* Remove the given triangle from the matrix
		*/
		public function remove(Triangle $t){
			$break = false;
			foreach ($this->matrix as $key => $triangle) {		
				if($triangle === $t){
					unset($this->matrix[$key]);
					$this->numberOfElements --;
					$break = true;
					break;
				}
			}
		}

		/**
		* Return the matrix
		*/
		public function get(){
			return $this->matrix;
		}

		/**
		* Clear/reset the matrix
		*/
		public function clear(){
			$this->matrix = array();
			$this->numberOfElements = 0;
		}

		/**
		* Return the number of triangle in the matrix
		*/
		public function count(){
			return $this->numberOfElements;
		}

	}

?>