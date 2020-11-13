<?php
	header( 'Content-type: text/html; charset=utf-8' );
	set_time_limit(0);
	
	// DB credentials
	$user = 'DB_USER';
	$pass = 'DB_PASS';
	$host = 'DB_HOST';
	$dbna = 'DB_NAME';
	
	$dbh = new PDO('mysql:host='.$host.';dbname='.$dbna, $user, $pass);
	
	$redditUrl = 'https://www.reddit.com/r/';
	$multireddit = $redditUrl;
	$subreddits = file('subreddits.txt');
	
	$users          = [];
	$SFW_subreddit  = [];
	$NSFW_subreddit = [];
	
	$i = 1;
	echo "<table>";
	echo "<tr>
			<th>#</th>
			<th>Raw title</th>
			<th>Title</th>
			<th>NSFW</th>
			<th>URL</th>
		</tr>";
	foreach($subreddits as $rawTitle) {
		$rawTitle = trim($rawTitle);

		$apiURL = 'https://www.reddit.com/r/' . $rawTitle . '/about.json';
		$apiRawResult = file_get_contents($apiURL);
		
		try {
			
			$apiResult = json_decode($apiRawResult);
			
			$title = $apiResult->data->title;
			$nsfw  = empty($apiResult->data->over18)?'0':'1';
			$url   = $apiResult->data->url;
			
			if (strpos($url, '/user/')>0) {
				//$users[] = $rawTitle;
			}
			else {
				echo "<tr>";
				echo "<td>$i</td>";
				echo "<td>$rawTitle</td>";
				echo "<td>$title</td>";
				echo "<td>$nsfw</td>";
				echo "<td>$url</td>";
				echo "</tr>";
				
				$stmt = $dbh->prepare("
					INSERT INTO subreddit (sr_rawtitle, sr_title, sr_nsfw, sr_url)
					VALUES (:sr_rawtitle, :sr_title, :sr_nsfw, :sr_url)
					ON DUPLICATE KEY UPDATE
						sr_rawtitle = :sr_rawtitle,
						sr_title = :sr_title,
						sr_nsfw = :sr_nsfw,
						sr_url = :sr_url
				");
				$stmt->bindParam(':sr_rawtitle', $rawTitle);
				$stmt->bindParam(':sr_title', $title);
				$stmt->bindParam(':sr_nsfw', $nsfw);
				$stmt->bindParam(':sr_url', $url);
				$stmt->execute();
			}
		}
		catch (\Exception $e) {
			echo "<tr><td colspan=5>$link fail</td></tr>";
		}
		$i++;
		flush();
		ob_flush();
	}
	echo "</table>";
	
	$dbh = null;