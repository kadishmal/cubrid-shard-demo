USE sharddb;

CREATE TABLE tbl_posts(
	post_id INTEGER PRIMARY KEY,
	title VARCHAR(255) NOT NULL,
	content TEXT,
	post_date INTEGER NOT NULL
);
