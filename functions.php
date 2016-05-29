<?php
	
	ini_set("display_errors", 1);
	$vead=array();
	
	function connect_db(){
  		global $connection;
 		 $host="localhost";
 		 $user="test";
 		 $pass="t3st3r123";
 		 $db="test";
 		 $connection = mysqli_connect($host, $user, $pass, $db) or die("ei saa mootoriga ühendust");
 		 mysqli_query($connection, "SET CHARACTER SET UTF8") or die("Ei saanud baasi utf-8-sse - ".mysqli_error($connection));
	}
	
	
	function logout(){
		$_SESSION=array();
		session_destroy();
		header("Location: ?");
	}
	
	function login(){
			global $connection;
			global $vead;
			if (!empty($_SESSION['user'])){
				header("Location: ?page=pealeht");	
			}
			if ($_SERVER['REQUEST_METHOD']=='GET'){
				include_once('view/login.html');
			}
			if ($_SERVER['REQUEST_METHOD']=='POST'){
				if (empty($_POST["kasutajanimi"])){
					$vead[]= "Kasutajaväli ei saa olla tühi!";
				}
				if (empty($_POST["parool"])){
					$vead[]="Parool ei saa olla tühi!";
				}
				if (!empty($vead)){
					include_once('view/login.html');
				}
			}
			if (!empty($_POST) && empty($vead)){
				$kasutaja= mysqli_real_escape_string($connection, $_POST["kasutajanimi"]);
				$parool= mysqli_real_escape_string($connection, $_POST["parool"]);
				$p2ring="SELECT id_kasutaja, roll FROM srekkaro__kasutaja WHERE kasutajanimi = '$kasutaja' AND password = SHA1('$parool')";
				$result=mysqli_query($connection, $p2ring);
				if (mysqli_num_rows($result)>0){
					$rida=mysqli_fetch_assoc($result);
					$_SESSION['user']=$rida['id_kasutaja'];
					$_SESSION['roll']=$rida['roll'];
					header("Location: ?page=vastutajad");
				}
				if (mysqli_num_rows($result)==0){
					$errors[]= "Sellist kasutajat ei leitud!";
					include_once('view/login.html');
				}
		
		
		}
	}
	
	function vastutajad(){
		include_once('view/vastutajad.html');	
	}
	
	function lisakasutaja(){
		include_once('view/lisaKasutaja.html');	
	}
	
	function kasutajad(){
			global $connection;
			if (empty($_SESSION['user'])){
				header("Location: ?page=login");	
			}
			$kasutajad=array();
			$kasutajateP2ring = "SELECT * FROM srekkaro__kasutaja";
			$tulemus= mysqli_query($connection, $kasutajateP2ring) or die ("Viga andmebaasis - ".mysqli_error($connection));
			while ($rida = mysqli_fetch_assoc($tulemus)){
				$kasutajad[]=$rida;
			}
		include_once('view/kasutajad.html');
		include_once('view/foot.html');	
	}
	
	
	function muudaauto(){
		include_once('view/muudamasin.html');	
	}

	function lisaauto(){
		include_once('view/lisaMasin.html');	
	}
	
	function autod(){
		include_once('view/kuvamasin.html');	
	}
?>