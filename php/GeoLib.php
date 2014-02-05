<?php

	
	function orient2d(Pixel $v1, Pixel $v2, Pixel $v3){
		//        (b.x-a.x)*(c.y-a.y) - (b.y-a.y)*(c.x-a.x);
		return ($v2->x - $v1->x)*($v3->y - $v1->y) - ($v2->y - $v1->y)*($v3->x - $v1->x);
	} 

?>