import cubrid.jdbc.driver.CUBRIDConnection;

import java.sql.*;

public class ShardConnection {
	static  {
		try {
			Class.forName("cubrid.jdbc.driver.CUBRIDDriver");
		} catch (ClassNotFoundException e) {
			throw new RuntimeException(e);
		}
	}

	public static CUBRIDConnection getCUBRIDConnection() {
		Connection conn = null;

		try {
			conn = DriverManager.getConnection("jdbc:cubrid:10.11.12.14:45011:sharddb:::?charSet=utf8", "shard", "shard123");
			System.out.println("Connected!");
		} catch ( Exception e ) {
			System.err.println("Error: " + e.getMessage());
		}

		return (CUBRIDConnection)conn;
	}
}