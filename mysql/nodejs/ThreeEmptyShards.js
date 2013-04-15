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

var shards = [0, 1];

client.connect(function (err) {
	if (err) {
		console.log(err);
	}
	else{
		console.log('Connected');

		client.setEnforceOldQueryProtocol(true);

		var startTime = new Date().getTime(),
			endTime;

		async.eachSeries(shards, emptyShard, function (err) {
			if (err) {
				console.log(err);
			}
			else{
				endTime = new Date().getTime();
				console.log(shards.length + ' shards were emptied in ' + (endTime - startTime) + ' ms.');
			}

			client.close();
		});
	}
});

client.on(client.EVENT_CONNECTION_CLOSED, function () {
  console.log('Connection closed.');
});

function emptyShard(shardId, done) {
	var sql = 'DELETE FROM tbl_posts /*+ shard_id(' + shardId + ') */';

	console.log('Executing: ' + sql);

	client.addNonQuery(sql, function (err) {
		if (err) {
			done(err);
		}
		else{
			done();
		}
	});
}