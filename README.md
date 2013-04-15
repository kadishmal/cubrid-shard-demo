# CUBRID SHARD Demo

This repo provides a set of complete working examples which demonstrate how to configure and run CUBRID and MySQL database sharding with CUBRID SHARD. There are JDBC and Node.js examples in this demo. Feel free to run whichever you are more comfortable with.

To demonstrate sharding we need multiple machines. Both dedicated machines and VMs are fine. To automate the entire installation and configuration, we will rely on Chef and [CUBRID Cookbook](https://github.com/kadishmal/cubrid-cookbook).

At the end all you have to do is fire up one command like `vagrant up`, and you will have everything set up.

The code is licensed under [MIT license](https://github.com/CUBRID/cubrid-shard-demo/blob/master/LICENSE.md), so feel free to fork the repo and do whatever you want.

## Requirements

You need at least two Linux (CentOS 5.6~6.3 or Ubuntu 10.04) machines for this demo to work. You have two options:

1. Make sure you have at least two real servers.
2. Or at least two VM machines.

Most of the times you won't have access to multiple real server, so for simplicity I will go with the second option - multi VM environment.

### VirtualBox

To create multiple VMs, we will use [VirtualBox](https://www.virtualbox.org/), a free and open source virtualization software. You can [download](https://www.virtualbox.org/wiki/Downloads) it for OS X, Windows, Linux, and Solaris hosts.

### Vagrant

Since we want to automate the entire installation and configuration process with Chef, [Vagrant](http://www.vagrantup.com/) is probably the easiest, yet powerful tool to provision VMs. You can [download](http://downloads.vagrantup.com/) it for OS X, Windows, and Linux.

**Note**: in this demo we will use Vagrant 1.1.x. If you have earlier versions, consider upgrading as Vagrantfile created for this demo is not compatible with older versions.

So, these two software are only ones we need to get this demo running.

## Fire up!

Once you fork & clone or download this repository, enter the repository directory:

    cd cubrid-shard-demo

To configure and run MySQL database sharding, change the directory to `mysql` and start up Vagrant.

    cd mysql
    vagrant up

It takes about 10 minutes on my machine for Vagrant to provision MySQL database sharding with CUBRID SHARD on two VMs. By default Vagrant instructs Chef to install CUBRID 9.1. While Vagrant is cooking your VMs, keep on reading.

**Note:** In this demo Vagrant does not install any programming environment nor CUBRID/MySQL drivers on VMs. Only CUBRID SHARD and MySQL are installed along with the preinstalled OS software, which may include some programming environments.

If you would like to install CUBRID drivers and/or programming environments, edit **Vagrantfile** in `mysql` directory and uncomment one/some of the following lines according to your needs.

    #chef.add_recipe "cubrid::pdo_cubrid"
    #chef.add_recipe "cubrid::perl_driver"
    #chef.add_recipe "cubrid::php_driver"
    #chef.add_recipe "cubrid::python_driver"
    
    #chef.add_recipe "node.js"
    #chef.add_recipe "java"

## Prepare shards

Once everything is installed and configured, we need to populate all shards with the same shard schema. We will SSH into the last shard node (**node2**), where CUBRID SHARD has been started, and execute the SQL defined in `schema.sql` on every MySQL instance.

    vagrant ssh node2
    $ mysql -ushard -pshard123 -hnode1 < /vagrant/mysql-schema.sql
    $ mysql -ushard -pshard123 -hnode2 < /vagrant/mysql-schema.sql

## Run Examples

Choose examples from the below list based on your favorite programming language, then run them one by one to see how CUBRID SHARD works in each case.

- [JDBC](#jdbc)
- [Node.js](#nodejs)

### JDBC

#### Set classpath

In order to run JDBC examples, we need to set `CLASSPATH` environment variable which is used by JDK to find libraries and Java files.

On Mac OS X/Linux:

    cd jdbc/src
    export CLASSPATH=../lib/cubrid_jdbc.jar:.

On Windows:

    cd jdbc\src
    set CLASSPATH=..\lib\cubrid_jdbc.jar;.

#### Select all records from all shards

Then try runing the following scripts one by one.

    javac OneSelectAll.java
    java OneSelectAll

On the first run, the above code should output:

    Connected!
	Executing: SELECT * FROM tbl_posts /*+ shard_id(0) */
	Number of columns: 4
	post_id(INTEGER), title(VARCHAR), content(BIT VARYING), post_date(INTEGER), 
	There are 0 rows.
	Executing: SELECT * FROM tbl_posts /*+ shard_id(1) */
	Number of columns: 4
	post_id(INTEGER), title(VARCHAR), content(BIT VARYING), post_date(INTEGER), 
	There are 0 rows.
	Connection is closed.

The above output means there are no records currently in both shards.

#### Insert records

Continue running the following code to populate the shards with 5,000 records.

    javac TwoInsertRecords.java
    java TwoInsertRecords

You will see something like:

    Connected!
    Connection is closed.
    5000 records were inserted in 6241 ms.

> **Note** that you need to run the above *TwoInsertRecords* example **only** when the shards are empty, otherwise you will get an error because of Primary Key violation. There is a PK constraint on `post_id` column. If you need to empty shards, proceed to **Empty Shards** example below.

#### Select Inserted Rows

Since we have inserted relatively small number of records, we are fine with selecting all records from all shards. For this we will execute the very first example again.

    java OneSelectAll

Output:

	Connected!
	Executing: SELECT * FROM tbl_posts /*+ shard_id(0) */
	Number of columns: 4
	post_id(INTEGER), title(VARCHAR), content(BIT VARYING), post_date(INTEGER)
	1  Post 1  Post 1 content  1366008762  
	2  Post 2  Post 2 content  1366008762  
	3  Post 3  Post 3 content  1366008762  
	4  Post 4  Post 4 content  1366008762  
	5  Post 5  Post 5 content  1366008762  
	...
	There are 2541 rows.
	Executing: SELECT * FROM tbl_posts /*+ shard_id(1) */
	Number of columns: 4
	post_id(INTEGER), title(VARCHAR), content(BIT VARYING), post_date(INTEGER)
	65  Post 65  Post 65 content  1366008763  
	66  Post 66  Post 66 content  1366008763  
	67  Post 67  Post 67 content  1366008763  
	68  Post 68  Post 68 content  1366008763  
	69  Post 69  Post 69 content  1366008763  
	...
	There are 2459 rows.
	Connection is closed.

We can notice that the data has been evenly distributed across two shards.

#### Empty Shards

To delete all records from all shards, run the following example.

    cd jdbc\src
    javac ThreeEmptyShards.java
    java ThreeEmptyShards

You will see something like:

    Connected!
    Connection is closed.
    2 shards were emptied in 11 ms.

### Node.js

#### Install dependencies

This assumes you have Node.js installed on your local machine. We need to first install Node.js package dependencies.

    cd nodejs
    npm install

#### Select all records from all shards

    node OneSelectAll

Output indicates that we have no records at this point in any shard.

	Connected
	Executing: SELECT * FROM tbl_posts /*+ shard_id(0) */
	Number of columns: 4
	post_id(Int), title(String), content(Varbit), post_date(Int)
	Shard(0) holds 0 records
	Executing: SELECT * FROM tbl_posts /*+ shard_id(1) */
	Number of columns: 4
	post_id(Int), title(String), content(Varbit), post_date(Int)
	Shard(1) holds 0 records
	Connection closed.

#### Insert records

Continue running the following code to populate the shards with 5,000 records.

    node TwoInsertRecords

You will see something like:

	Connected
	5000 records were inserted in 37178 ms.
	Connection closed.

> **Note** that you need to run the above *TwoInsertRecords* example **only** when the shards are empty, otherwise you will get an error because of Primary Key violation. There is a PK constraint on `post_id` column. If you need to empty shards, proceed to **Empty Shards** example below.

#### Select Inserted Rows

Since we have inserted relatively small number of records, we are fine with selecting all records from all shards. For this we will execute the very first example again.

	node OneSelectAll

Output:

	Connected
	Executing: SELECT * FROM tbl_posts /*+ shard_id(0) */
	Number of columns: 4
	post_id(Int), title(String), content(Varbit), post_date(Int)
	1 Post 1 Post 1 content 1366008762 
	2 Post 2 Post 2 content 1366008762 
	3 Post 3 Post 3 content 1366008762 
	4 Post 4 Post 4 content 1366008762 
	5 Post 5 Post 5 content 1366008762 
	...
	Shard(0) holds 2541 records
	Executing: SELECT * FROM tbl_posts /*+ shard_id(1) */
	Number of columns: 4
	post_id(Int), title(String), content(Varbit), post_date(Int)
	65 Post 65 Post 65 content 1366008763 
	66 Post 66 Post 66 content 1366008763 
	67 Post 67 Post 67 content 1366008763 
	68 Post 68 Post 68 content 1366008763 
	69 Post 69 Post 69 content 1366008763 
	...
	Shard(1) holds 2459 records
	Connection closed.

We can notice that the data has been evenly distributed across two shards.

#### Empty Shards

To delete all records from all shards, run the following example.

	node ThreeEmptyShards

You will see something like:

	Connected
	Executing: DELETE FROM tbl_posts /*+ shard_id(0) */
	Executing: DELETE FROM tbl_posts /*+ shard_id(1) */
	2 shards were emptied in 1007 ms.
	Connection closed.



## What's next

At this moment I have plans to add more examples in other programming languages like PHP, Python, etc.

### Contribute

If you want to contribute, fork this repo and send a pull request with your examples in your favorite language.

## License

Distributed under  ([MIT license](https://github.com/CUBRID/cubrid-shard-demo/blob/master/LICENSE.md)).
 
Copyright (c) 2013 Esen Sagynov <kadishmal@gmail.com>