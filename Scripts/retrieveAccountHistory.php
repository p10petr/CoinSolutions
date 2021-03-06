<?php
require_once("config.php");
require_once("db_connect.php");
require_once("authorisation.php");
require_once("postFunctions.php");
checkAuthorised();
$data = json_decode(file_get_contents("php://input"),true);
$con = dbConnect($dbhost, $dbuser, $dbpass, $dbname);
$id = getPostValue(0,"id","int","id",true);
if (mysqli_select_db($con,$dbname)) {
	$sql = "SELECT Id FROM $dbname.User WHERE Id=$id;";
	if ($result=mysqli_query($con,$sql)) {
		// Return the number of rows in result set
		$rowcount = mysqli_num_rows($result);
		if ($rowcount ==0) {
			//User doesn't exist in the DB
			die("User doesn't exist!");
		}
		else if ($rowcount ==1) {
			//User exists in the DB
            $sql = "SELECT Transaction.Cur_amount, Transaction.Cur_type, Transaction.Source_usr_id, Transaction.Target_usr_id, Transaction.State FROM Transaction INNER JOIN User ON User.Id WHERE User.Id = $id AND Transaction.State = 1 AND (User.Id = Transaction.Source_usr_id OR User.Id = Transaction.Target_usr_id);";
            
            if ($res = mysqli_query($con,$sql)) {
                $ar = array();
                while ($row = mysqli_fetch_array($res)) {
                    $obj = new stdClass();
                    $obj->Source_usr_id = $row['Source_usr_id'];
                    $obj->Target_usr_id = $row['Target_usr_id'];
                    $obj->Cur_type = $row['Cur_type'];
                    $obj->Cur_amount = $row['Cur_amount'];
                    $obj->State = $row['State'];
                    array_push($ar,$obj);
                }
                echo json_encode($ar);
            }
		}
		mysqli_free_result($result);
	}
	else {
		echo mysqli_error($con);
	}
}
else {
	echo mysqli_error($con);
}
mysqli_close($con);	
/*echo "<table>";
echo "<tr>";
	echo "<td><b>From</b></td>";
	echo "<td><b>To</b></td>";
	echo "<td><b>Currency</b></td>";
	echo "<td><b>Amount</b></td>";
	echo "<td><b>State</b></td>"; 
for ($i=0; i<sizeof($ar); ++$i) {
	echo "<tr>";
	echo "<td>$ar[$i]['Source_usr_id']</td>";
	echo "<td>$ar[$i]['Target_usr_id']</td>";
	echo "<td>$ar[$i]['Cur_type']</td>";
	echo "<td>$ar[$i]['Cur_amount']</td>";
	echo "<td>$ar[$i]['State']</td>";
	echo "</tr>";
}
echo "</table>";*/
?>