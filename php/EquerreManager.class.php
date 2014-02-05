<?php

require_once 'Equerre.class.php';

class EquerreManager {

	private $equerre;
	private $imgPath;

	public function __construct($imageRequest, $param){

		/* Save the image in ftp if $param have no image name*/
		// if no imgPath give from parameter : 
		if( !isset($param['imgPath'])) {
			$this->uploadImage($imageRequest);
			// exit();
			$this->equerre = new Equerre($this->imgPath, $param);
			$this->equerre->processEquerre();
		} else {
			$this->imgPath = $param['imgPath'];
			$this->equerre = new Equerre($param['imgPath'], $param);
			$this->equerre->processEquerre(); 
		}
	}

	public function getResult(){
		//return json file
		$result = array(
			"imageBase64" => $this->equerre->getBase64Image(), 
			"timeProccesed" => $this->equerre->getProcessDuration(), 
			"numberOfTriangle" => $this->equerre->getNumberTriangle(),
			"imgPath" => $this->imgPath,
			"imgName" => basename ( $this->imgPath),
		);


		$this->equerre->delete();

		return json_encode($result);
	}

	/**
	*	Upload temp image an sanitize name. Also convert to png
	*	
	*
	**/
	private function uploadImage($imageRequest){
		if( ! $image_type = exif_imagetype($imageRequest["tmp_name"]))
			return "Photos not valid";
		// $image_type = exif_imagetype($imageRequest["tmp_name"]);

		if( $image_type == IMAGETYPE_JPEG ) {
			$ext = ".jpg";
		} elseif( $image_type == IMAGETYPE_GIF ) {
			$ext = ".gif";
		} elseif( $image_type == IMAGETYPE_PNG ) {
			$ext = ".png";
		} else return " invalid image format, only jpg, gif, png ";

		// create folder and name
		$dirname = "../uploads/".date("Y/m/");
		if(is_dir($dirname) == false) mkdir($dirname, 0775, true);

		$tmp_name = $imageRequest["tmp_name"];
		$name = $dirname.$this->sanitize(substr($imageRequest["name"], 0, -3)).$ext;

		// check if file already exist
		$fullpath = $this->checkAndRenameIfFileExist($name);

		move_uploaded_file($tmp_name, $fullpath);
		// unlink($tmp_name);

		// change extension to png & convert image to png
		// if($ext != ".png") {
			// $fp_toUnlink = $fullpath;
			// $fullpath = substr($fullpath, 0, -3)."png";
			// imagepng(imagecreatefromstring(file_get_contents($fp_toUnlink)), $fullpath);
			// unlink($fp_toUnlink);
		// }
		
		$this->imgPath = $fullpath;
		
		return $fullpath;
	}


	private function checkAndRenameIfFileExist($path){
		if (file_exists($path))
			// return $this->checkAndRenameIfFileExist(substr($path, 0, -3).mt_rand (0,9).substr($path,-4));
			return $this->checkAndRenameIfFileExist(substr($path, 0, -5).(substr($path, -5, 1)+1).substr($path,-4));
		else return $path;
	}


	/**
	 * Function: sanitize
	 * Returns a sanitized string, typically for URLs.
	 *
	 * Parameters:
	 *     $string - The string to sanitize.
	 *     $force_lowercase - Force the string to lowercase?
	 *     $anal - If set to *true*, will remove all non-alphanumeric characters.
	 */
	private function sanitize($string, $force_lowercase = true, $anal = false) {

		if (extension_loaded('intl') === true) {
				$string = Normalizer::normalize($string, Normalizer::FORM_KD);
		}

		if (strpos($string = htmlentities($string, ENT_QUOTES, 'UTF-8'), '&') !== false) {
				$string = html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|caron|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $string), ENT_QUOTES, 'UTF-8');
		}

	    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
	                   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
	                   "â€”", "â€“", ",", "<", ".", ">", "/", "?");
	    $clean = trim(str_replace($strip, "", strip_tags($string)));
	    $clean = preg_replace('/\s+/', "-", $clean);
	    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
	    return ($force_lowercase) ?
	        (function_exists('mb_strtolower')) ?
	            mb_strtolower($clean, 'UTF-8') :
	            strtolower($clean) :
	        $clean;
	}

}


?>