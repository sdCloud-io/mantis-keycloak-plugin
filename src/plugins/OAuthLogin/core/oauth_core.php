<?php

function private_oauth_send_request ($method = 'GET', $path = '/', $headers = array(), $data = '') {
	
	$method = strtoupper($method);
	$url = $path;

	// Initiate HTTP request
	$request = curl_init();

	curl_setopt($request, CURLOPT_URL, $url);
	curl_setopt($request, CURLOPT_RETURNTRANSFER, TRUE);

	if ($method === 'POST') {
		curl_setopt($request, CURLOPT_POST, TRUE);
		curl_setopt($request, CURLOPT_POSTFIELDS, $data);
		array_push($headers, 'Content-Length: ' . strlen($data));
	}

	curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
	$response = curl_exec($request);
	$response_code = curl_getinfo($request, CURLINFO_HTTP_CODE);
	curl_close($request);

	return array(
		'code' => $response_code,
		'body' => $response
	);
}

function private_oauth_has_redirect_query_params() {
    return isset($_GET['state']) && isset($_GET['session_state']);
}

function private_oauth_get_token_by_code( $grant_code ) {
    $url = 'http://keycloak-server/auth/realms/<realm-name>/protocol/openid-connect/token';
    $data = array(
        'grant_type' => 'authorization_code',
        'code' => $grant_code,
        'redirect_uri' => 'http://127.0.0.1:81/mantis/login.php',
        'client_id' => '<client-id>',
        'client_secret' => '<client-secret>'
    );


    $headers = array(
        'Content-type: application/x-www-form-urlencoded'
    );

    echo 'Sending request...';
    echo "<br />";

    $response = private_oauth_send_request('POST', $url, $headers, http_build_query($data));

    echo 'Got response';
    echo "<br />";

    echo $response['body'];
    echo "<br />";
    echo "<br />";

    $responseJson = json_decode($response['body']);

    $token = false;
    
    if(!isset($responseJson->access_token)) {
        echo 'Error fetching access token';
        echo "<br />";
    } else {
        $tokenParts = explode('.', $responseJson->access_token);
        echo $tokenParts[1];
        echo "<br />";
        echo base64_decode($tokenParts[1]);
        echo "<br />";
        $token = json_decode(base64_decode($tokenParts[1]));
    }

    return $token;
}

function private_oauth_get_user_id_by_token_data( $token ) {
    $user_id = false;

    $tokenUser  = $token->preferred_username;
    $mantisUser = $token->mantis_login;

    $user_id = user_get_id_by_name( $tokenUser );
    if( $user_id !== false ) {
        echo "Got user by keycloak id ".$tokenUser." = ".$user_id;
        echo "<br />";
    } else {
        echo "Failed to get user by keycloak id ".$tokenUser;
        echo "<br />";

        $user_id = user_get_id_by_name( $mantisUser );

        if( $user_id !== false ) {
            echo "Got user by mantis_login id ".$mantisUser." = ".$user_id;
            echo "<br />";
        } else {
            echo "Failed to get user by mantis login ".$mantisUser;
            echo "<br />";
        }
    }

    return $user_id;
}

function oauth_attempt_login( $t_return ) {
    $result = false;

    if (private_oauth_has_redirect_query_params()) {

        $kk_state = $_GET['state'];
        $kk_session_state = $_GET['session_state'];
    
        echo $kk_state;
        echo "<br />";
        echo $kk_session_state;
        echo "<br />";
    
        $token = private_oauth_get_token_by_code( $_GET['code'] );
        $user_id = false;
        if ( $token ) {
            $user_id = private_oauth_get_user_id_by_token_data( $token );
        }
        
        if ($user_id !== false) {
            auth_login_user($user_id, true);
            $result = 'login_cookie_test.php?return=' . $t_return;
        } else {
            $result = 'login_page.php';
        }    
    }
}