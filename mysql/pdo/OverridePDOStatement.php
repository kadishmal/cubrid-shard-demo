<?php

include_once 'shard_connection.php';

class ExampleStatementClass extends PDOStatement
{
	public function fetchAll()
	{
		$cols = $this->columnCount();

		$varbit_columns = array();

		for($j = 0; $j < $cols; ++$j)
		{
			$column_meta = $this->getColumnMeta($j);

			if (strstr($column_meta['type'], 'varbit'))
			{
				$varbit_columns[] = $column_meta['name'];
			}
		}

		$result  = array();
		$records = parent::fetchAll();

		foreach ($records as $rec)
		{
			foreach ($varbit_columns as $column)
			{
				$rec[$column] = pack('H*', $rec[$column]);
			}

			$result[] = $rec;
		}

		return $result;
	}
}


try
{
	$conn->setAttribute(PDO::ATTR_STATEMENT_CLASS, array("ExampleStatementClass"));

	for ($i = 0; $i < $shards_count; ++$i)
	{
		$sql = "SELECT * FROM tbl_posts /*+ shard_id(" . $i . ") */";

		echo "Executing: " . $sql . PHP_EOL;

		$req  = $conn->query($sql);

		foreach ($req->fetchAll() as $row)
		{
			var_dump($row);
		}
	}
}
catch(PDOException $E)
{
	exit('Failed: (' . $E->getCode() . ')' . $E->getMessage());
}
