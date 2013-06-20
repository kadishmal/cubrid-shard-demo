<?php
include_once('shard_connection.php');

for ($i = 0; $i < $shards_count; ++$i) {
	$sql = "SELECT * FROM tbl_posts /*+ shard_id(" . $i . ") */";

	echo "Executing: " . $sql . "\n";

	if (!($req = cubrid_query($sql, $conn))) {
		die("Query failed: " . cubrid_error_msg());
	}

	$cols = cubrid_num_cols($req);
	$rows = cubrid_num_rows($req);
	$column_names = cubrid_column_names($req);
	$column_types = cubrid_column_types($req);
	$columns = array();

	echo "Number of columns: " . $cols . "\n";

	for($j = 0; $j < $cols; ++$j) {
		array_push($columns, $column_names[$j] . "(" . $column_types[$j] . ")");
	}

	echo join(", ", $columns) . "\n";

	while ($row = cubrid_fetch($req)) {
		for($j = 0; $j < $cols; ++$j) {
			// `varbit` represents `TEXT` data type in MySQL.
			// The raw value of `varbit` column comes in bytes. In PHP, it's
			// bytes string (HEX), so we need to convert them to UTF8.
			if ($column_types[$j] == 'varbit') {
				// Since PHP 5.4 you can use [hex2bin()](http://php.net/manual/kr/function.hex2bin.php)
				// function. For lower versions, the following is a solution.
				echo pack('H*', $row[$j]);
			} else {
				echo $row[$j];
			}

			echo " ";
		}

		echo "\n";
	}

	echo "Shard(" . $i . ") holds " . $rows . " records.\n";

	cubrid_close_request($req);
}

cubrid_close();

echo "Connection is closed.\n";
?>