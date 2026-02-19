<?php 
include_once "lib.php"; 

$g_session_id = session_id();

$conn="";
$phone = isset($_POST["phone"]) ? $_POST["phone"] : "";

$loginpass = isset($_POST["loginpass"]) ? $_POST["loginpass"] : "";
$key = isset($_GET["key"]) ? $_GET["key"] : '-1';

$name = "";
$user_type = "FREE";  /* FREE, PAID, SUPER, ADMIN */
				

function athenticate_web()
{
	global $conn,$phone,$key,$loginpass, $user_type, $g_session_id;
	
	$conn=dbConnect($conn);
	
	$phone = sanitiseInput($conn,$phone);
	$key = sanitiseInput($conn,$key);
	
	startSqlTxn($conn);

	try {
		$sql = "SELECT name,phone,pass,user_type,lang from user where phone='$phone'"; 
	
		if(($result = mysqli_query($conn, $sql))){
				$count = mysqli_num_rows($result);

				if($count==1){
					while($row = $result->fetch_assoc()) {
						$db_password = $row["pass"];
						$name = $row["name"];
						$user_type = $row["user_type"];
						$user_lang = isset($row["lang"]) ? $row["lang"] : '';
					}
					
					if(password_verify($loginpass,$db_password))
					{  
						$sql = "UPDATE user SET last_login=LOCALTIMESTAMP(), imei='$g_session_id' where phone='$phone'";
						
						mysqli_query($conn, $sql);

						$_SESSION['logged']=true;
						$_SESSION['Name']=$name;
						$_SESSION['phone'] = $phone;
						// Set language into session/cookie
						if(!empty($user_lang)){
							$_SESSION['language'] = $user_lang;
							setcookie('language', $user_lang, time() + (86400 * 365), "/");
						}
                        
						setcookie('Name', $name, time() + (86400 * 30), "/"); // 86400 = 1 day
						setcookie('phone', $phone, time() + (86400 * 30), "/"); // 86400 = 1 day
						
						/* If code reaches this point without errors then commit the data in the database */
						commitSqlTxn($conn);
		
						if(validUser($g_session_id)==true){
							
							header( "Refresh:0; url=index.php", true, 303);
						}
						else{
							echo "<script>alert('This user already logged in another browser/system')</script>";
							header( "Refresh:0; url=new_loginPage.php", true, 303);
						}
					}
					else{
						echo "<script>alert('Invalid phone number or password')</script>";
						$_SESSION['phone'] = $phone;
						header( "Refresh:0; url=new_loginPage.php", true, 303);
					}
				}else {
					echo "<script>alert('You do not have access, please register')</script>";
					$_SESSION['phone'] = $phone;
					header( "Refresh:0; url=new_registration.php", true, 303);
				}
		} else{
				$msg = 'ERROR: Could not able to execute athenticate query - ' .  mysqli_error($conn);
				echo "<script>alert('$msg')</script>";
				header("HTTP/1.0 500");
		}
		
		
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
}

function validUser($session_id){
	global $conn, $phone;
	
	$result = false;
	
	$conn=dbConnect($conn);
	
	startSqlTxn($conn);

	try {
		$sql = "SELECT imei from user where phone='$phone' AND imei='$session_id'";
	
		if(($result = mysqli_query($conn, $sql))){
				$count = mysqli_num_rows($result);
		}

		if($count==0){
			$result = false;
		}
		else{
			$result = true;
		}
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	//dbDisconnect($conn);
	
	return $result;
}


//Authenticate the user

athenticate_web();

?>
