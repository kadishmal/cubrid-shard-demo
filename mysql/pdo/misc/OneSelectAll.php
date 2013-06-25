<?php

include_once __DIR__ . '/../shard_connection.php';
include_once __DIR__ . '/OverriddenPDOStatement.php';

try
{
	$conn->setAttribute(PDO::ATTR_STATEMENT_CLASS, array("OverriddenPDOStatement"));

	for ($i = 0; $i < $shards_count; ++$i)
	{
		$sql = "SELECT * FROM tbl_posts /*+ shard_id(" . $i . ") */";

		echo "Executing: " . $sql . PHP_EOL;

		$req  = $conn->query($sql);

		$cols = $req->columnCount();
		$rows = $req->rowCount();

		$columns         = array();
		$column_metadata = array();

		echo "Number of columns: " . $cols . PHP_EOL;

		foreach ($req->fetchAll() as $row)
		{
			foreach($row as $k => $v)
			{
				echo $v . " ";
			}

			echo PHP_EOL;
		}

		echo "Shard(" . $i . ") holds " . $rows . " records." . PHP_EOL;
	}
}
catch(PDOException $E)
{
	exit('Failed: (' . $E->getCode() . ')' . $E->getMessage());
}

$conn = null;

echo "Connection is closed." . PHP_EOL;