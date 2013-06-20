<?php
include_once('shard_conf.php');

$conn = cubrid_connect($host, $port, $db_name, $username, $password);

if (!$conn) {
	die('Connect Error (' . cubrid_error_code() . ')' . cubrid_error_msg());
}

echo "Connected!\n";
