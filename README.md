Equerre - image triangularisation
=================================

Random triangularisation of image in php

![ImageExample](http://api.equer.re/uploads/2014/equerre_eqquerise.png)

TO DO
-----
* Change API to use base64 image upload and simplify the use
* Color option

How to use
----------


####First Call
The API url is [http://api.equer.re/?image](http://api.equer.re/?image)

The first time you use the API, you have to upload the image. The received data should be as follow :

*$_FILES*

    [Image] => Array
      (
        [name] => 1106947_10142208.jpg
        [type] => image/jpeg
        [tmp_name] => /tmp/phpULeri6
        [error] => 0
        [size] => 141307
      ) 

*$_POST*

    [imageParam] => 
        {
            "numberDetail":4,
            "stretch":20
        } 

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


####Other call
If the image has already been uploaded, you just have to give the image path and the parameters. The image path is `result['imgPath'] in last code
The data should be formating like

    "imageParam" : {
    	"numberDetail" :  5,
    	"stretch" : 20,
    	"imgPath" : "../uploads/2014/02/452145.jpg"
    }

####Last Request
Last request on the api is saved in [this file](http://api.equer.re/api/test.txt)


### Result JSON
Each request result in a JSON formated as follow

    {
        "imageBase64" : "iVBORw0KG---BASE64-IMAGE-DATA-5CYII=",
        "timeProccesed " : 0.32224702835083,
        "numberOfTriangle" : 92,
        "imgPath" : "../uploads/2014/02/1106947101422010.jpg",
        "imgName" : "1106947101422010.jpg"
    }




