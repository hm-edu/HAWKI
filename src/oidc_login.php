<?php

// use library for dealing with OpenID connect
require __DIR__ . '/vendor/autoload.php';

use Jumbojett\OpenIDConnectClient;

if (file_exists(".env")){
    $env = parse_ini_file('.env');
}

// Create OpenID connect client

$oidc = new OpenIDConnectClient(
    isset($env) ? $env["OIDC_IDP"] : getenv("OIDC_IDP"),
    isset($env) ? $env["OIDC_CLIENT_ID"] : getenv("OIDC_CLIENT_ID"),
    isset($env) ? $env["OIDC_CLIENT_SECRET"] : getenv("OIDC_CLIENT_SECRET")
);

# Demo is dealing with HTTP rather than HTTPS
$testuser = isset($env) ? $env["TESTUSER"] : getenv("TESTUSER");
if ($testuser) {
    $oidc->setHttpUpgradeInsecureRequests(false);
}

$oidc->addScope('email');
$oidc->authenticate();

$_SESSION['username'] = $oidc->requestUserInfo('email');

header("Location: interface.php");
exit();

?>