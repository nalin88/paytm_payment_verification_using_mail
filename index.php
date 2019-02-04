<?php
/**
 * Author : Nalin Nishant
 * Description : Direct Paytm Wallet Payment Verification with the use of mail without Using API
 *
 */

include 'data.php';


//function to get string between two strings
function get_string_between($string, $start, $end){
  $string = " " . $string;
  $ini = strpos($string, $start);
  if ($ini == 0) return "";
  $ini+= strlen($start);
  $len = strpos($string, $end, $ini) - $ini;
  return substr($string, $ini, $len);
}
	//getting mobile number
	function number($value){
		$result = trim(get_string_between($value, 'Linked to', '</a>'));
		$result = strip_tags($result);
		if (empty($result)) {
			$result = trim(get_string_between($value, 'Mobile No. ', '<'));
			//add more changed value
		}
		return $result;
	}

	//getting transaction id 
	function trnsid($value){
		$result = trim(get_string_between($value, 'Transaction ID : ', '<'));
		$result = strip_tags($result);
		if (empty($result)) {
   		$result = trim(get_string_between($value, 'Payment Identification Number : ', '<'));
			if (empty($result)) {
				$result = trim(get_string_between($value, 'Transaction Id: ', '<'));
			 //add more changed value
			 
		 }
		}
		return $result;
	}

if(isset($_POST['tid']) && isset($_POST['reg']) && isset($_POST['sname'])){

  $transid = trim($_POST['tid']);

  //initializing connection to database
 
	$conn = new mysqli($servername, $db_username, $db_password, $db_name);
	if (mysqli_connect_error()) {
    die("Database connection failed: " . mysqli_connect_error());
}

$sql = "CREATE TABLE IF NOT EXISTS $table (
	`name` varchar(50) NOT NULL,
	`reg` varchar(20) NOT NULL,
	`tid` varchar(20) PRIMARY KEY,
	`date` varchar(50) NOT NULL,
	`amount` float NOT NULL,
	`mob_number` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

if (!$conn->query($sql) === TRUE) {
	echo "Error creating table: " . $conn->error;
} 


  //connect to gmail
  $imapPath = '{imap.gmail.com:993/imap/ssl}INBOX';

  //try to connect
  $inbox = imap_open($imapPath, $username, $password) or die('Cannot connect to Gmail: ' . imap_last_error());

  //search for emails with payment subject => You have received Rs.
  $emails = imap_search($inbox, 'SUBJECT "You have received Rs."');


  //for each found email extract the transaction id
  foreach($emails as $mail){

	$output = array();
	$fullstring = imap_fetchbody($inbox, $mail, 1);
	

	//NOW FETCH MAIL WHICH LINKED WITH YOUR PAYTM ACCOUNT

	$output['mob_number'] = number($fullstring);
	$output['tid'] = trnsid($fullstring);
	$head = imap_headerinfo($inbox, $mail);
	$subject = $head->subject;
	$output['amount'] = get_string_between($subject, 'Rs.', ' ');
	$output['date'] = $head->date;




	/* ---------------------------------------------------------------- */

	$test_amount = round($output['amount']);

	
	
	if($output['tid'] == $transid && $test_amount >= $decided_fee){
	  $sql = "INSERT INTO payment (tid, date, amount, mob_number,name,reg)
	  VALUES ('".$output['tid']."', '".$output['date']."', '".$output['amount']."','".$output['mob_number']."','".$_POST['sname']."','".$_POST['reg']."')";
	  if ($conn->query($sql) === TRUE) {
		  $msg[0] = 'success';
			$msg[1] = 'You have been registered.';
			break;
			
	  } else {
		if (strpos($conn->error, 'Duplicate') !== false) {
			$msg[0] = 'error';
			$msg[1] = 'Already registered.';
		}
		else{
		  $msg[0] = 'error';
		  $msg[1] = $conn->error;
		}
	  }
	  $conn->close();
	}
	else{
	  $msg[0] = 'error';
	  $msg[1] = 'Invalid entry.';
	}

  }

  //imap closing statements
  imap_expunge($inbox);
  imap_close($inbox);

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Paytm Wallet To Wallet Transfer </title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#000000"/>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>
</head>
<body>

<div class="container">
  <h2>Registration form - <small><b> Donate Me For Further Development</b></small></h2>
   <?php
  if(isset($msg[0])){
	if($msg[0] == "success"){
	  $status1 = "success";
	  $status2 = "Success";
	}
	if($msg[0] == "error"){
	  $status1 = "danger";
	  $status2 = "Error";
	}
	echo '<div class="alert alert-'.$status1.'">
  <strong>'.$status2.'!</strong>  '.$msg[1].'
</div>';
  }
  ?>
  <form action="" method="POST">
	<div class="form-group">
	  <label for="email">Name :</label>
		<input type="text" name="sname" placeholder="Name" class="form-control" required>
	</div>
	<div class="form-group">
	  <label for="reg">Registration number :</label>
		<input type="text" name="reg" placeholder="Registration number" class="form-control" required>
	</div>
	<div class="form-group">
	  <label for="tid">PayTM transaction ID :</label>
		<input type="text" name="tid" placeholder="PayTM transcation ID" class="form-control" required>
	</div>
	<button type="submit" class="btn btn-primary">Register</button>
  </form>
</div>

</body>
</html>

