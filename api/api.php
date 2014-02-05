<?php
	
	require_once 'MyAPI.class.php';

	// Requests from the same server don't have a HTTP_ORIGIN header
	if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
		$_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
	}
	
	try {
		$file = 'test.txt'; $current = " ------- \$_FILES ------- \n".print_r($_FILES, true)." \n ------- \$_REQUEST ------- \n".print_r($_REQUEST, true); 
		file_put_contents($file, $current);

		$API = new MyAPI($_REQUEST, $_FILES, $_SERVER['HTTP_ORIGIN']);

		echo $API->processAPI();
	} catch (Exception $e) {
		echo json_encode(Array('error' => $e->getMessage()));
	}

	exit();
?>