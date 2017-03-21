<?php

require __DIR__ . '/../lib/vendor/autoload.php';

use \LINE\LINEBot\SignatureValidator as SignatureValidator;

$adminIds = [
	'ceefour' => 'U11d4438ecbcd135f2f85c7faf4cb7a5d',
	'shaqiinamachz' => 'U651ad6a7b141fb5517e3e2f0ae2deae9',
	'simkuringaryo' => 'U41000a9fc727ed43bf296290c4468faa'
];
$beacons = [
	'cb10023f-a318-3394-4199-a8730c7c1aec' => [
		'name' => 'Lab. Kendali',
		'description' => 'Laboratorium kendali memberikan fasilitas bagi mahasiswa untuk melakukan penelitian di bidang sistem kendali, dengan peralatan yang memadai.',
		'items' => ['notebook', 'power supply', 'monitor', 'baterai'],
	],
	'fda50693-a4e2-4fb1-afcf-c6eb07647825' => [
		'name' => 'Lab. Robotika',
		'items' => ['NAO', 'microphone', 'sound card', 'keyboard', 'mouse', 'flash drive'],
	]
];
$allItems = ['notebook', 'power supply', 'monitor', 'baterai',
	'NAO', 'microphone', 'sound card', 'keyboard', 'mouse', 'flash drive'];

// load config
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

// initiate app
$configs =  [
	'settings' => ['displayErrorDetails' => true]
];
$app = new Slim\App($configs);

/* ROUTES */
$app->get('/', function ($request, $response) {
	return "<h1>Info Inventaris Lab</h1>\n" .
		"<ul><li>Mendapatkan informasi langsung mengenai inventaris laboratorium yang dapat digunakan oleh mahasiswa</li>\n" .
		"<li>Dapat melakukan secara langsung memesan pinjaman</li>\n" .
		"<li>Proses peminjaman lebih efektif dan efisien</li>\n" .
		"</ul>";
});

$app->post('/', function ($request, $response)
{
	global $allItems;

	// get request body and line signature header
	$body 	   = file_get_contents('php://input');
	$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'];

	// log body and signature
	file_put_contents('php://stderr', 'Body: '.$body);
	error_log('Body: '.$body);

	// is LINE_SIGNATURE exists in request header?
	if (empty($signature)){
		return $response->withStatus(400, 'Signature not set');
	}

	// is this request comes from LINE?
	if($_ENV['PASS_SIGNATURE'] == false && ! SignatureValidator::validateSignature($body, $_ENV['CHANNEL_SECRET'], $signature)){
		return $response->withStatus(400, 'Invalid signature');
	}

	// init bot
	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);

	$data = json_decode($body, true);
	foreach ($data['events'] as $event)
	{
		if ($event['type'] == 'message')
		{
			if($event['message']['type'] == 'text')
			{
				$text = $event['message']['text'];

				if ($text == 'help') {
					$replyMessage = "Info Inventaris Lab\n\n" .
						"Saat memasuki area lab, Anda akan otomatis mendapatkan informasi tentang lab. tersebut.\n" .
						"Untuk meminjam barang, ketik: pinjam <barang>\n" .
						"Untuk mengembalikan barang, ketik: kembali <barang>\n";
					$result = $bot->replyText($event['replyToken'], $replyMessage);
					return $result->getHTTPStatus() . ' ' . $result->getRawBody();
				} else if (preg_match('/pinjam (.+)/', $text, $matches)) {
					$item = $matches[1];
					if (in_array($item, $allItems)) {
						$replyMessage = 'Anda telah meminjam ' . $item . ".\n" .
							"Ketik: kembali " . $item . "\n".
							"bila Anda telah selesai meminjam.";
						$result = $bot->replyText($event['replyToken'], $replyMessage);
						return $result->getHTTPStatus() . ' ' . $result->getRawBody();
					} else {
						$replyMessage = 'Maaf, kami tidak menyediakan ' . $item . '.';
						$result = $bot->replyText($event['replyToken'], $replyMessage);
						return $result->getHTTPStatus() . ' ' . $result->getRawBody();
					}
				} else if (preg_match('/kembali (.+)/', $text, $matches)) {
					$item = $matches[1];
					$replyMessage = 'Terima kasih, Anda telah mengembalikan ' . $item .'.';
					$result = $bot->replyText($event['replyToken'], $replyMessage);
					return $result->getHTTPStatus() . ' ' . $result->getRawBody();
				} else {
					// send same message as reply to user
					$result = $bot->replyText($event['replyToken'], $event['message']['text']);

					// file_put_contents('php://stderr', 'Received LINE Event: ' . print_r($event, true));
					// error_log('Out Received LINE Event: ' . print_r($event, true));

					// or we can use pushMessage() instead to send reply message
					// $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($event['message']['text']);
					// $result = $bot->pushMessage($event['source']['userId'], $textMessageBuilder);
					
					return $result->getHTTPStatus() . ' ' . $result->getRawBody();
				}
			}
		}
	}

});

