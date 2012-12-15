<?php
include("MyTXT.php");

// open "testdb.txt", print out its rows, change the delimiter, and save to "newfile.txt"
$mytxt = new MyTXT();
$mytxt->read("testdb.txt");
print_r($mytxt->rows);
$mytxt->delimiter = ",";
$mytxt->save("newfile.txt");
$mytxt->close();

// add a row with given data and save in "morepeeps.txt"
$mytxt = new MyTXT("testdb.txt");
$mytxt->add_row(array("Andross", "Master", "24"));
$mytxt->save("morepeeps.txt");
$mytxt->close();

// delete second row and save to "morepeeps.txt"
$mytxt = new MyTXT("testdb.txt");
$mytxt->remove_row(1);
$mytxt->save("lesspeeps.txt");
$mytxt->close();

// retreive and print the name of the person with the rank of "Leutenant"
$mytxt = new MyTXT("testdb.txt");
$nameOfCaptain = "";
foreach ($mytxt->rows as $row) {
	if ($row['rank'] == "Leutenant") {
		$nameOfCaptain = $row['name'];
	}
}
$mytxt->close();
print($nameOfCaptain);










?>
