<?php

$id = preg_replace( '/[^a-f0-9]/','',$_GET["id"] );
readfile( "../var/item/$id" );
