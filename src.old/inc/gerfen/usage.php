<?php
include_once('class.stream.php');

$data = array();

new stream($data);

$_PUT = $data['post'];
$_FILES = $data['file'];

/* Handle moving the file(s) */
if (count($_FILES) > 0) {
    foreach($_FILES as $key => $value) {
        if (!is_uploaded_file($value['tmp_name'])) {
            rename($value['tmp_name'], '/path/to/uploads/'.$value['name']);
        } else {
            move_uploaded_file($value['tmp_name'], '/path/to/uploads/'.$value['name']);
        }
    }
}
