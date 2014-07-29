<?php

$in = "Useful Links SRC.html";
$out = "Useful Links.html";

$page = file_get_contents($in);

$oupage = preg_replace("/resources\/(.*)\.png/e", 'imageproc("\\1")', $page);

function imageproc($image){
	return "data:image/png;base64,".base64_encode(file_get_contents("resources/{$image}.png"));
}

file_put_contents($out,$oupage);


?>