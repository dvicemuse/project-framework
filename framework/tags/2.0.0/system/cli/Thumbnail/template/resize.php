<?php

	$image_location = '|IMAGE_LOCATION|';
	$image_cache_location = '|IMAGE_CACHE_LOCATION|';

	try
	{
		// Keep PHP happy
		date_default_timezone_set('America/Los_Angeles');
		
		// File type
		$file_type = str_replace('jpeg', 'jpg', strtolower(pathinfo($_GET['file'], PATHINFO_EXTENSION)));

		// Basic headers (needed for cache and new image)
		header("Content-Type: image/{$file_type}");
		header("Cache-Control: private, max-age=10800, pre-check=10800");
		header("Pragma: private");
		header("Expires: " . date(DATE_RFC822, strtotime("1 week")));

		// Check for cache header
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			// Images are never updated, so say to use cached version
			header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'], true, 304);
			exit;
		}else{
			// Start framework
			include_once('system/Framework.php');
			$frm = new Framework;

			// Load thumbnail class
			$frm->load_helper('Thumbnail');

			// File cache hash
			$file_hash = md5($_SERVER['REQUEST_URI']);
			
			// Path to save the image to
			$cache_save_path = $image_cache_location . substr($file_hash, 0, 2);
			$cache_image_save_path = "{$cache_save_path}/{$file_hash}.{$file_type}";

			// Check if image is already cached
			if(is_file($cache_image_save_path))
			{
				// Set last modified header
				header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($cache_image_save_path)).' GMT', true, 200);
				
				// Send image data
				$fp = fopen($cache_image_save_path, 'rb');
				die(fpassthru($fp));
			}

			// Image location
			$image_location = $image_location . str_replace("/", '', $_GET['file']);

			// Load image to resize
			$thumb = Thumbnail::create($image_location);

			// Return correct resize type
			if($_GET['type'] == 'adaptive')
			{
				// Force width/height
				$thumb->adaptiveResize($_GET['width'], $_GET['height']);
			}else{
				// Resize within width/height
				$thumb->resize($_GET['width'], $_GET['height']);
			}
			
			// Check if cache folder exists, create if necessary
			if(!is_dir($cache_save_path))
			{
				// Make directory
				@mkdir($cache_save_path); // mkdir() does not always return what you think it should

				// Verify folder creation
				if(!is_dir($cache_save_path))
				{
					die('Unable to create cache directory.');
				}
			}

			// Save image
			$thumb->save($cache_image_save_path, $file_type);
			
			// Set last modified header
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($cache_image_save_path)).' GMT', true, 200);
			
			// Show image
			$thumb->show($file_type);
			exit;
		}
		
	}catch(Exception $e){
		// Silent
	}

?>