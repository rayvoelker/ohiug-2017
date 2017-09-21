<?php

//reset all variables needed for our connection
$username = null;
$password = null;
$dsn = null;
// $dsn looks like this in the config: 
// $dsn = "pgsql:"
//	. "host=sierra-db.library.org;"
//	. "dbname=iii;"
//	. "port=1032;"
//	. "sslmode=require;";

// config file supplies the variables above
require_once('config.php');

//make our database connection
try {
	$connection = new PDO($dsn, $username, $password);
}

catch ( PDOException $e ) {
	$row = null;
	$statement = null;
	$connection = null;

	echo "problem connecting to database...\n";
	error_log('PDO Exception: '.$e->getMessage());
	exit(1);
}

// set output to utf-8
$connection->query('SET NAMES UNICODE');

// create our query using the heredoc (nowdoc) string for the prepare statement
// http://php.net/manual/en/language.types.string.php#language.types.string.syntax.nowdoc 
$sql = <<<SQLEND
SELECT
r.record_type_code || r.record_num || 'a' as record_num

FROM
sierra_view.record_metadata as r

WHERE
r.record_type_code || campus_code = ?

ORDER BY
r.id DESC

LIMIT
10;

SQLEND;

try{
	// prepare the query
	$statement = $connection->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	
	// execute the query with the variable set as desired
	// remember to sanitize input if coming from the user!
	$record_type_code = 'p';
	$statement->execute( array($record_type_code) );
	
	// in our example, we can output csv data
	// open stream to standard output
	$out = fopen('temp.csv', 'w');
	
	while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, $row);
    }
	
	// close our output
	fclose($out);
}
catch (Exception $e) {
	//~ TODO: catch some errors
}

// close our connection
$row = null;
$statement = null;
$connection = null;
$username = null;
$password = null;
$dsn = null;
?>
