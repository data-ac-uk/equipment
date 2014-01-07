#!/usr/bin/php
<?php

require_once( "../etc/eq_config.php" );

if(substr($eq_config->db->connection,0,6) != 'mysql:'){
	die("Can only use mysql\n");
}


parse_str(str_replace(";","&", substr($eq_config->db->connection, 6) ), $con);
print_r($con);


$exec = "mysqldump -d";

$exec .= " -h ".escapeshellarg($con['host']);
$exec .= " -P ".escapeshellarg($con['port']);

$exec .= " -u ".escapeshellarg($eq_config->db->user);
$exec .= " -p".escapeshellarg($eq_config->db->password);

$exec .= " ".escapeshellarg($con['dbname']);

$exec .= " > {$eq_config->pwd}/install/mysql.sql";

passthru($exec);

echo "DB Schema Saved to {$eq_config->pwd}/install/mysql.sql\n";