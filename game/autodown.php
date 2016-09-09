<?php
	if (isset($_POST['img'])) {
		$img = $_POST['img'];
		$id = $_POST['id'];
		$type = $_POST['type'];
		$contextOptions = array(
			"ssl" => array(
				"verify_peer"      => false,
				"verify_peer_name" => false,
			),
		);

		if ($type == 'card') {
			$path = 'upload/avatar_'.$id.'.jpg';
		}

		if ($type == 'chieucao') {
			$path = 'upload/cover_'.$id.'.jpg';
		}
		
		$copy = copy($img, $path, stream_context_create($contextOptions));
		$res = array('status' => false);
		if ($copy) {
			$res['img'] = $path;
			$res['status'] = true;
		} 
		echo json_encode($res);
	}
?>