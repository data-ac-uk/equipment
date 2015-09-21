<?php

if(!isCommandLineInterface()){
	chdir("../..");
	include("index.php");
}

$src = $argv[1];

if(!file_exists($src)){
	die("file not exists");
}

$in = basename($src);
$dir = dirname($src);

chdir($dir);

echo "Large\n";
$cmd = "convert -density 300  -colorspace sRGB ".escapeshellarg($in)." -alpha off -resize 1300 page-%d-large.png";
`$cmd`;

echo "Page\n";
$cmd = "convert -density 150  -colorspace sRGB ".escapeshellarg($in)." -alpha off -resize 420x595 page-%d.png";
`$cmd`;

echo "Thumb\n";
$cmd = "convert -density 75  -colorspace sRGB ".escapeshellarg($in)." -alpha off -resize 200 thumb-%d.png";
`$cmd`;


function isCommandLineInterface()
{
    return (php_sapi_name() === 'cli');
}