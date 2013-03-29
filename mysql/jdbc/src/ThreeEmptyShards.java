import cubrid.jdbc.driver.CUBRIDConnection;
import cubrid.jdbc.driver.CUBRIDPreparedStatement;

import java.sql.Statement;
import java.util.Date;

/**
 * Created with IntelliJ IDEA.
 * User: nhn
 * Date: 3/29/13
 * Time: 4:34 PM
 * To change this template use File | Settings | File Templates.
 */
public class ThreeEmptyShards {
	public static void main(String[] args) throws Exception {
		CUBRIDConnection conn = ShardConnection.getCUBRIDConnection();
		Statement stmt = conn.createStatement();
		String sql;
		int shardsCount = 2;

		try {
			int startTime = (int)(new Date().getTime());

			for (int i = 0; i < shardsCount; ++i) {
				sql = "DELETE FROM tbl_posts /*+ shard_id(" + i + ") */";

				stmt.executeUpdate(sql);
			}

			int endTime = (int)(new Date().getTime());

			stmt.close();
			conn.close();

			System.out.println("Connection is closed.");
			System.out.println(shardsCount + " shards were emptied in " + (endTime - startTime) + " ms.");
		} catch ( Exception e ) {
			System.err.println("SQLException : " + e.getMessage());
			System.err.println(e);
		} finally {
			if ( conn != null ) conn.close();
		}
	}
}
