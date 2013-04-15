var CUBRID = require('node-cubrid'),
  async = require('async'),
  Result2Array = CUBRID.Result2Array,
  ActionQueue = CUBRID.ActionQueue,
  dbConf = {
    host: '10.11.12.14',
    port: 45011,
    user: 'shard',
    password: 'shard123',
    database: 'sharddb'
  },
  client = CUBRID.createCUBRIDConnection(dbConf.host, dbConf.port, dbConf.user, dbConf.password, dbConf.database);

client.connect(function (err) {
	if (err) {
		console.log(err);
	}
	else{
		console.log('Connected');

		client.setEnforceOldQueryProtocol(true);
		client._QUERIES_QUEUE_CHECK_INTERVAL = 1;

		var startTime = new Date().getTime(),
			totalRecordsToInsert = 5000,
			// post_id, title, content, post_date
			sql = "INSERT INTO tbl_posts VALUES (/*+ shard_key */ ?, ?, ?, ?)",
			insertedCount = 0;

		for (var i = 1; i <= totalRecordsToInsert; ++i) {
			var insertSQL = CUBRID.Helpers._sqlFormat(sql,
				[i,  'Post ' + i, 'Post ' + i + ' content', new Date().getTime()/1000],
				["", "'", "'", ""]);

			client.addNonQuery(insertSQL, function (err) {
				if (err) {
					console.log(insertSQL);
					console.log(err);
				}

				if (++insertedCount == totalRecordsToInsert) {
					console.log(totalRecordsToInsert + " records were inserted in " + (new Date().getTime() - startTime) + " ms.");
					client.close();
				}
			});
		}
	}
});

client.on(client.EVENT_CONNECTION_CLOSED, function () {
  console.log('Connection closed.');
});
