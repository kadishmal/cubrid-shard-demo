<?php

include_once 'shard_connection.php';

try
{
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

		for($j = 0; $j < $cols; ++$j)
		{
			$column_metadata[$j] = $req->getColumnMeta($j);

			$columns[] = $column_metadata[$j]['name'] . "(" . $column_metadata[$j]['type'] . ")";

		}

		echo join(", ", $columns) . PHP_EOL;

		foreach ($req->fetchAll() as $row)
		{
			for($j = 0; $j < $cols; ++$j)
			{
				// `varbit` represents `TEXT` data type in MySQL.
				// The raw value of `varbit` column comes in bytes. In PHP, it's
				// bytes string (HEX), so we need to convert them to UTF8.
				if (strstr($column_metadata[$j]['type'], 'varbit'))
				{
					// Since PHP 5.4 you can use [hex2bin()](http://php.net/manual/kr/function.hex2bin.php)
					// function. For lower versions, the following is a solution.
					echo pack('H*', $row[$column_metadata[$j]['name']]);
				}
				else
				{
					echo $row[$column_metadata[$j]['name']];
				}

				echo " ";
			}

			echo PHP_EOL;
		}

		echo "Shard(" . $i . ") holds " . $rows . " records." . PHP_EOL;

		$req->closeCursor();
	}
}
catch(PDOException $E)
{
	exit('Failed: (' . $E->getCode() . ')' . $E->getMessage());
}

$conn = null;

echo "Connection is closed." . PHP_EOL;