<?php 
include "lib.php"; 

$conn="";
$key = isset($_GET["key"]) ? $_GET["key"] : '-1';

if(checkAdminKey($key)){
	$_SESSION['logged']=true;
	$user_type = 'ADMIN';
	setcookie('phone','9445321790');
	header("Location: index.php?key=".$key);
	exit();
}

$paid = 0;
$name = "";

function athenticate_mobile()
{
	global $conn,$phone,$key,$user_type,$name;
	
	$conn=dbConnect($conn);

	$key = sanitiseInput($conn,$key);
	
	startSqlTxn($conn);

	try {
		$sql = "SELECT name,phone,user_type,lang from user where imei='$key'";

		//$sql = "SELECT name,phone,imei,paid,user_type from user where concat(imei,encryption)='$key'";
	
		if(($result = mysqli_query($conn, $sql))){
				$count = mysqli_num_rows($result);

				if($count==1){
					while($row = $result->fetch_assoc()) {
						$name = $row["name"];
						$phone = $row["phone"];
						$user_type = $row["user_type"];
						$user_lang = isset($row["lang"]) ? $row["lang"] : '';
					}
					
					$sql = "UPDATE user SET last_login=CONVERT_TZ(LOCALTIMESTAMP(),'+00:00','+05:30') where phone=$phone";
					mysqli_query($conn, $sql);
					
					$_SESSION['logged']=true;
					$_SESSION['name']=$name;
					$_SESSION['phone']=$phone;
					$_SESSION['user_type']=$user_type;
					if(!empty($user_lang)){
						$_SESSION['language'] = $user_lang;
						setcookie('language', $user_lang, time() + (86400 * 365), "/");
					}
					
					if(strcmp($user_type,"FREE")==0){  //________FREE user
						echo $name . "|" . $phone  . "|" . $user_type;
						header("HTTP/1.0 201");
					}
					else{ 								//________PAID/ADMIN user
						echo $name . "|" . $phone  . "|" . $user_type;
						header("HTTP/1.0 200");
						
					}
				}elseif($count==0) {									//________New user, register
					echo "<BR>INFO: Please register your account";
					header("HTTP/1.0 202");
				}
				else{
					echo "<BR>ERROR: Multiple records, please contact admin";
					header("HTTP/1.0 500");
				}
		} else{											//________System error
				echo "<BR>ERROR: please contact admin";
				echo "<BR>ERROR: Could not able to execute $sql. " . mysqli_error($conn);
				header("HTTP/1.0 500");
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
}

//Authenticate the user
athenticate_mobile();

?>
