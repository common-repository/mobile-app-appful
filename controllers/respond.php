<?php

/*
  Controller name: Kommentare
  Controller description: Dieses Modul wird für das Senden von Kommentaren aus der App benötigt.
 */

class Appful_API_Respond_Controller {

	function submit_comment() {
		global $appful_api;
		nocache_headers();

		if (empty($_REQUEST['post_id'])) {
			$appful_api->error("No post specified. Include 'post_id' var in your request.");
			die();
		} else if (empty($_REQUEST['name']) || empty($_REQUEST['email']) || empty($_REQUEST['content'])) {
				$appful_api->error("Please include all required arguments (name, email, content).");
				die();
			} else if (!is_email($_REQUEST['email'])) {
				$appful_api->error("Please enter a valid email address.");
				die();
			}

		$pending = new Appful_API_Comment();

		$submit = $pending->handle_submission();
		return $submit;
	}
}


?>
