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

		var startTime = new Date().getTime(),
			totalRecordsToInsert = 5000,
			insertedCount = 0;

		function insert () {
			++insertedCount;
			// post_id, title, content, post_date
			var insertSQL = "INSERT INTO tbl_posts VALUES (/*+ shard_key */ " +
				insertedCount +
				", 'Post " + insertedCount + "'" +
				", 'Post " + insertedCount + " content'" +
				", " + new Date().getTime()/1000 +
				")";

			client.execute(insertSQL, function (err) {
				if (err) {
					console.log(insertSQL);
					console.log(err);
					client.close();
				}
				else{
					if (insertedCount == totalRecordsToInsert) {
						console.log(totalRecordsToInsert + " records were inserted in " + (new Date().getTime() - startTime) + " ms.");
						client.close();
					} else {
						insert();
					}
				}
			});
		}

		insert();
	}
});

client.on(client.EVENT_CONNECTION_CLOSED, function () {
  console.log('Connection closed.');
});
