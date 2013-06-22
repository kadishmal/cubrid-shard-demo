<?php

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