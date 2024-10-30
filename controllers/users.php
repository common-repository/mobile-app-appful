<?php

/*
  Controller name: Benutzer
  Controller description: Dieses Modul wird zum erstellen von Benutzerkonten benötigt.
 */


class Appful_API_Users_Controller {

	function create_user() {
		global $appful_api;
		nocache_headers();

		if (isset($_REQUEST["email"]) && (isset($_REQUEST["username"]) || (isset($_REQUEST["first_name"]) && isset($_REQUEST["last_name"])))) {
			if (email_exists($_REQUEST["email"]) == false) {
				$username = isset($_REQUEST["username"]) ? $_REQUEST["username"] : $_REQUEST["first_name"] . "." . $_REQUEST["last_name"];
				$startUsername = $username;
				$i = 1;
				while (username_exists($username)) {
					$username = $startUsername . "." . $i++;
				}

				$random_password = wp_generate_password(8, false);
				$user = wp_create_user($username, $random_password, $_REQUEST["email"]);

				if (!is_wp_error($user)) {
					if (isset($_REQUEST["first_name"])) update_user_meta($user, 'first_name', $_REQUEST["first_name"]);
					if (isset($_REQUEST["last_name"])) update_user_meta($user, 'last_name', $_REQUEST["last_name"]);
					if (isset($_REQUEST["url"])) update_user_meta($user, 'user_url', $_REQUEST["url"]);

					if (isset($_REQUEST["avatar_url"])) {
						include_once ABSPATH . 'wp-admin/includes/plugin.php';
						if (is_plugin_active('wp-user-avatar/wp-user-avatar.php') || is_plugin_active_for_network('wp-user-avatar/wp-user-avatar.php')) {
							$url = $_REQUEST["avatar_url"];
							$tmp = download_url($url);
							if (!is_wp_error($tmp)) {
								$file_array = array();

								preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches);
								$file_array['name'] = "user_". $user . "_" . time() . "." . $matches[1];
								$file_array['tmp_name'] = $tmp;

								if (is_wp_error($tmp)) {
									@unlink($file_array['tmp_name']);
									$file_array['tmp_name'] = '';
								}

								$attach_id = media_handle_sideload($file_array, 0);
								if ( is_wp_error($attach_id) ) {
									@unlink($file_array['tmp_name']);
								} else {
									global $wpdb;
									update_user_meta($user, $wpdb->get_blog_prefix() . 'user_avatar', $attach_id);
								}
							}
						}
					}

					$appful_api->response->respond(array("payload" => $user));
					die();
				} else {
					$appful_api->error($user->get_error_message());
					die();
				}
			} else {
				$appful_api->response->respond(array("error" => "already_registered"));
				die();
			}
		} else {
			$appful_api->error("Please include all required arguments (username, email).");
			die();
		}
	}


}


?>
