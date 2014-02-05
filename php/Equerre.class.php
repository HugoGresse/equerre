<?php
/*
	Triangle generator 
	Author: Hugo Gresse xjet.fr
	Version 0.5
	Changelog : 
		0.1 - Base for drawing triangle
		0.2 - change approche to dicotomie
		0.3 - First color from other image
		0.4 - change triangle rasterisation
		0.5 - Back to the old triangle rasterisation, less clean but 2x faster
			  Clean the file
	
	TODO: 
		- change image size 499 and not 500

	Optional TO DO : 
		- log (maybe not very usefull)
	
	Source : 
		- triangle rasterisation : http://fgiesen.wordpress.com/2013/02/10/optimizing-the-basic-rasterizer/

*/

require_once "TriangleMatrix.class.php";

class Equerre {

	/**
	* Change php procces time limit
	*/
	private $time_limit = 100;

	/**
	* Original image path
	*/
	private $imageBasePath;

	/**
	* Output GD Image
	*/
	private $image;

	/**
	* Original GD Image
	*/
	private $imageBase;

	/**
	* Image size width
	*/
	private $imageWidth =  0; 

	/**
	* Image size height
	*/
	private $imageHeight = 0;

	/**
	* Set the size of a tiny triangle (used to reduce processing on tiny triangle)
	*/
	private $tinyTriangle = 15;

	/**
	* The TriangleMatrix stored object
	*/
	private $triangleMatrix; // store all triangles

	/**
	*
	*	- numberColor default 10;
	*
	*	- dichotomieNumb default 4
	*
	*	- stretch default 3
	*   	More coefMin is big, closer can be the new point from others
	*
	**/
	private $options = array();

	/**
	* Duration of generation
	*/
	private $processDuration = 0;


	/*
	* Init the Equerre stuff with given path to img and param in an array
	*
	*/
	public function __construct($imagePath, $param = null){

		$this->imageBasePath = $imagePath;

		// read param
		// $param = json_decode($param);

		if( isset($param['numberDetail']) ) 
			$this->options['dichotomieNumb'] = $param['numberDetail']; // numberOfTriangle
		else $this->options['dichotomieNumb'] = 5;

		if( isset($param['stretch']) ) 
			$this->options['stretch'] = $param['stretch'];
		else $this->options['stretch'] = 10;
		
		if( isset($param['numberColor']) ) 
			$this->options['numberColor'] = $param['numberColor'];
		else $this->options['numberColor'] = 10;

		$this->imageLoad();

		$this->triangleMatrix = new TriangleMatrix();	
	}

	/**
	* Create the new image with triangle and calculate color
	*/
	public function processEquerre(){
		set_time_limit($this->time_limit);
		$timestart=microtime(true);


		$this->cutRectangle();
		$this->startTriangleProcess();

		// $this->colorAllocate("auto"); // Change colorisation to random color
		$this->colorAllocate();


		$this->drawTriangles();

		$this->processDuration = microtime(true)-$timestart;
	}

	/**
	* Return the process duration
	*/
	public function getProcessDuration(){
		return $this->processDuration;
	}

	/**
	* Return the number of triangle in the final image
	*/
	public function getNumberTriangle(){
		return $this->triangleMatrix->count();
	}

	/**
	* Clear object
	*/
	public function delete(){
		imagedestroy($this->imageBase);
		$this->triangleMatrix = null;
		$this->options = null;
	}

	/**
	* Return the proccesed image in base64
	*/
	public function getBase64Image(){
		ini_set('memory_limit', -1);
		$fileGeneratePath = substr($this->imageBasePath, 0, 19)."0generate-".substr($this->imageBasePath, 19);

		// Another way to convert image into base64 whitout saving the images
		// if( $this->extExif == ".jpg" ) {
		// 	imagejpeg($this->image);
		// } elseif($this->extExif == ".gif" ) {
		// 	imagegif($this->image);
		// } elseif( $this->extExif == ".png" ) {
		// 	imagepng($this->image);
		// } else {
		// 	ob_end_clean (); 
		// 	exit(" invalid image type ");
		// }

		imagepng($this->image, $fileGeneratePath);
		imagedestroy($this->image);
		$imgBase64 = base64_encode(file_get_contents($fileGeneratePath));

		unlink($fileGeneratePath);

		ini_restore('memory_limit');
		
		return $imgBase64;
	}



