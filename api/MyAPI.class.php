<?php
require_once 'API.class.php';
require_once '../php/EquerreManager.class.php';

class MyAPI extends API {

	protected $imageParam;
	protected $imageRequest;

	public function __construct($request, $image, $origin) {
		parent::__construct($request);

		// first upload : upload image and parameter
		if( !empty($image))
			$this->imageParam = json_decode(stripslashes($request['imageParam']), true);
		else // if image already on serv
			$this->imageParam = json_decode(stripslashes(json_encode($request['imageParam'])), true);
				
		if(isset( $image['Image']))
			$this->imageRequest = $image['Image'];
		else $this->imageRequest = $this->imageParam['imgPath'];
	}

	/*
	* Main endpoint
	*/
	protected function image() {

		if ($this->method == 'POST') {

			if (!isset($this->imageParam['imgPath']) && $this->imageRequest['error'] !== 0) 
				return;

			$manager = new EquerreManager($this->imageRequest, $this->imageParam);
			
			return $manager->getResult();


		} else {
			return " only POST request accepted ";
		}
	}
 }


?>