<?php

return [
	'client_id' 		=> env('GOOGLEDRIVE_CLIENT_ID', ''),
	'client_secret' 	=> env('GOOGLEDRIVE_CLIENT_SECRET', ''),
	'access_type' 		=> env('GOOGLEDRIVE_ACCESS_TYPE', 'offline'),
	'callback' 			=> env('GOOGLEDRIVE_CALLBACK', ''),
];