<?php

class MyTXT {
	public $file;
	public $delimiter;
	public $rows;
	function MyTXT($filepath="__default__", $text_delimiter=":|:") {
		// read file into memory if needed

		$this->delimiter = $text_delimiter;
		if ($filepath == "__default__") {
			$this->file = ".";
			$this->rows = ".";
		}
		else {
			$this->file = $filepath;
			if (file_exists($filepath)) {
				$this->read();
			}
			else {
				$this->rows = ".";
				if ($fh = fopen($filepath, 'w')) {
					fwrite($fh, ".");
					fclose($fh);
				}
				else {
					echo("Text database not found. Could not create new file.");
				}
			}
		}
	}
	function read($readfile="__default__") {
		// read a new text database into memory
		
		if ($readfile == "__default__") {
			$readfile = $this->file;
		}

		$this->file = $readfile;
		$fh = fopen($readfile, 'r');
		clearstatcache();
		$textdata = fread($fh, filesize($readfile));
		fclose($fh);
		$textdata = str_replace("\r", "\n", $textdata);
		$textdata = str_replace("\n\n", "\n", $textdata);
		$rowdata = array();
		$textdata = explode("\n", $textdata);
		$textdata = array_reverse($textdata);
		$indices = explode($this->delimiter, array_pop($textdata));
		$textdata = array_reverse($textdata);
		$count = 0;
		foreach ($textdata as $value) {
			if ($value != "") {
				$rowdata[$count] = explode($this->delimiter, $value);
				foreach ($rowdata[$count] as $oldkey=>$propertyvalue) {
					$rowdata[$count][$indices[$oldkey]] = $propertyvalue;
					unset($rowdata[$count][$oldkey]);
				}
				$count += 1;
			}
		}
		// will load memory with contents of text file table
		$this->rows = $rowdata;
		return True;
	}
	function save($savefile="__default__") {
		// will save a 2-dimensional array of data to a text file

		// check if database exists in memory
		if ($this->rows == ".") {
			echo("Could not write file: contains no data.");
			return False;
		}
		
		// check if user specified a new savefile
		if ($savefile == "__default__") {
			$savefile = $this->file;
		}
		
		// parse array into a string for storage
		$output = implode($this->delimiter, $this->columns());
		foreach ($this->rows as $row) {
			$output .= "\n" . implode($this->delimiter, $row);
		}
		
		// actually save data to file
		if (!$fh = fopen($savefile, 'w')) {
			return False;
		}
		$textdata = fwrite($fh, $output);
		fclose($fh);
		clearstatcache();
		return True;
	}
	function columns() {
		// will return array of column names for table

		$columns = array();
		foreach ($this->rows[0] as $key => $value) {
			array_push($columns, $key);
		}
		return $columns;
	}
	function display() {
		// will print a table showing the contents of the data
		
		$output = "<table><tbody><tr>";
		foreach ($this->columns() as $column) {
			$output .= "<td><b>" . $column . "</b></td>";
		}
		$output .= "</tr>";
		foreach ($this->rows as $row) {
			$output .= "<tr><td>" . implode("</td><td>", $row) . "</td></tr>";
		}
		$output .= "</tbody></table>";
		return $output;
	}
	function add_row($rowdata) {

		if (count($rowdata) != count($this->columns())) {
			die("Row Add Error: new row size must be same as column number.");
			return False;
		}
		$columns = $this->columns();
		foreach ($rowdata as $key=>$row) {
			$rowdata[$columns[$key]] = $row;
			unset($rowdata[$key]);
		}
		$this->rows[count($this->rows)] = $rowdata;
		return True;
	}
	function remove_row($row) {
		if ($row >= count($this->rows)) {
			die("Row Delete Error: row does not exist.");
			return False;
		}
		unset($this->rows[$row]);
		return True;
	}
	function close() {
		// free up memory from all stored information
		$this->rows = ".";
		$this->delimiter = "";
		$this->file = ".";
	}
}
?>
