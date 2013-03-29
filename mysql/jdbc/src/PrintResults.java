import java.sql.ResultSet;
import java.sql.ResultSetMetaData;

/**
 * Created with IntelliJ IDEA.
 * User: nhn
 * Date: 3/29/13
 * Time: 3:50 PM
 * To change this template use File | Settings | File Templates.
 */
public class PrintResults {
	public static void print(ResultSet rs) {
		try {
			ResultSetMetaData rsmd = rs.getMetaData();
			int numberofColumns = rsmd.getColumnCount(),
				rowsCount = 0;

			System.out.println("Number of columns: " + numberofColumns);

			for (int j = 1; j <= numberofColumns; ++j ) {
				System.out.print(rsmd.getColumnName(j) + "(" + rsmd.getColumnTypeName(j) + "), ");
			}

			System.out.println("");

			while (rs.next ()) {
				++rowsCount;

				for(int j = 1; j <= numberofColumns; ++j ) {
					// If this is TEXT column.
					if (rsmd.getColumnType(j) == -3) {
						byte[] byteContent = rs.getBytes(j);
						System.out.print(new String(byteContent, "UTF-8") + "  ");
					}
					else{
						System.out.print(rs.getString(j) + "  ");
					}
				}

				System.out.println("");
			}

			System.out.println("There are " + rowsCount + " rows.");

		} catch ( Exception e ) {
			System.err.println("SQLException : " + e.getMessage());
		}
	}
}