	/**
	* Load original image and create a new one with same size
	*/
	private function imageLoad(){

		if( ! $image_type = exif_imagetype($this->imageBasePath))
		exit("Photos not valid");

		if( $image_type == IMAGETYPE_JPEG ) {
			$this->extExif = ".jpg";
		} elseif( $image_type == IMAGETYPE_GIF ) {
			$this->extExif = ".gif";
		} elseif( $image_type == IMAGETYPE_PNG ) {
			$this->extExif = ".png";
		} else exit(" invalid image format, only jpg, gif, png ");

		
		//create GD image according to his filetype
		if( $this->extExif == ".jpg" ) {
			$this->imageBase = imagecreatefromjpeg($this->imageBasePath);
		} elseif($this->extExif == ".gif" ) {
			$this->imageBase = imagecreatefromgif($this->imageBasePath);
		} elseif( $this->extExif == ".png" ) {
			$this->imageBase = imagecreatefrompng($this->imageBasePath);
		} else exit(" invalid image type ");


		//get orignal image size
		$image_info = getimagesize($this->imageBasePath);
		$this->imageWidth = $image_info[0] -1;
		$this->imageHeight = $image_info[1] -1;

		$this->image = imagecreatetruecolor($this->imageWidth, $this->imageHeight);
	}
	
