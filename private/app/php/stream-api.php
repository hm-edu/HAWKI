<?php

if (!defined('BOOTSTRAP_PATH')) {
	define('BOOTSTRAP_PATH',  '../../bootstrap.php');
}

require_once BOOTSTRAP_PATH;

session_start();
if (!isset($_SESSION['username'])) {
	http_response_code(401);
	exit;
}

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

// Add this block to handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(204);
	exit;
}

if (file_exists(ENV_FILE_PATH)){
    $env = parse_ini_file(ENV_FILE_PATH);
}

// Replace with your API URL and API key
$apiUrl = isset($env) ? $env['OPENAI_API_URL'] : getenv('OPENAI_API_URL');
$apiKey = isset($env) ? $env['OPENAI_API_KEY'] : getenv('OPENAI_API_KEY');

// Read the request payload from the client
$requestPayload = file_get_contents('php://input');

//set include_usage:true if not set in json
$usage_pattern = '/"include_usage":true/';
if(!preg_match($usage_pattern, $requestPayload)){
	$requestPayload = json_decode($requestPayload, true);
	$requestPayload['stream_options']['include_usage'] = true;
	$requestPayload = json_encode($requestPayload);
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $requestPayload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
	'Authorization: Bearer ' . $apiKey,  # necessary for OpenAI
	'api-key: ' . $apiKey,               # necessary for Microsoft Azure AI
	'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) {
	get_tokens($data);
	echo $data;
	if (ob_get_level() > 0) {
		ob_flush();
	}
	flush();
	return strlen($data);
});

curl_exec($ch);

if (curl_errno($ch)) {
	echo 'Error:' . curl_error($ch);
}

curl_close($ch);


function get_tokens($data){
	$pattern = '/"usage":\{"prompt_tokens":[0-9]+,"completion_tokens":[0-9]+,"total_tokens":[0-9]+\}/';
	if (preg_match_all($pattern, $data, $matches)) {
		$last_match = end($matches[0]);
		$numberpattern = '/[0-9]+/';
		if (preg_match_all($numberpattern, $last_match, $numbers)){
	
			$prompt_tokens = $numbers[0][0];
			$completion_tokens = $numbers[0][1];
			$total_tokens = $numbers[0][2];
			
			$host = getenv("DB_HOST");
			$db = getenv("DB_DB");
			$table = getenv('DB_TABLE');
			$user = getenv("DB_USER");
			$pass = getenv("DB_PASS");
			$port = getenv("DB_PORT");
			$dsn = "pgsql:host=$host;port=$port;dbname=$db";
			try{
				$pdo = new PDO($dsn, $user, $pass);
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$sql = "INSERT INTO $table (username,datum,prompt_tokens,completion_tokens,total_tokens) VALUES(:username,:datum,:prompt,:completion,:total) ON CONFLICT (username,datum) DO UPDATE SET prompt_tokens = $table.prompt_tokens + EXCLUDED.prompt_tokens, completion_tokens = $table.completion_tokens + EXCLUDED.completion_tokens, total_tokens = $table.total_tokens + EXCLUDED.total_tokens;";
				$stmt=$pdo->prepare($sql);

				$stmt->bindParam(':username',$username);
				$stmt->bindParam(':datum',$datum);
				$stmt->bindParam(':prompt',$prompt);
				$stmt->bindParam(':completion',$completion);
				$stmt->bindParam(':total',$total);

				$username = $_SESSION['username'];
				$datum = date("Y-m-d");
				$prompt = $prompt_tokens;
				$completion = $completion_tokens;
				$total = $total_tokens;
				$stmt->execute();

			} catch (PDOException $e) {
				error_log($e->getMessage(),0);
			}
		}
		
	}
}