<?php
include_once('shard_connection.php');

$start_time = microtime(true);

for ($i = 0; $i < $shards_count; ++$i) {
	$sql = "DELETE FROM tbl_posts /*+ shard_id(" . $i . ") */";

	echo "Executing: " . $sql . "\n";

	if (!cubrid_query($sql, $conn)) {
		die("Execute failed: " . cubrid_error_msg());
	}
}

$end_time = microtime(true);

echo $shards_count . " shards were emptied in " . round(($end_time - $start_time) * 1000) . " ms.\n";

cubrid_close();

echo "Connection is closed.\n";
?>