	/**
	* Cut rectangle/new image into 3 or 4 triangles (depends, it's random)
	*/
	private function cutRectangle(){


		/** 0       1
		*	*-------*
		*   .      /.
		*   .     / .
		*   .    /  .
		*   .   /   .
		*   .  /    .
		*   . /     .
		*   ./      .
		*   *-------*
		*   2     
		*/

		// == INIT
		$numberOfCut = mt_rand(1,2);
		$choosedEdge1 = mt_rand(1,2);
		$choosedEdge2 = 0;
		$deltaX = $deltaY = 0;
		$point1 = new Pixel();
		$cornerTopLeft = new Pixel(0,0);
		$cornerTopRight = new Pixel($this->imageWidth, 0);
		$cornerBottomLeft = new Pixel(0, $this->imageHeight);
		$cornerBottomRight = new Pixel($this->imageWidth, $this->imageHeight);

		// == calculate min x/y and max 
		$deltaX = $this->imageWidth /  $this->options['stretch'];
		$deltaY = $this->imageHeight /  $this->options['stretch'];

		if($numberOfCut == 1){ // one point to cut the image
			// echo "-- One cut -- ";
			$choosedEdge1 = mt_rand(1,4);
			switch ($choosedEdge1) {
				case 1:
					$point1->setX(mt_rand(0 + $deltaX, $this->imageWidth - $deltaX));
					$point1->setY(0);

					//top left
					$this->triangleMatrix->add(new Triangle($cornerTopLeft, $point1, $cornerBottomLeft) ) ;
					//top right
					$this->triangleMatrix->add(new Triangle($point1, $cornerTopRight, $cornerBottomRight) ) ;
					//bottom
					$this->triangleMatrix->add(new Triangle($cornerBottomLeft, $point1, $cornerBottomRight) ) ;
					break;
				case 2:
					$point1->setX($this->imageWidth);
					$point1->setY(mt_rand(0 + $deltaY, $this->imageHeight - $deltaY));

					//top
					$this->triangleMatrix->add(new Triangle($cornerTopLeft, $cornerTopRight, $point1) ) ;
					//center
					$this->triangleMatrix->add(new Triangle($cornerTopLeft, $point1, $cornerBottomLeft) ) ;
					//bottom
					$this->triangleMatrix->add(new Triangle($cornerBottomLeft, $point1, $cornerBottomRight) ) ;

					break;
				case 3:
					$point1->setX(mt_rand(0 + $deltaX, $this->imageWidth - $deltaX));
					$point1->setY($this->imageHeight);
					//left
					$this->triangleMatrix->add(new Triangle($cornerTopLeft, $cornerTopRight, $point1) ) ;
					//top
					$this->triangleMatrix->add(new Triangle($cornerTopLeft, $point1, $cornerBottomLeft) ) ;
					//right
					$this->triangleMatrix->add(new Triangle($cornerTopRight, $cornerBottomRight, $point1) ) ;

					break;
				case 4:
					$point1->setX(0);
					$point1->setY(mt_rand(0 + $deltaY, $this->imageHeight - $deltaY));
					//top
					$this->triangleMatrix->add(new Triangle($cornerTopLeft, $cornerTopRight, $point1) ) ;
					//middle
					$this->triangleMatrix->add(new Triangle($point1, $cornerTopRight, $cornerBottomRight) ) ;
					//bottom
					$this->triangleMatrix->add(new Triangle($point1, $cornerBottomRight, $cornerBottomLeft) ) ;

					break;	
			}

		} else if($numberOfCut == 2){ // two point to cut the image
			// echo "-- 2 cut -- ";


			$point2 = new Pixel();
			$choosedEdge2 = mt_rand(1,4);

			// echo "<br/> - - - - $choosedEdge1 - $choosedEdge2 <br/>";
			if($choosedEdge2 == $choosedEdge1) {
				$choosedEdge2 += 1; // choosedEdge1 is always < 3
			} else if($choosedEdge2 < $choosedEdge1){
				$temp = $choosedEdge1;
				$choosedEdge1 = $choosedEdge2;
				$choosedEdge2 = $temp;
			}

			// == pink a point on edge (top or right) of image
			if($choosedEdge1 == 1){
				$point1->setX(mt_rand(0 + $deltaX, $this->imageWidth - $deltaX));
			} else if($choosedEdge1 == 2){
				$point1->setX($this->imageWidth);
				$point1->setY(mt_rand(0 + $deltaY, $this->imageHeight - $deltaY));
			} else throw new Exception('Random number fail');

			// echo "<br/> - - - - $choosedEdge1 - $choosedEdge2 <br/>";

			switch ($choosedEdge2) {
				case 1:
					$point2->setX(mt_rand(0 + $deltaX, $this->imageWidth - $deltaX));
					$point2->setY(0);
					break;
				case 2:
					$point2->setX($this->imageWidth);
					$point2->setY(mt_rand(0 + $deltaY, $this->imageHeight - $deltaY));
					break;
				case 3:
					$point2->setX(mt_rand(0 + $deltaX, $this->imageWidth - $deltaX));
					$point2->setY($this->imageHeight);
					break;
				case 4:
					$point2->setX(0);
					$point2->setY(mt_rand(0 + $deltaY, $this->imageHeight - $deltaY));
					break;	
				default:
					throw new Exception('switch choosedEdge 2 fail');
					break;
			}

			if($choosedEdge1 == 1 && $choosedEdge2 == 2){ 
				// echo "- 1-2 - ";

				// echo "point 1 : ".$point1."<br/>";
				// echo "point 2 : ".$point2."<br/>";

				//top left
				$this->triangleMatrix->add( new Triangle($cornerTopLeft, $point1, $cornerBottomLeft) ) ;
				//top right
				$this->triangleMatrix->add( new Triangle($point1, $cornerTopRight, $point2) ) ;
				//center
				$this->triangleMatrix->add( new Triangle($cornerBottomLeft, $point1, $point2) ) ;
				//bottom right
				$this->triangleMatrix->add( new Triangle($cornerBottomLeft, $point2, $cornerBottomRight) ) ;

			} else if($choosedEdge1 == 1 && $choosedEdge2 == 3){
				// echo "- 1-3 - ";
				//top left
				$this->triangleMatrix->add( new Triangle($cornerTopLeft, $point1, $cornerBottomLeft) ) ;
				//top right
				$this->triangleMatrix->add( new Triangle($point1, $cornerTopRight, $point2) ) ;
				//bottom left
				$this->triangleMatrix->add( new Triangle($cornerBottomLeft, $point1, $point2) ) ;
				//bottom right
				$this->triangleMatrix->add( new Triangle($point2, $cornerTopRight, $cornerBottomRight) ) ;

			} else if($choosedEdge1 == 1 && $choosedEdge2 == 4){  
				// echo "- 1-4 - ";
				//top left
				$this->triangleMatrix->add( new Triangle($cornerTopLeft, $point1, $point2) ) ;
				//top right
				$this->triangleMatrix->add( new Triangle($point1, $cornerTopRight, $cornerBottomRight) ) ;
				//center
				$this->triangleMatrix->add( new Triangle($point1, $cornerBottomRight, $point2) ) ;
				//bottom left
				$this->triangleMatrix->add( new Triangle($point2, $cornerBottomRight, $cornerBottomLeft) ) ;

			} else if($choosedEdge1 == 2 && $choosedEdge2 == 3){
				// echo "- 2-3 - ";
				//top 
				$this->triangleMatrix->add( new Triangle($cornerTopLeft, $cornerTopRight, $point1) ) ;
				//center
				$this->triangleMatrix->add( new Triangle($cornerTopLeft, $point1, $point2) ) ;
				//bottom left
				$this->triangleMatrix->add( new Triangle($cornerTopLeft, $point2, $cornerBottomLeft) ) ;
				//bottom right
				$this->triangleMatrix->add( new Triangle($point1, $point2, $cornerBottomRight) ) ;

			} else if($choosedEdge1 == 2 && $choosedEdge2 == 4){
				// echo "- 2-4 - ";
				//top right
				$this->triangleMatrix->add( new Triangle($cornerTopLeft, $cornerTopRight, $point1) ) ;
				//top left
				$this->triangleMatrix->add( new Triangle($cornerTopLeft, $point1, $point2) ) ;
				//bottom right
				$this->triangleMatrix->add( new Triangle($point2, $point1, $cornerBottomRight) ) ;
				//bottom left
				$this->triangleMatrix->add( new Triangle($point2, $cornerBottomRight, $cornerBottomLeft) ) ;
			} else {
				echo "should not be here : $choosedEdge1  -  $choosedEdge2";
			}
		} // end if($numberOfCut == 2){
	} // end cutRectangle function

