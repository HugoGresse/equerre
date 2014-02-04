Equerre - image triangularisation
=================================

Random triangularisation of image in php


How to use
----------



1. The first time you use the API, you have to upload the image. The received data should be as follow : 
**$_FILES**

    [Image] => Array
      (
        [name] => 1106947_10142208.jpg
        [type] => image/jpeg
        [tmp_name] => /tmp/phpULeri6
        [error] => 0
        [size] => 141307
      ) 

**$_POST**
  [imageParam] => {"numberDetail":4,"numberColor":5,"stretch":20} 

You can use the jQuery HTML5 FileUpload Plugin to call the API using: 
     GLOBAL.$imageInput.fileUpload({
    	url: "http://api.equer.re/?image",
    	type: 'POST',
    	allowDataInBase64: true,
    	imageParam: dataToSend,
    	fileType: "/^(gif|jpe?g|png?)$/i",
    	dataType: 'json',
    	success: function (result, status, xhr) {
    		result = $.parseJSON(result);
    		Main.imgUri = 'data:image/png;base64,'+ result['imageBase64'];
    		Main.imgPath = result['imgPath'];
    	}
    )};
