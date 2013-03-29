import cubrid.jdbc.driver.CUBRIDConnection;

import java.sql.*;

public class OneSelectAll {
	public static void main(String[] args) throws Exception {
		CUBRIDConnection conn = ShardConnection.getCUBRIDConnection();
		Statement stmt = conn.createStatement();
		ResultSet rs = null;
		String sql;
		int shardsCount = 2;

		try {
			for (int i = 0; i < shardsCount; ++i) {
				sql = "SELECT * FROM tbl_posts /*+ shard_id(" + i + ") */";

				System.out.println("Executing: " + sql);

				rs = stmt.executeQuery(sql);

				PrintResults.print(rs);
			}

			stmt.close();

			conn.close();

			System.out.println("Connection is closed.");
		} catch ( Exception e ) {
			System.err.println("SQLException : " + e.getMessage());
			System.err.println(e);
		} finally {
			if ( conn != null ) conn.close();
		}
	}
}