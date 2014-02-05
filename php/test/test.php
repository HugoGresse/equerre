<?php

require_once "Equerre.class.php";

class Test {

	public $image;
	public $triangleMatrix;

	public function __construct(){

		$equerre = new Equerre("test/hugo_reduce.JPG");
		$equerre->processEquerre();

		echo "Duration : ".$equerre->getProcessDuration()." <br> <br> <br>";
		echo '<img width="1000" style="position : absolute; top : 0; right : 0; " src="data:image/png;base64,'.$equerre->getBase64Image().'" /> ';

	}



}


$test = new Test();

?>