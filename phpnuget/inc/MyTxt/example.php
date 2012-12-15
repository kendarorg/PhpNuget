<?php
// must include the file to use the class
include('MyTXT.php');

// Instantiate the class
// Must supply a db file to constructor
// If database does not exist, it will try to create it
// Automatically reads and parses data into "rows" property
$mytxt = new MyTXT("testdb.txt");

echo("Edit/view memory table copied from file:<br/>");
// Access/edit memory table with "rows" property
var_dump($mytxt->rows);

echo("<br/><hr/>Display memory table in a formatted html table:<br/>");
// Display data in a formatted table with "display()" method
print($mytxt->display());

// Change delimiter characters to avoid conflicts
// By default uses ":|:" to avoid conflicts with any characters
// You can change to anything you want. Bigger the delimiter, more space it takes up
$mytxt->delimiter = ":|:";

echo("<br/><hr/>Get array of column names:<br/>");
// Get array of column names
var_dump($mytxt->columns());

// save array of data in $mytxt->rows into text file "newdb.txt"
$mytxt->save("newdb.txt");

// read database into memory structure for database
$mytxt->read("newdb.txt");

?>
