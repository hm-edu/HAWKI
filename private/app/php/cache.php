<?php
function get_token_limit(){
    
	/*
	update every 30 min
	*/ 

	$update = apcu_add("time", false, 1800);
	if ($update) {
		
		apcu_store("update", true, 10);
		usleep(100 * 1000); 
		error_log("update cache");
		update_cache();
		apcu_delete("update");
	}

    
	while (apcu_fetch("update")) {
		usleep(30 * 1000);
	}
	$token_limit = apcu_fetch("token_limit");
	$index = apcu_fetch($_SESSION['username']);
	$index_status = apcu_fetch("STUDENT"); #$_SESSION['status']


    if (has_tokenlimit($token_limit, $index)) {

        return $token_limit[$index]['token_limit'];
    }

    if (is_int($index_status)) {
        return $token_limit[$index_status]['token_limit'];
    }

    return getenv("TOKEN_LIMIT");

}

function update_cache() {
    $host = getenv("DB_HOST");
	$db = getenv("DB_DB");
	$table = getenv('DB_TABLE_LIMIT');
	$user = getenv("DB_USER");
	$pass = getenv("DB_PASS");
	$port = getenv("DB_PORT");
	$dsn = "pgsql:host=$host;port=$port;dbname=$db";
	
	try {
 		$pdo = new PDO($dsn, $user, $pass);
 		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sql = "SELECT username,token_limit FROM $table WHERE valid_till >= :datum";
	    $stmt = $pdo->prepare($sql);
		$stmt->bindParam(':datum', $datum);
		$datum = date("Y-m-d");
	    $stmt->execute();
	    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		apcu_store("token_limit", $result, 3600);
		for ($i=0; $i < count($result); $i++) {
			apcu_store($result[$i]['username'], $i, 3600);
		}


	} catch (PDOException $e) {
	    error_log($e->getMessage());
	    exit;
	}

}

function has_tokenlimit ($token_limit, $index) {
	if (! ($token_limit && is_int($index))) {
		return false;
	}

	if ($token_limit[$index]['username'] != $_SESSION['username']) {
		apcu_delete($_SESSION['username']);
		return false;
	}

	return true;
}