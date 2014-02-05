<?php
	
	

	require_once "Color.class.php";
	require_once "Pixel.class.php";
	require_once "GeoLib.php";

	/**
	* A triangle class
	* Author : Hugo Gresse 
	* Date : 03/02/2014
	*/
	class Triangle {

		/**
		* A array with 3 Pixel corresponding to each triangle vertex
		*/
		private $vertexes;

		/**
		* Not used : the pixels composing each edge
		*/
		private $pixels;

		/**
		* An array with coefficient and y-intercept for each point
		*/
		private $math;

		/**
		* The triangle Color
		*/
		private $color;

		/**
		* An Array with the distance between each vertexs
		*/
		private $distance;

		/**
		* The largest triangle edge (0, 1 or 2)
		*/
		private $largestEdge;

		/**
		* The bouding box array
		*/
		private $minMax;


		/**
		* Instancie a triangle with 3 vertexs/Pixels
		*/
		public function __construct(Pixel $s1, Pixel $s2, Pixel $s3){
			$this->vertexes =  array($s1, $s2, $s3) ;
			$this->color = new Color(255,255,255);

			$this->calcMath();
			$this->calcDistance();
			$this->minMax = $this->calcMinMax();

			// var_dump($this->vertexes);
		}

		public function __toString(){
			return "T(".$this->vertexes[0].",<br/>  -- ".$this->vertexes[1].",<br/> -- ".$this->vertexes[2]."<br/>)<br/>";
		}

		/**
		* Get Color of the triangle
		* See : Color.class
		*/
		public function getColor(){
			return $this->color;
		}

		/**
		* Set triangle color
		* Param : Color object
		*/
		public function setColor(Color $c){
			$this->color = $c;
		}

		/**
		* Return the largest triangle edge : 0 if it's between vertex one and two, 
		* 		1 if between two and third, 2 if between third and first
		* 
		*/
		public function getLargestEdge(){
			return $this->largestEdge;
		}

		/** 
		* Return a random Pixel between the given point : (0 1 or 2) 
		* More coefMin is big, closer can be the new point from others
		* See : Pixel.class
		*/
		public function getPointBetweenPoint($v1, $v2, $coefMin = 3){
			
			$coefMinX = $coefMinY = 0;
			$s1 = $this->vertexes[$v1];
			$s2 = $this->vertexes[$v2];

			$coef = $this->math[$v1]['coef'];
			$ordOri = $this->math[$v1]['ordOri'];

			$point = new Pixel(0,0);

			if($coef == 0 && $ordOri == 0) { // vertical line
				$point->x = $s2->x;
				if($s1->y < $s2->y) {
					$coefMinY = ($s2->y - $s1->y ) /  $coefMin;
					$point->y = mt_rand($s1->y + $coefMinY,  $s2->y - $coefMinY);
				} else {
					$coefMinY = ($s1->y - $s2->y ) /  $coefMin;
					$point->y = mt_rand($s2->y + $coefMinY,  $s1->y - $coefMinY);
				}
			} else if($coef == -1 && $ordOri == -1) { // horizontale line
				$point->y = $s1->y;

				if($s1->x < $s2->x) {
					$coefMinX = ($s2->x - $s1->x ) /  $coefMin;
					$point->x = mt_rand($s1->x + $coefMinX,  $s2->x - $coefMinX);
				} else {
					$coefMinX = ($s1->x - $s2->x ) /  $coefMin;
					$point->x = mt_rand($s2->x + $coefMinX,  $s1->x - $coefMinX);
				}

			} else {

				if($s1->x < $s2->x) {
					$coefMinX = ($s2->x - $s1->x ) /  $coefMin;
					$rd = mt_rand($s1->x + $coefMinX,  $s2->x - $coefMinX);
				} else {
					$coefMinX = ($s1->x - $s2->x ) /  $coefMin;
					$rd = mt_rand($s2->x + $coefMinX,  $s1->x - $coefMinX);
				}
				$point->x = $rd;
				$point->y = $rd * $coef + $ordOri;

			}
			return $point;
		}

		/**
		* Return a random Pixel on the largest triangle edge
		* More coefMin is big, closer can be the new point from others
		* See : Pixel.class
		* Not used
		*/
		public function getPointOnLargestEdge($coefMin = 3){
			switch ($this->largestEdge ) {
				case 0:
					return $this->getPointBetweenPoint(0,1, $this->coefMin);
					break;
				case 1:
					return $this->getPointBetweenPoint(1,2, $this->coefMin);
					break;
				case 2:
					return $this->getPointBetweenPoint(2,3, $this->coefMin);
					break;
				default:
					throw new Exception('switch getPointOnLargestEdge');
					break;
			}
		}

		/**
		* Return an array with the bouding box of the triangle : minX, minY, maxX, maxY
		*/
		public function getMinMax(){
			return $this->minMax;
		}

		/**
		* Return the Pixel corresponding to the given number (0, 1 or 2)
		*/
		public function getVertex($numb){
			return $this->vertexes[$numb];
		}

		/**
		* Return an array with the vertexes/Pixels
		*/
		public function getListVertex(){
			return array($this->vertexes[0], $this->vertexes[1], $this->vertexes[2]);
		}

		/**
		* Return true if the given Pixel is inside the triangle
		*/
		public function isInsideTriangle(Pixel $p){

			list($a, $b, $c) = $this->getListVertex();

			$as_x = $p->x - $a->x;
		    $as_y = $p->y - $a->y;

		    $bool_s_ab = ($b->x-$a->x) * $as_y-($b->y-$a->y) * $as_x > 0;

		    if(($c->x - $a->x) * $as_y-  ($c->y - $a->y) * $as_x > 0 == $bool_s_ab) return false;

		    if(($c->x - $b->x) * ($p->y-$b->y)-($c->y - $b->y)*($p->x-$b->x) > 0 != $bool_s_ab) return false;

		    return true;
		}

		/**
		* Return true if the given Triangle is equal, false if not
		*/
		public function isEqual(Triangle $t){
			return $this === $t;
		}




		/**
		* Calculate distance between each edge and search the largest edge
		*/
		private function calcDistance(){

			$this->distance = array();

			$this->distance[0] = $this->getDistance($this->vertexes[0],$this->vertexes[1]);
			$this->distance[1] = $this->getDistance($this->vertexes[1],$this->vertexes[2]);
			$this->distance[2] = $this->getDistance($this->vertexes[2],$this->vertexes[0]);

			$largestEdge = 0;
			if($this->distance[1] > $this->distance[0]) $largestEdge = 1;
			if($this->distance[2] > $this->distance[0]) $largestEdge = 2;

			$this->largestEdge = $largestEdge;
		}

		/**
		* Get the distance between the given the given Pixel
		* Note : should go somewhere else in a lib
		*/
		private function getDistance(Pixel $a, Pixel $b){
			if($a->getX() < $b->getX() )
				if($a->getY() < $b->getY()){
					$x = ($b->getX() - $a->getX());
					$x = $x * $x;
					$y = ($b->getY() - $a->getY());
					$y = $y * $y;
					$d = sqrt( $x + $y );
				}
				else {
					$x = ($b->getX() - $a->getX());
					$x = $x * $x;
					$y = ($a->getY() - $b->getY());
					$y = $y * $y;
					$d = sqrt( $x + $y );
				}

			else 
				if($a->getY() < $b->getY()){
					$x = ($a->getX() - $b->getX());
					$x = $x * $x;
					$y = ($b->getY() - $a->getY());
					$y = $y * $y;
					$d = sqrt( $x + $y );
				}
				else {
					$x = ($a->getX() - $b->getX());
					$x = $x * $x;
					$y = ($a->getY() - $b->getY());
					$y = $y * $y;
					$d = sqrt( $x + $y );
				}

			return $d;
		}

		/**
		* Indexes the pixel of each edge
		* Not used
		*/
		private function calcTrianglePixelsFromVertex(Pixel $s1, Pixel $s2){
			if($s1->getY() != $s2->getY()) {

				if($s1->getX() < $s2->getX()) {
					$coef = ( $s2->getY() - $s1->getY() ) / ( $s2->getX() - $s1->getX() );
					$ordOri = $s1->getY() - $coef * $s1->getX();

					//si la différence entre les deux points en x est < à celle en Y
					if($s1->getY() < $s2->getY()) $difY = $s2->getY() - $s1->getY();
					else $difY = $s1->getY() - $s2->getY();

					if( $s2->getX() - $s1->getX() >=  $difY) {
						for($i=$s1->getX(); $i<=$s2->getX(); $i++){
							$y = $coef * $i + $ordOri ;
							$this->pixels[] = new Pixel($i, $y);
						}
					} else {

						if($s1->getY() < $s2->getY()) {
							for($i=$s1->getY(); $i<=$s2->getY(); $i++){
								$this->pixels[] = new Pixel( ($i - $ordOri)/$coef , $i);
							}
						} else {
							for($i=$s2->getY(); $i<=$s1->getY(); $i++){
								$this->pixels[] = new Pixel( ($i - $ordOri)/$coef , $i);
							}
						}

					}
					
				} else if ($s1->getX() > $s2->getX()) {
					$coef = ( $s1->getY() - $s2->getY() ) / ( $s1->getX() - $s2->getX() );
					$ordOri = $s2->getY() - $coef * $s2->getX();

					for($i=$s2->getX(); $i<=$s1->getX(); $i++){
						$this->pixels[] = new Pixel($i, $coef * $i + $ordOri);
					}
				} else { // if same X : vertical line
					if($s1->getY() < $s2->getY()) {
						for($i=$s1->getY(); $i<=$s2->getY(); $i++){
							$this->pixels[] = new Pixel($s1->getX(), $i);
						}
					} else {
						for($i=$s2->getY(); $i<=$s1->getY(); $i++){
							$this->pixels[] =new Pixel($s1->getX(),  $i);
						}
					}
				}
			} else { // same Y : horizontal line
				if($s1->getX() < $s2->getX()) {
					for($i=$s1->getX(); $i<=$s2->getX(); $i++){
						$this->pixels[] = new Pixel($i, $s1->getY());
					}
				} else {
					for($i=$s2->getX(); $i<=$s1->getX(); $i++){
						$this->pixels[] =new Pixel($i,  $s2->getY());
					}
				}
				
			} // end else
		}

		/**
		* Calculate all needed math stuff : coeficient and y-intercept (called ordOri here)
		*/
		private function calcMath(){

			$this->math = array();

			list($coef, $ordOri) = $this->calcMathPointToPoint($this->vertexes[0], $this->vertexes[1]);
			$this->math[0]["coef"] = $coef;
			$this->math[0]["ordOri"] = $ordOri;

			list($coef, $ordOri) = $this->calcMathPointToPoint($this->vertexes[1], $this->vertexes[2]);
			$this->math[1]["coef"] = $coef;
			$this->math[1]["ordOri"] = $ordOri;

			list($coef, $ordOri) = $this->calcMathPointToPoint($this->vertexes[2], $this->vertexes[0]);
			$this->math[2]["coef"] = $coef;
			$this->math[2]["ordOri"] = $ordOri;
		} 

		/**
		* Calculte the math with the given Pixel
		* Note : should go somewhere else
		*/
		private function calcMathPointToPoint(Pixel $s1, Pixel $s2){

			if($s1->getX() < $s2->getX()) {
				$coef = ( $s2->getY() - $s1->getY() ) / ( $s2->getX() - $s1->getX() );
				$ordOri = $s1->getY() - $coef * $s1->getX();
				
				// horizontale
				if($coef == 0 && $ordOri == 0) $coef = $ordOri = -1;

			} else if ($s1->getX() > $s2->getX()) {
				$coef = ( $s1->getY() - $s2->getY() ) / ( $s1->getX() - $s2->getX() );
				$ordOri = $s2->getY() - $coef * $s2->getX();

				// horizontale
				if($coef == 0 && $ordOri == 0) $coef = $ordOri = -1;

			} else { // if same X : vertical line
				$coef = 0;
				$ordOri = 0;

			}

			return array($coef, $ordOri);
		}

		/**
		* Pick a random poin on a random edge and return it as a Pixel
		*/
		private function pickRandPointOnEdge(){

			$point = null;

			$choosedVertice = mt_rand(1, 3);
			switch ($choosedVertice) {
				case 1:
					$point = $this->getPointBetweenPoint(0,1);
					break;
				case 2:
					$point = $this->getPointBetweenPoint(1,2);
					break;
				case 3:
					$point = $this->getPointBetweenPoint(2,0);
					break;
			}

			return $point;
		}

		/**
		* Return an array mit the coordonate of each vertex as : 
		* 		0 : x of first vertex, 2: y of first vertex, and so on
		*/
		public function getArrayXY(){
			$myArray = array();

			$myArray[] = $this->vertexes[0]->getX();
			$myArray[] = $this->vertexes[0]->getY();
			$myArray[] = $this->vertexes[1]->getX();
			$myArray[] = $this->vertexes[1]->getY();
			$myArray[] = $this->vertexes[2]->getX();
			$myArray[] = $this->vertexes[2]->getY();

			return $myArray;
		}

		/**
		* Calculte the Pixel composing triangles edges
		* Not Used
		*/
		private function calcPixels(){
			$this->pixels = array();
			$this->calcTrianglePixelsFromVertex($this->$this->vertexes[0], $this->$this->vertexes[1]);
			$this->calcTrianglePixelsFromVertex($this->$this->vertexes[1], $this->$this->vertexes[2]);
			$this->calcTrianglePixelsFromVertex($this->$this->vertexes[2], $this->$this->vertexes[0]);
		}

		/**
		* Return an array with the bouding box : minx, minY, maxX, maxY
		*/
		private function calcMinMax(){

			$closerX = $this->vertexes[0]->getX();
			$closerY = $this->vertexes[0]->getY();

			$fartherX = $this->vertexes[0]->getX();
			$fartherY = $this->vertexes[0]->getY();

			foreach ($this->vertexes as $key => $v) {
				if($v->getX() < $closerX )
					$closerX = $v->getX();
				if($v->getY() < $closerY )
					$closerY = $v->getY();

				if($v->getX() > $fartherX )
					$fartherX = $v->getX();
				if($v->getY() > $fartherY )
					$fartherY = $v->getY();
			}

			return array($closerX,  $closerY, $fartherX, $fartherY);
		}


	}


?>