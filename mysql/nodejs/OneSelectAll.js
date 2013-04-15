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

		async.eachSeries(shards, selectAll, function (err) {
			if (err) {
				console.log(err);
			}

			client.close();
		});
	}
});

client.on(client.EVENT_CONNECTION_CLOSED, function () {
  console.log('Connection closed.');
});

function printResults(err, result, queryHandle, callback) {
	if (err || !result || !result.ColumnValues.length) {
		callback();
	}
	else{
		var columnNames = result.ColumnNames,
			columnValues = result.ColumnValues,
			len = columnValues.length;

		if (len) {
			for (var i = 0; i < len; ++i) {
				var row = '';

				for (var j = 0, jLen = columnNames.length; j < jLen; ++j) {
					// If this is the TEXT column of MySQL shard, the value is a buffer.
					if (j == 2) {
						var buf = new Buffer(columnValues[i][j]);
						row += buf.toString() + ' ';
					}
					else{
						row += columnValues[i][j] + ' ';
					}
				}

				console.log(row);
			}

			client.fetch(queryHandle, function (err, result, queryHandle) {
				printResults(err, JSON.parse(result), queryHandle, callback);
			});
		}
		else{
			callback();
		}
	}
}

function selectAll(shardId, done) {
	var sql = 'SELECT * FROM tbl_posts /*+ shard_id(' + shardId + ') */';

	console.log('Executing: ' + sql);

	client.addQuery(sql, function (err, result, queryHandle) {
		if (err) {
			done(err);
		}
		else{
			result = JSON.parse(result);

			var rowCount = result.RowsCount,
				columnNames = result.ColumnNames,
				columnTypes = result.ColumnDataTypes,
				colNames = [];

			console.log('Number of columns: ' + columnNames.length);

			for (var i = 0, len = columnNames.length; i < len; ++i) {
				colNames.push(columnNames[i] + '(' + columnTypes[i] + ')');
			}

			console.log(colNames.join(', '));

			printResults(err, result, queryHandle, function (err) {
				if (err) {
					done(err);
				}
				else{
					client.closeQuery(queryHandle, function (err) {
						if (err) {
							done(err);
						}
						else{
							console.log('Shard(' + shardId + ') holds ' + rowCount + ' records');

							done();
						}
					});
				}
			});
		}
	});
}