<?php
include_once('shard_connection.php');

$sql = "INSERT INTO tbl_posts VALUES (/*+ shard_key */ ?, ?, ?, ?)";

if (!($stmt = cubrid_prepare($conn, $sql))) {
	die("Prepare failed: " . cubrid_error_msg());
}

$start_time = microtime(true);

for ($i = 1; $i <= $totalRecordsToInsert; ++$i) {
	// The column which represents the `shard_key` by default is `INTEGER`.
	// So, we need to specifically tell the PHP driver that the value to
	// be bound as `INT`, otherwise, by default the driver binds the values
	// as `STRING`. However, if the default sharding strategy on CUBRID
	// SHARD expects an `INTEGER` value, the data will not be sharded correctly.
	// So, make sure the `shard_key` column value type is correctly set
	// during binding process.
	if (!cubrid_bind($stmt, 1, $i, 'INT')) {
		die("Binding parameters failed: " . cubrid_error_msg());
	}

	if (!cubrid_bind($stmt, 2, "Post " . $i)) {
		die("Binding parameters failed: " . cubrid_error_msg());
	}

	if (!cubrid_bind($stmt, 3, "Post " . $i . " content")) {
		die("Binding parameters failed: " . cubrid_error_msg());
	}

	if (!cubrid_bind($stmt, 4, mktime(), 'INT')) {
		die("Binding parameters failed: " . cubrid_error_msg());
	}

	if (!cubrid_execute($stmt)) {
		die("Execute failed: " . cubrid_error_msg());
	}
}

$end_time = microtime(true);

echo "5000 records were inserted in " . round(($end_time - $start_time) * 1000) . " ms.\n";

cubrid_close();

echo "Connection is closed.\n";
?>