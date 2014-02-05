<?php
	
	/**
	* A Color class
	* Author : Hugo Gresse 
	* Date : 24/12/2013
	*/
	class Color {

		/**
		* Red Value (0-255)
		*/
		public $r;

		/**
		* Green Value (0-255)
		*/
		public $g;

		/**
		* Blue Value (0-255)
		*/
		public $b;


		/**
		* Create a Color object with the given color : red, green and blue
		*/
		public function __construct($r, $g, $b){
			$this->r=  $r;
			$this->g=  $g;
			$this->b=  $b;
		}

		/**
		* Return an array with Red, Green and Blue value
		*/
		public function getColor(){
			return array($this->r, $this->g, $this->b);
		}

		/**
		* return Red Value (0-255)
		*/
		public function getRed(){
			return $this->r;
		}

		/**
		* return green Value (0-255)
		*/
		public function getGreen(){
			return $this->g;
		}

		/**
		* return blue Value (0-255)
		*/
		public function getBlue(){
			return $this->b;
		}

		/**
		* Override generic toString method
		*/
		public function __toString(){
			return "Color($this->r,$this->g,$this->b) ";
		}
	}

?>