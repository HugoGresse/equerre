<?php
	
	/**
	* A Pixel Class is more like a vertex : x and y coordonate
	* Author : Hugo Gresse 
	* Date : 03/02/2014
	*/
	class Pixel {

		/**
		* X coordonate of the pixel
		*/
		public $x;

		/**
		* Y coordonate of the pixel
		*/
		public $y;

		/**
		* New Pixel, default values is (0,0)
		*/
		public function __construct($x = 0, $y=0){
			$this->x=  round($x);
			$this->y=  round($y);
		}

		/**
		* Return an array with x and y
		*/
		public function getPos(){
			return array($this->x, $this->y);
		}

		/**
		* Return the X coordinate
		*/
		public function getX(){
			return $this->x;
		}

		/**
		* Return the Y coordonate
		*/
		public function getY(){
			return $this->y;
		}

		/**
		* Set the x coordonate
		*/
		public function setX($x){
			$this->x = $x;
		}

		/**
		* Set the y coordonate
		*/
		public function setY($y){
			$this->y = $y;
		}
		
		/**
		* Change the pixel value by giving him a new pixel
		*/
		public function setPixel(Pixel $p){
			$this->x = $p->getX();
			$this->y = $p->getY();
		}

		/**
		* Override the generic method
		*/
		public function __toString(){
			return "P($this->x,$this->y) ";
		}


		/**
		* Check if a line is on the left of the line given with $a and $b
		*  a,b : line   c: point to check
		*/
		public function isLeft(Pixel $a, Pixel $b){
			return (($b->x - $a->x) * ($this->y - $a->y) - ($b->y - $a->y)*($this->x - $a->x)) > 0;
		}
	}

?>