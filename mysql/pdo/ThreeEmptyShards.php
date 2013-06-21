<?php

include_once 'shard_connection.php';

$start_time = microtime(true);

try
{
	for ($i = 0; $i < $shards_count; ++$i)
	{
		$sql = "DELETE FROM tbl_posts /*+ shard_id(" . $i . ") */";

		echo "Executing: " . $sql . "\n";

		$conn->query($sql);
	}
}
catch(PDOException $E)
{
	exit('Execute failed: (' . $E->getCode() . ')' . $E->getMessage());
}

$end_time = microtime(true);

echo $shards_count . " shards were emptied in " . round(($end_time - $start_time) * 1000) . " ms.\n";

$conn = null;

echo "Connection is closed.\n";
