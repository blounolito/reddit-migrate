<?php
	header( 'Content-type: text/html; charset=utf-8' );
	set_time_limit(0);
	
	// You have to use 2 GET parameters, action and nsfw
	// e.g : ./subscribe.php?action=sub&nsfw=0
	$action = $_GET['action'];
	if (!in_array($action, ['sub', 'unsub']))
	{
		die('action: sub, unsub');
	}
	$nsfw   = $_GET['nsfw'];
	if (!in_array($nsfw, ['0', '1']))
	{
		die('nsfw: 0, 1');
	}
	
	// DB credentials
	$user = 'DB_USER';
	$pass = 'DB_PASS';
	$host = 'DB_HOST';
	$dbna = 'DB_NAME';
	
	// Reddit account where you want to subscribe to your olds subreddits
	$redditUser = 'REDDIT_USER';
	$redditPass = 'REDDIT_PASS';
	
	// Under your new account, you have to create an API key
	// More infos here : https://github.com/reddit-archive/reddit/wiki/OAuth2
	$appliCode   = 'APPLI_CODE';
	$appliSecret = 'APPLI_SECRET';

	$tokenAPI     = 'https://www.reddit.com/api/v1/access_token';
	$subscribeAPI = 'https://oauth.reddit.com/api/subscribe';

	$dbh = new PDO('mysql:host='.$host.';dbname='.$dbna, $user, $pass);

	// 1. get token
	$params = [
		'grant_type' => 'password',
		'username' => $redditUser,
		'password' => $redditPass
	];
	$options = [
		CURLOPT_URL => $tokenAPI,
		CURLOPT_USERPWD => "$appliCode:$appliSecret",
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $params,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false
	];
	$ch = curl_init();
	curl_setopt_array($ch, $options);
	$apiRawResult = curl_exec($ch);
	$apiResult    = json_decode($apiRawResult);
	$token        = $apiResult->access_token;
	curl_close($ch);

	// 2. list subreddits
	$sth = $dbh->prepare("SELECT * FROM subreddit WHERE sr_nsfw = $nsfw AND sr_g_id IS NULL");
	$sth->execute();
	$subreddits = $sth->fetchAll();
	
	$threshold = 1024;
	$currentThreshold = 0;
	$list = '';
	foreach($subreddits as $k => $subreddit) {
		$list .= ',' . $subreddit['sr_rawtitle'];
		if(strlen($list) > 1024 || $k == count($subreddits)-1) {
			$list = ltrim($list, ',');
			echo "list=".ltrim($list, ',') . "<br />\n";
			
			// 3. curl sub/unsub
			$params = [
				'sr_name' => $list,
				'action' => $action
			];
			$options = [
				CURLOPT_URL => $subscribeAPI,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $params,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_USERAGENT => 'Subscriber_bot/0.1 by Blounolito',
				CURLOPT_HTTPHEADER => [
					"Authorization: Bearer $token"
				]
			];
			$ch = curl_init();
			curl_setopt_array($ch, $options);
			$apiRawResult = curl_exec($ch);
			curl_close($ch);
			
			$list = '';
		}
	}
	$dbh = null;