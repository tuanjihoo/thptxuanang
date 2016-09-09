<?php
	if (isset($_POST['img'])) {
		$img = str_replace('http://caffe-blog.cf/game', '', $_POST['img']);
		unlink('/home/u107045817/public_html/game/' . $img);
		if (isset($_POST['cover']))  {
			$cover = str_replace('http://caffe-blog.cf/game', '', $_POST['cover']);
			unlink('/home/u107045817/public_html/game/' . $cover);
		}
	}

	$res = array('status' => true);
	echo json_encode($res);
?>