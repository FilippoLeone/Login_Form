<?php
	// ** Start of Initializing the external connection ** //
	class DBConnection {
	public static function DataBaseConnect() {
	try {
		// * Start of External DB Connection * //    
			$DB_HOST = 'localhost';
			$DB_ID = 'root';
			$DB_PW = '';
			$DB_NAME = 'account';
			// * End of External DB Connection * //
			$conn = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8;", $DB_ID);
			// set the PDO error mode to exception
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			echo "<b>You are now connected to the MySQL Database.</b> <br>"; 
			return $conn;
		}
		catch(PDOException $e)
			{
				echo "Connection failed: " . $e->getMessage();
				return $conn=null;
			}	
	} // * Closing DataBaseConnect * //
		
	// * Initializing Password Hashing * //
	public static function SaltThisPassword() {
		$hash = password_hash($_POST['pwd'], PASSWORD_DEFAULT);
		return $hash;
	}
	// * End of Password Hash * // 
} // * Closing DBConnection class * //



// * Query handler start * //
	class ExecuteQuery {
		public static function RegistrationQuery() {
			$my_temp_connection = new DBConnection();
			$connect = $my_temp_connection->DataBaseConnect();
			$stmt = $connect->prepare("INSERT INTO account(Name, Email, Pwd) VALUES (:fullname, :email, :password)");
			$stmt->bindParam(':fullname', $_POST['fullname']);
			$stmt->bindParam(':email', $_POST['email']);
			$Pwd = $my_temp_connection->SaltThisPassword();
			$stmt->bindParam(':password', $Pwd);
			echo "<br><br> Hash:" . $Pwd;
			$stmt->execute();
			$connect = null;		
		}	
		public static function LoginQuery() {
			$my_temp_connection = new DBConnection();
			$connect = $my_temp_connection->DataBaseConnect();
			$hash = $_POST['log-pwd'];
			$stmt = $connect->prepare("SELECT Name,Pwd FROM account WHERE Email=:email");
			$stmt->bindParam(':email', $_POST["log-email"]);
			$stmt->execute();
			$Data = $stmt->fetch(PDO::FETCH_ASSOC);
			$connect = null;
			if (password_verify($hash, $Data["Pwd"])) 
			{
				echo "<br>Login Successful, Welcome: <b>" . $Data["Name"] . "</b>";
			}
			else
			{
				echo '<b>Login failed.</b> Credentials not found in the database.';
			}
		}

	} 
// * Query Handler end * //
	
	

// * Start of Validation for $_POST * //
	class RequestValidation {
		
		public static function PostRequestExists() {
			if (!empty($_POST)){ return true; }
			else { return false; }
		}
		public static function PostRequestExecute() {
		if (isset($_POST['log-email']) && isset($_POST['log-pwd'])) {
			$LoginQuery = new ExecuteQuery();
			$LoginQuery->LoginQuery();
		}
		elseif (isset($_POST['fullname']) && isset($_POST['email']) && isset($_POST['pwd'])) {
			// registration
			//echo "[Debug] POST_REQ DATA: <br>". "name:" . $_POST['fullname'] . "<br>" . "email:" . $_POST['email'] . "pwd:" . $_POST['pwd'];
			$RegistrationQuery = new ExecuteQuery();    
			$RegistrationQuery->RegistrationQuery();
		}
		else { echo "[Debug] POST_REQ DATA: <br>". "name:" . $_POST['fullname'] . "<br>" . "email:" . $_POST['log-email'] . "pwd:" . $_POST['pwd']; }
		}
	} // * RequestValidation Class End * //
	// * If the request is not empty insert records in the database * //
// * $_POST Validation end * //





// ***************** Main Handler Start ***************** //
	$Handler = new RequestValidation();
	if ($Handler->PostRequestExists()){
	$Handler->PostRequestExecute();
	}
	else {
		echo "Something went wrong...";
	}
// ***************** Main Handler End ***************** //



?>