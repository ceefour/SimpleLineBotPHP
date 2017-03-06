<?php

require __DIR__ . '/../lib/vendor/autoload.php';

use \LINE\LINEBot\SignatureValidator as SignatureValidator;

$adminIds = [
	'ceefour' => 'U11d4438ecbcd135f2f85c7faf4cb7a5d',
	'shaqiinamachz' => 'U651ad6a7b141fb5517e3e2f0ae2deae9',
	'simkuringaryo' => 'U41000a9fc727ed43bf296290c4468faa'
];

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
	return "Lanjutkan!";
});

$app->post('/', function ($request, $response)
{
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

/* JUST RUN IT */
$app->run();