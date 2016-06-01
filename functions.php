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
	
	function lisakasutaja(){
			global $connection;
			if (empty($_SESSION['user']) || (($_SESSION['roll'])!="admin")) {
				header("Location: ?page=login");	
			}
			$pildiurl="";
			if ($_SERVER['REQUEST_METHOD']=='GET'){
				include_once('view/lisaKasutaja.html');
			}
			if ($_SERVER['REQUEST_METHOD']=='POST'){
				if (empty($_POST["Eesnimi"])){
					$vead[]= "Kasutajal peab olema eesnimi!";
				}
				if (empty($_POST["perenimi"])){
					$vead[]= "Kasutajal peab olema perekonnanimi!";
				}
				if (empty($_POST["isikukood"])){
					$vead[]= "Kasutajal peab olema isikukood!";
				}
				if (empty($_POST["kasutajanimi"])){
					$vead[]= "Kasutajal peab olema kasutajanimi!";
				}
				if (empty($_POST["parool"])){
					$vead[]= "Kasutajal peab olema salasõna!";
				}
				if (($_POST["parool"])!= ($_POST["parooluuesti"])){
					$vead[]= "Salasõnad ei klapi!";
				}
				if (empty($_POST["elukoht"])){
					$errors[]="Kasutajal peab olema elukoht!";
				}
				
				if (($_POST["KasutajaRoll"]=="user") && ($_POST["KasutajaRoll"])=="admin"){
					$vead[]="Kasutaja peab olema kas user või admin";
				}
				if (!empty($_FILES['KasutajaFoto']['name'])){
					$pildiurl=uploadKasutaja('KasutajaFoto');
				} 
				if ($pildiurl==""){
					$errors[]="Kasutajal peab olema pilt!";
				}
				$nimi=mysqli_real_escape_string($connection, $_POST["Eesnimi"]);
				$perenimi=mysqli_real_escape_string($connection, $_POST["perenimi"]);
				$isikukood=mysqli_real_escape_string($connection, $_POST["isikukood"]);
				$kasutajanimi=mysqli_real_escape_string($connection, $_POST["kasutajanimi"]);
				$passw=mysqli_real_escape_string($connection, $_POST["parool"]);
				$passw=sha1($passw);
				$elukoht=mysqli_real_escape_string($connection, $_POST["elukoht"]);
				$roll=mysqli_real_escape_string($connection, $_POST["KasutajaRoll"]);
				$pildiurl=mysqli_real_escape_string($connection, $pildiurl);	
				if (empty($vead)){
					$sql= "INSERT INTO srekkaro__kasutaja ( nimi, perenimi, kasutajanimi, password, isikukood, elukoht, foto_k, roll) VALUES ('$nimi', '$perenimi', '$kasutajanimi', '$passw', '$isikukood', '$elukoht', '$pildiurl', '$roll')";
					$tulemus=mysqli_query($connection, $sql);
					$viga= mysqli_error($connection);
					print_r($viga);	
						if ($tulemus){
							if(mysqli_affected_rows($connection)>0){
								header("Location: ?page=kasutajad");
							}
						}
					}		
			}
		include_once('view/lisaKasutaja.html');	
	}
	
	function kasutajad(){
			global $connection;
			if (empty($_SESSION['user']) || (($_SESSION['roll'])!="admin")) {
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
		
	function muudakasutaja(){
			global $connection;
			if (empty($_SESSION['user']) || (($_SESSION['roll'])!="admin")) {
				header("Location: ?page=login");	
			}
			$pildiurl="";
			if ($_SERVER['REQUEST_METHOD']=='GET'){
				if ($_GET['id']=="") {
					header("Location: ?page=kasutajad");	
				}
				else {
					$kasutajaid=htmlspecialchars($_GET['id']);	
				}
			}
			if ($_SERVER['REQUEST_METHOD']=='POST'){
				if ($_POST['id']=="") {
					header("Location: ?page=kasutajad");	
				}
				else {
					$kasutajaid=htmlspecialchars($_POST['id']);	
				}
			}
			$kasutaja=hangi_kasutaja($kasutajaid);
			
			if ($_SERVER['REQUEST_METHOD']=='POST'){
				if (empty($_POST["Eesnimi"])){
					$vead[]= "Kasutajal peab olema eesnimi!";
				}
				if (empty($_POST["perenimi"])){
					$vead[]= "Kasutajal peab olema perekonnanimi!";
				}
				if (empty($_POST["isikukood"])){
					$vead[]= "Kasutajal peab olema isikukood!";
				}
				if (empty($_POST["kasutajanimi"])){
					$vead[]= "Kasutajal peab olema kasutajanimi!";
				}
				if (!empty($_POST["parool"])){
					if (($_POST["parool"])!= ($_POST["parooluuesti"])){
						$vead[]= "Salasõnad ei klapi!";
					}
					else {
						$passw=mysqli_real_escape_string($connection, $_POST["parool"]);
						$passw=sha1($passw);
					}
				}
				else {
					$passw=$kasutaja['password'];
				}
				
				if (empty($_POST["elukoht"])){
					$vead[]="Kasutajal peab olema elukoht!";
				}
				if (!empty($_FILES['KasutajaFoto']['name'])){
					$pildiurl=uploadKasutaja('KasutajaFoto');
					print_r($pildiurl);
				}
				$id=mysqli_real_escape_string($connection, $_POST["id"]); 	
				$nimi=mysqli_real_escape_string($connection, $_POST["Eesnimi"]);
				$perenimi=mysqli_real_escape_string($connection, $_POST["perenimi"]);
				$isikukood=mysqli_real_escape_string($connection, $_POST["isikukood"]);
				$kasutajanimi=mysqli_real_escape_string($connection, $_POST["kasutajanimi"]);
				$elukoht=mysqli_real_escape_string($connection, $_POST["elukoht"]);
				$roll=mysqli_real_escape_string($connection, $_POST["KasutajaRoll"]);
				if ($pildiurl!=""){
					$pildiurl=mysqli_real_escape_string($connection, $pildiurl);
					$sql= "UPDATE srekkaro__kasutaja SET nimi='$nimi', perenimi='$perenimi', kasutajanimi='$kasutajanimi', password='$passw', isikukood='$isikukood', elukoht='$elukoht', foto_k='$pildiurl', roll='$roll' WHERE id_kasutaja='$id'";
				}
				if ($pildiurl==""){
					$sql= "UPDATE srekkaro__kasutaja SET nimi='$nimi', perenimi='$perenimi', kasutajanimi='$kasutajanimi', password='$passw', isikukood='$isikukood', elukoht='$elukoht', roll='$roll' WHERE id_kasutaja='$id'";
				}
				if (empty($vead)){
						$tulemus=mysqli_query($connection, $sql);
						$viga= mysqli_error($connection);
						print_r($viga);	
						if ($tulemus){
							if(mysqli_affected_rows($connection)>0){
								header("Location: ?page=kasutajad");
							}
						}
					}									
				}		
		include_once('view/muudakasutaja.html');	
	}
	
	function kustutakasutaja(){
			global $connection;
			if ((empty($_SESSION['user'])) || ($_SESSION['roll']!="admin")) {
				header("Location: ?page=login");	
			}
			if ($_SERVER['REQUEST_METHOD']=='GET'){
				if ($_GET['id']=="") {
					header("Location: ?page=autod");	
				}
				else {
					$kasutajaid=htmlspecialchars($_GET['id']);	
				}
			}
			$kasutajaid=mysqli_real_escape_string($connection, $kasutajaid);
			$sql="DELETE from srekkaro__kasutaja WHERE id_kasutaja='$kasutajaid'";
			if (empty($vead)){	
				$tulemus=mysqli_query($connection, $sql);
				$viga= mysqli_error($connection);
				print_r($viga);	
					if ($tulemus){
						if(mysqli_affected_rows($connection)>0){
							header("Location: ?page=kasutajad");
						}
					
					}						
			}
	}	
	
	
	function muudaauto(){
			global $connection;
			if (empty($_SESSION['user'])) {
				header("Location: ?page=login");	
			}
			$pildiurl="";
			if ($_SERVER['REQUEST_METHOD']=='GET'){
				if ($_GET['id']=="") {
					header("Location: ?page=autod");	
				}
				else {
					$autoid=htmlspecialchars($_GET['id']);	
				}
			}
			if ($_SERVER['REQUEST_METHOD']=='POST'){
				if ($_POST['id']=="") {
					header("Location: ?page=autod");	
				}
				else {
					$autoid=htmlspecialchars($_POST['id']);	
				}
			}
			$auto=hangi_auto($autoid);
			if ($_SERVER['REQUEST_METHOD']=='POST'){
				if (empty($_POST["vinkood"])){
					$vead[]= "Autol peab olema VIN kood!";
				}
				if (empty($_POST["regnum"])){
					$vead[]= "Autol peab olema registreerimis number!";
				}
				if (empty($_POST["mark"])){
					$vead[]= "Autol peab olema mark!";
				}
				if (empty($_POST["mudel"])){
					$vead[]= "Autol peab olema mudel!";
				}
				if (empty($_POST["labisoit"])){
					$vead[]="Autol peab olema fikseeritud läbisõit!";
				}
				if (!empty($_FILES['AutoFoto']['name'])){
					$pildiurl=uploadAuto('AutoFoto');
				} 

				$id=mysqli_real_escape_string($connection, $_POST["id"]);
				$vinkood=mysqli_real_escape_string($connection, $_POST["vinkood"]);
				$regnum=mysqli_real_escape_string($connection, $_POST["regnum"]);
				$mark=mysqli_real_escape_string($connection, $_POST["mark"]);
				$mudel=mysqli_real_escape_string($connection, $_POST["mudel"]);
				$labisoit=mysqli_real_escape_string($connection, $_POST["labisoit"]);
					
				if ($pildiurl!=""){
					$pildiurl=mysqli_real_escape_string($connection, $pildiurl);
					$sql= "UPDATE srekkaro__masin SET vinkood='$vinkood', regnum='$regnum', mark='$mark', mudel='$mudel', labisoit='$labisoit', foto_s='$pildiurl' WHERE id='$id'";
				}
				if ($pildiurl==""){
					$sql= "UPDATE srekkaro__masin SET vinkood='$vinkood', regnum='$regnum', mark='$mark', mudel='$mudel', labisoit='$labisoit' WHERE id='$id'";
				}
				if (empty($vead)){	
					$tulemus=mysqli_query($connection, $sql);
					$viga= mysqli_error($connection);
					print_r($viga);	
						if ($tulemus){
							if(mysqli_affected_rows($connection)>0){
								header("Location: ?page=autod");
							}
						}
					}		
			}
		
		include_once('view/muudamasin.html');	
	}
	
	function kustutaauto(){
			global $connection;
			if (empty($_SESSION['user'])) {
				header("Location: ?page=login");	
			}
			if ($_SESSION['roll']!="admin"){
				$vead[]="Auto kustutamiseks pead olema administraator!";
				header("Location: ?page=autod");
		}
			if ($_SERVER['REQUEST_METHOD']=='GET'){
				if ($_GET['id']=="") {
					header("Location: ?page=autod");	
				}
				else {
					$autoid=htmlspecialchars($_GET['id']);	
				}
			}
			$autoid=mysqli_real_escape_string($connection, $autoid);
			$sql="DELETE from srekkaro__masin WHERE id='$autoid'";
			if (empty($vead)){	
				$tulemus=mysqli_query($connection, $sql);
				$viga= mysqli_error($connection);
				print_r($viga);	
					if ($tulemus){
						if(mysqli_affected_rows($connection)>0){
							header("Location: ?page=autod");
							}
						}
					}						
	}

	function lisaauto(){
		global $connection;
			if (empty($_SESSION['user'])) {
				header("Location: ?page=login");	
			}
			$pildiurl="";
			if ($_SERVER['REQUEST_METHOD']=='GET'){
				include_once('view/lisaMasin.html');
			}
			if ($_SERVER['REQUEST_METHOD']=='POST'){
				if (empty($_POST["vinkood"])){
					$vead[]= "Autol peab olema VIN kood!";
				}
				if (empty($_POST["regnum"])){
					$vead[]= "Autol peab olema registreerimis number!";
				}
				if (empty($_POST["mark"])){
					$vead[]= "Autol peab olema mark!";
				}
				if (empty($_POST["mudel"])){
					$vead[]= "Autol peab olema mudel!";
				}
				if (empty($_POST["labisoit"])){
					$vead[]="Autol peab olema fikseeritud läbisõit!";
				}
				if (!empty($_FILES['AutoFoto']['name'])){
					$pildiurl=uploadAuto('AutoFoto');
				} 
				if ($pildiurl==""){
					$vead[]="Autost peab olema pilt!";
				}
				$vinkood=mysqli_real_escape_string($connection, $_POST["vinkood"]);
				$regnum=mysqli_real_escape_string($connection, $_POST["regnum"]);
				$mark=mysqli_real_escape_string($connection, $_POST["mark"]);
				$mudel=mysqli_real_escape_string($connection, $_POST["mudel"]);
				$labisoit=mysqli_real_escape_string($connection, $_POST["labisoit"]);
				$pildiurl=mysqli_real_escape_string($connection, $pildiurl);	
				if (empty($vead)){
					$sql= "INSERT INTO srekkaro__masin ( vinkood, regnum, mark, mudel, labisoit, foto_s) VALUES ('$vinkood', '$regnum', '$mark', '$mudel', '$labisoit', '$pildiurl')";
					$tulemus=mysqli_query($connection, $sql);
					$viga= mysqli_error($connection);
					print_r($viga);	
						if ($tulemus){
							if(mysqli_affected_rows($connection)>0){
								header("Location: ?page=autod");
							}
						}
					}		
			}
		include_once('view/lisaMasin.html');	
	}
	
function autod(){
			global $connection;
			if (empty($_SESSION['user'])) {
				header("Location: ?page=login");	
			}
			$autod=array();
			$autodeP2ring = "SELECT * FROM srekkaro__masin";
			$tulemus= mysqli_query($connection, $autodeP2ring) or die ("Viga andmebaasis - ".mysqli_error($connection));
			while ($rida = mysqli_fetch_assoc($tulemus)){
				$autod[]=$rida;
			}
		include_once('view/kuvamasin.html');	
	}
	
	
	
function uploadKasutaja($name){
	$allowedExts = array("jpg", "jpeg", "gif", "png");
	$allowedTypes = array("image/gif", "image/jpeg", "image/png","image/pjpeg");
	$ajutine = explode(".", $_FILES[$name]["name"]);
	$extension = end($ajutine);

	if ( in_array($_FILES[$name]["type"], $allowedTypes)
		&& ($_FILES[$name]["size"] < 600000)
		&& in_array($extension, $allowedExts)) {
    // fail õiget tüüpi ja suurusega
		if ($_FILES[$name]["error"] > 0) {
			$_SESSION['notices'][]= "Return Code: " . $_FILES[$name]["error"];
			return "";
		} else {
      // vigu ei ole
			if (file_exists("pildid/kasutaja" . $_FILES[$name]["name"])) {
        // fail olemas ära uuesti lae, tagasta failinimi
				$_SESSION['notices'][]= $_FILES[$name]["name"] . " juba eksisteerib. ";
				return "pildid/kasutaja/" .$_FILES[$name]["name"];
			} else {
        // kõik ok, aseta pilt
				move_uploaded_file($_FILES[$name]["tmp_name"], "pildid/kasutaja/" . $_FILES[$name]["name"]);
				return "pildid/kasutaja/" .$_FILES[$name]["name"];
			}
		}
	} else {
		return "";
	}
}

function uploadAuto($name){
	$allowedExts = array("jpg", "jpeg", "gif", "png");
	$allowedTypes = array("image/gif", "image/jpeg", "image/png","image/pjpeg");
	$ajutine = explode(".", $_FILES[$name]["name"]);
	$extension = end($ajutine);

	if ( in_array($_FILES[$name]["type"], $allowedTypes)
		&& ($_FILES[$name]["size"] < 600000)
		&& in_array($extension, $allowedExts)) {
    // fail õiget tüüpi ja suurusega
		if ($_FILES[$name]["error"] > 0) {
			$_SESSION['notices'][]= "Return Code: " . $_FILES[$name]["error"];
			return "";
		} else {
      // vigu ei ole
			if (file_exists("pildid/masin/" . $_FILES[$name]["name"])) {
        // fail olemas ära uuesti lae, tagasta failinimi
				$_SESSION['notices'][]= $_FILES[$name]["name"] . " juba eksisteerib. ";
				return "pildid/masin/" .$_FILES[$name]["name"];
			} else {
        // kõik ok, aseta pilt
				move_uploaded_file($_FILES[$name]["tmp_name"], "pildid/masin/" . $_FILES[$name]["name"]);
				return "pildid/masin/" .$_FILES[$name]["name"];
			}
		}
	} else {
		return "";
	}
}

function hangi_kasutaja($id) {
	global $connection;
	$leiakasutaja="SELECT * FROM srekkaro__kasutaja WHERE id_kasutaja=$id";
	$tulemus=mysqli_query($connection, $leiakasutaja) or die ("Viga andmebaasis -".mysqli_error($connection));
	if (mysqli_num_rows($tulemus)==0) {
		header("Location: ?page=kasutaja");	
	}
	else {
		return mysqli_fetch_assoc($tulemus);
	}	
}

function hangi_auto($id) {
	global $connection;
	$leiakasutaja="SELECT * FROM srekkaro__masin WHERE id=$id";
	$tulemus=mysqli_query($connection, $leiakasutaja) or die ("Viga andmebaasis -".mysqli_error($connection));
	if (mysqli_num_rows($tulemus)==0) {
		header("Location: ?page=autod");	
	}
	else {
		return mysqli_fetch_assoc($tulemus);
	}	
}
?>