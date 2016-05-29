<?php
	require_once('functions.php');

	session_start();
	connect_db();
	$leht="home";
	$page="pealeht";
	if (isset($_GET['page']) && $_GET['page']!=""){
		$page=htmlspecialchars($_GET['page']);
	}
	include_once('view/head.html');
	switch($page){
		case "login":
			login();
		break;
		case "logout":
			logout();
		break;
		case "lisakasutaja":
			lisakasutaja();
		break;
#		case "muudakasutaja":
#			muudakasutaja();
#		break;
		case "kasutajad":
			kasutajad();
		break;
#		case "lisavastutaja":
#			lisavastutaja();
#		break;
#		case "muudavastutaja":
#			muudavastutaja();
#		break;
		case "vastutajad":
			vastutajad();
		break;
		case "muudaauto":
			muudaauto();
		break;
		case "lisaauto":
			lisaauto();
		break;
		case "autod":
			autod();
		break;
		default:
			include_once('view/index.html');
		break;
		}
	include_once ('view/foot.html');
?>