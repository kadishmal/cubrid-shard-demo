<?php
include_once 'shard_conf.php';

try
{
	$conn = new PDO("cubrid:dbname=" . $db_name . ";host=" . $host . ";port=" . $port, $username, $password);

	$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $E)
{
	exit('Connect Error (' . $E->getCode() . ')' . $E->getMessage());
}

echo "Connected!\n";