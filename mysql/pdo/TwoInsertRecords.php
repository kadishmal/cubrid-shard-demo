<?php

include_once 'shard_connection.php';

$sql = "INSERT INTO tbl_posts VALUES (/*+ shard_key */ ?, ?, ?, ?)";

try
{
	$stmt = $conn->prepare($sql);
}
catch(PDOException $E)
{
	exit('Prepare failed: (' . $E->getCode() . ')' . $E->getMessage());
}

$start_time = microtime(true);

try
{
	for ($i = 1; $i <= $totalRecordsToInsert; ++$i)
	{
		// The column which represents the `shard_key` by default is `INTEGER`.
		// So, we need to specifically tell the PHP driver that the value to
		// be bound as `INT`, otherwise, by default the driver binds the values
		// as `STRING`. However, if the default sharding strategy on CUBRID
		// SHARD expects an `INTEGER` value, the data will not be sharded correctly.
		// So, make sure the `shard_key` column value type is correctly set
		// during binding process.

		$text_string = "Post :" . $i;

		$stmt->bindParam(1, $i, PDO::PARAM_INT);

		$stmt->bindParam(2, $text_string, PDO::PARAM_STR);

		$stmt->bindParam(3, $text_string, PDO::PARAM_STR);

		$stmt->bindParam(4, mktime(), PDO::PARAM_INT);

		$stmt->execute();
	}
}
catch(PDOException $E)
{
	exit('Execute failed: (' . $E->getCode() . ')' . $E->getMessage());
}

$end_time = microtime(true);

echo "5000 records were inserted in " . round(($end_time - $start_time) * 1000) . " ms." . PHP_EOL;

$conn = null;

echo "Connection is closed." . PHP_EOL;