	/**
	* Start bisection on first triangles
	*/
	private function startTriangleProcess(){

		$trangleMatrixBase = $this->triangleMatrix->get();
		$this->triangleMatrix->clear();

		foreach ($trangleMatrixBase as $key => $triangle) {		
			$this->processTriangle($triangle);
		}
	}

	/**
	* Cut rectangle into two others, remove the first from the matrix
	*  		If triangle is too tiny,didn't cut new triangle
	* 
	* Param : a Triangle and the number of pass on it's parent
	*/
	private function processTriangle(Triangle $tri, $count = 0){

		$this->triangleMatrix->remove($tri);

		$v0 = $tri->getVertex(0);
		$v1 = $tri->getVertex(1);
		$v2 = $tri->getVertex(2);

		$t1p0 = $t1p1 = $t1p2 = $t2p0 = $t2p1 = $t2p2 = null;

		switch ($tri->getLargestEdge()) {
			case 0: // 0 to 1

				$newPoint = $tri->getPointBetweenPoint(0,1,  $this->options['stretch']);
				// $newPoint = $tri->getPointOnLargestEdge($this->options['stretch']);

				$t1p0 = $v0;
				$t1p1 = $newPoint;
				$t1p2 = $v2; 

				$t2p0 = $newPoint;
				$t2p1 = $v1;
				$t2p2 = $v2;

				break;
			case 1: // 1 to 2
				$newPoint = $tri->getPointBetweenPoint(1,2,  $this->options['stretch']);
				// $newPoint = $tri->getPointOnLargestEdge($this->options['stretch']);

				$t1p0 = $v0;
				$t1p1 = $v1;
				$t1p2 = $newPoint; 

				$t2p0 = $v0;
				$t2p1 = $newPoint;
				$t2p2 = $v2;
				break;
			case 2: // 2 to 0
				$newPoint = $tri->getPointBetweenPoint(2,0,  $this->options['stretch']);
				// $newPoint = $tri->getPointOnLargestEdge($this->options['stretch']);

				$t1p0 = $v0;
				$t1p1 = $v1;
				$t1p2 = $newPoint; 

				$t2p0 = $newPoint;
				$t2p1 = $v1;
				$t2p2 = $v2;
				break;
			
		}

		$firstTriangle  = new Triangle($t1p0, $t1p1, $t1p2);
		$secondTriangle  = new Triangle($t2p0, $t2p1, $t2p2);

		$this->triangleMatrix->add( $firstTriangle );
		$this->triangleMatrix->add( $secondTriangle );

		// if we should cut triangle, check if his not too much tiny
		if($count < $this->options['dichotomieNumb'] ) {

			list($minX, $minY, $maxX, $maxY) = $firstTriangle->getMinMax();
			if( (($maxX - $minX) > $this->tinyTriangle) && (($maxY - $minY) > $this->tinyTriangle) )
				$this->processTriangle($firstTriangle , $count + 1);


			list($minX, $minY, $maxX, $maxY) = $secondTriangle->getMinMax();
			if( (($maxX - $minX) > $this->tinyTriangle) && (($maxY - $minY) > $this->tinyTriangle) )
				$this->processTriangle($secondTriangle , $count + 1);
		}
	}

