<?php
// Xu ly form
if (isset($_POST['img'])) {
	$img = $_POST['img'];
	$id = $_POST['id'];
	$type = $_POST['type'];
	//Get the base-64 string from data
	$filteredData = substr($img, strpos($img, ",")+1);
	//Decode the string
	$unencodedData = base64_decode($filteredData);
	//Save the image
	if ($type =='card') {
		$url = 'upload/export_image_card_' . $id . '.jpg';
	}
	if ($type == 'chieucao') {
		$url = 'upload/export_image_' . $id . '.jpg';
	}
	file_put_contents($url, $unencodedData);

	echo json_encode($url);
}