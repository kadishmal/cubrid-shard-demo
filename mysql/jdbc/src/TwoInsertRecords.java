import cubrid.jdbc.driver.CUBRIDConnection;
import cubrid.jdbc.driver.CUBRIDPreparedStatement;

import java.util.Date;

public class TwoInsertRecords {
	public static void main(String[] args) throws Exception {
		CUBRIDConnection conn = ShardConnection.getCUBRIDConnection();
		int totalRecordsToInsert = 5000;

		try {
			// post_id, title, content, post_date
			String sql = "INSERT INTO tbl_posts VALUES (/*+ shard_key */ ?, ?, ?, ?)";

			CUBRIDPreparedStatement preStmt = (CUBRIDPreparedStatement)conn.prepareStatement(sql);

			int startTime = (int)(new Date().getTime());

			for (int i = 1; i <= totalRecordsToInsert; ++i) {
				preStmt.setInt(1, i);
				preStmt.setString(2, "Post " + i);
				preStmt.setString(3, "Post " + i + " content");

				preStmt.setInt(4, (int) (new Date().getTime()/1000));

				preStmt.executeInsert();
			}

			int endTime = (int)(new Date().getTime());

			preStmt.close();
			conn.close();

			System.out.println("Connection is closed.");
			System.out.println(totalRecordsToInsert + " records were inserted in " + (endTime - startTime) + " ms.");
		} catch ( Exception e ) {
			System.err.println("SQLException : " + e.getMessage());
			System.err.println(e);
		} finally {
			if ( conn != null ) conn.close();
		}
	}
}