	/**
	* Calc the color of the triangle :
	* 		- if $mode == "image" : from imageBase
	*		- else : random color
	*/
	private function colorAllocate($mode = "image"){

		foreach ($this->triangleMatrix->get() as $key => $triangle) {
			
			if($mode == "image"){
				list($r, $g, $b) = $this->getTriangleAvgColor($triangle);
			} else {
				$r = mt_rand(0, 255);
				$g = mt_rand(0, 255);
				$b = mt_rand(0, 255);
			}

			$triangle->setColor( new Color($r, $g, $b) );
		}
	}

	/**
	* Return an array with the color corresponding to the triangle on image base
	* The algo for browing the triangle is quite bad but very efficient
	*/
	private function getTriangleAvgColor(Triangle $t){
		$pixelCount = 0;
		$r = $g = $b = 0;

		/**
		* 1. Get closer to origin point
		* 2. Check left or right point is inside triangle
		* 3. If yes, go in the right direction
		* 4. When the cursor moove outisde triangle, go to next point. 
		*
		**/
		list($x, $y, $xMax, $yMax) = $t->getMinMax();

		if( (($xMax - $x) < $this->tinyTriangle) && (($yMax - $y) < $this->tinyTriangle) ) {
			// too tiny triangle, only get color on edge
			foreach($t->getListVertex() as $vertex){
				$colors = imagecolorat($this->imageBase, $vertex->x, $vertex->y);
				$colors = imagecolorsforindex($this->imageBase, $colors);
				$r += $colors["red"];
				$g += $colors["green"];
				$b += $colors["blue"];
			}

			return array($r/3, $g/3, $b/3);
		}

		for($y; $y<=$yMax; $y++){

			if( $t->isInsideTriangle(new Pixel($x+1, $y)) ){
				for($x; $x<=$xMax; $x++){
					//current point not in triangle, stop loop
					if( !$t->isInsideTriangle(new Pixel($x, $y) ) )
						break;

					$colors = imagecolorat($this->imageBase, $x, $y);
					$colors = imagecolorsforindex($this->imageBase, $colors);
					$r += $colors["red"];
					$g += $colors["green"];
					$b += $colors["blue"];
					$pixelCount++;
				}
			} else if($t->isInsideTriangle(new Pixel($x-1, $y))){
				for($x; $x>=$xMax; $x--){
					//current point not in triangle, stop loop
					if( !$t->isInsideTriangle(new Pixel($x, $y) ) )
						break;

					$colors = imagecolorat($this->imageBase, $x, $y);
					$colors = imagecolorsforindex($this->imageBase, $colors);
					$r += $colors["red"];
					$g += $colors["green"];
					$b += $colors["blue"];
					$pixelCount++;
				}
			} else {

				if($y >= $this->imageHeight){
					// var_dump($t->getListVertex());
					// var_dump($borne);
					// echo "****";
				}
				// throw new Exception('Nothing left or right of given point');
				$colors = imagecolorat($this->imageBase, $x, $y);
				$colors = imagecolorsforindex($this->imageBase, $colors);
				$r += $colors["red"];
				$g += $colors["green"];
				$b += $colors["blue"];
				$pixelCount++;
			}

		} // end for

		
		if($pixelCount == 0){
			// echo "<br> pixelCount : $pixelCount  -  r:$r, g:$g, b:$b";
			// black tirangle if here
		} else {
			$r = $r/$pixelCount;
			$g = $g/$pixelCount;
			$b = $b/$pixelCount;

		}

		return array($r, $g, $b);
	} // end getTriangleAvgColor