// $app->get('/push/{to}/{message}', function ($request, $response, $args)
// {
// 	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
// 	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);

// 	$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($args['message']);
// 	$result = $bot->pushMessage($args['to'], $textMessageBuilder);

// 	return $result->getHTTPStatus() . ' ' . $result->getRawBody();
// });

$app->get('/pushadmin', function($request, $response, $args) { // ?message={message}
	global $adminIds;

	$message = $request->getQueryParam('message');
	if (empty($message)) {
		return $response->withStatus(400, 'message query param not set');
	}

	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);

	$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
	$result1 = $bot->pushMessage($adminIds['ceefour'], $textMessageBuilder);
	$result2 = $bot->pushMessage($adminIds['shaqiinamachz'], $textMessageBuilder);
	$result3 = $bot->pushMessage($adminIds['simkuringaryo'], $textMessageBuilder);

	$resultStr = $result1->getHTTPStatus() . ' ' . $result1->getRawBody() . "\n" .
		$result2->getHTTPStatus() . ' ' . $result2->getRawBody() . "\n" .
		$result3->getHTTPStatus() . ' ' . $result3->getRawBody();
	return $resultStr;
});

function sendText($userId, $message) {
	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);

	$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
	$result = $bot->pushMessage($userId, $textMessageBuilder);
	return $result;
}

/**
 * When a beacon is in range, device should GET to this action
 * and providing parameters:
 * username = LINE username of the device user
 * uuid = Beacon UUID
 */
$app->get('/pushdevice', function($request, $response, $args) { // ?message={message}
	global $adminIds, $beacons;

	$username = $request->getQueryParam('username');
	if (empty($username)) {
		return $response->withStatus(400, 'username query param not set');
	}
	$uuid = $request->getQueryParam('uuid');
	if (empty($uuid)) {
		return $response->withStatus(400, 'uuid query param not set');
	}

	$beacon = $beacons[$uuid];
	if (!$beacon) {
		return $response->withStatus(400, 'Unknown beacon: ' . $uuid);
	}
	$userId = $adminIds[$username];
	if (!$userId) {
		return $response->withStatus(400, 'Unregistered user: ' . $username);
	}	

	$message = 'Selamat datang di ' . $beacon['name'] . ". \n" .
		$beacon['description'];
	$message .= "\n" . "Barang yang dapat dipinjam: " . implode(', ', $beacon['items']) ."\n".
		"Untuk meminjam, ketik: pinjam <barang>\n".
		"Untuk mengembalikan, ketik: kembali <barang>\n";
	
	$result = sendText($userId, $message);
	$resultStr = $result->getHTTPStatus() . ' ' . $result->getRawBody() . "\n";
	return $resultStr;
});

/* JUST RUN IT */
$app->run();