	/**
	* Same as getTriangleAvgColor but with a more complex and proper browsing
	* Return an array with the color corresponding to the triangle on image base
	*
	* Not used : 2x slower than getTriangleAvgColor
	*/
	private function getTriangleAvgColorWithRasterisation(Triangle $t){

		$pixelCount = 0;
		$r = $g = $b = 0;

		list($v1, $v2, $v3) = $t->getListVertex();

		/* get the bounding box of the triangle */
		$maxX = max($v1->x, max($v2->x, $v3->x));
		$minX = min($v1->x, min($v2->x, $v3->x));
		$maxY = max($v1->y, max($v2->y, $v3->y));
		$minY = min($v1->y, min($v2->y, $v3->y));


		// Triangle setup
	    $A01 = $v1->y - $v2->y;
	    $B01 = $v2->x - $v1->x;
	    $A12 = $v2->y - $v3->y;
	    $B12 = $v3->x - $v2->x;
	    $A20 = $v3->y - $v1->y;
	    $B20 = $v1->x - $v3->x;

	    // Barycentric coordinates at minX/minY corner
		$p = new Pixel($minX, $minY);
		$w0_row = orient2d($v2, $v3, $p);
		$w1_row = orient2d($v3, $v1, $p);
		$w2_row = orient2d($v1, $v2, $p);

		// Rasterize
		for ($p->y = $minY; $p->y <= $maxY; $p->y++) {
			// Barycentric coordinates at start of row
			$w0 = $w0_row;
			$w1 = $w1_row;
			$w2 = $w2_row;

			for ($p->x = $minX; $p->x <= $maxX; $p->x++) {
				// If p is on or inside all edges, render pixel.
				if (($w0 | $w1 | $w2) >= 0){
					/* inside triangle */
					$colors = imagecolorat($this->imageBase, $p->x, $p->y);
					$colors = imagecolorsforindex($this->imageBase, $colors);
					$r += $colors["red"];
					$g += $colors["green"];
					$b += $colors["blue"];
					$pixelCount++;
				} 

				// One step to the right
				$w0 += $A12;
				$w1 += $A20;
				$w2 += $A01;
			}

			// One row step
			$w0_row += $B12;
			$w1_row += $B20;
			$w2_row += $B01;
		}

		if($pixelCount != 0) {
			$r = $r/$pixelCount;
			$g = $g/$pixelCount;
			$b = $b/$pixelCount;
		}
		
		return array($r, $g, $b);
	}

	/**
	* Draw all triangles from TriangleMatrix to ouput gd image with the right colors
	*/
	private function drawTriangles(){
		
		imageantialias($this->image, true);

		foreach ($this->triangleMatrix->get() as $key => $triangle) {
			

			$color = imagecolorallocate($this->image, $triangle->getColor()->getRed(), $triangle->getColor()->getGreen(), $triangle->getColor()->getBlue()) ;

			$myPoints = $triangle->getArrayXY(); 

			imagefilledpolygon($this->image, $myPoints, 3, $color );

			//draw a not filled polygon because it's antialliassed and filled not
			imagePolygon($this->image, $myPoints, 3, $color );

		}
	}

	/**
	* Dev method for debugging purpose only
	*/
	private  function display(){

		echo "Duration : ".$this->processDuration." <br> <br> <br>";
		echo '<img style=" position : absolute; top : 0; right : 0; " src="data:image/png;base64,'.$this->getBase64Image().'" /> ';

	}

} // END CLASS

?>