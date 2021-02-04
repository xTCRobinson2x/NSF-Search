<!DOCTYPE html>
<html>
<head>
	<title>Search Results</title>
</head>


<body>
	<?php

		$AwardNum = $_POST['AwardNum'];
		$PrincipalInvestigator = $_POST['PrincipalInvestigator'];
		$Institution = $_POST['Institution'];
		$SearchBy = $_POST['searchBy'];
		$flag = $_POST['flag'];
	echo "<h1>Search Results</h1><p></p>";
	// https://www.w3schools.com/php/php_mysql_select.asp - Select Data With PDO (+ Prepared Statements) 
	echo "<table style='border: solid 1px black;'>";
	echo "<tr><th>Award ID</th><th>Award Name</th><th>Principal Investigator(s)</th><th>Institution / Sponsor</th></tr>";
	class TableRows extends RecursiveIteratorIterator {
		function __construct($it){
			parent::__construct($it, self::LEAVES_ONLY);
		}

		function current(){
			return "<td style='width:150px;border:1px solid black;'>". parent::current(). "</td>";
		}

		function beginChildren() {
			echo "<tr>";
		}

		function endChildren(){
			echo "</tr>" . "\n";
		}
	}
	//MySQL login
	$serverName = "localhost";
	$username = "root";
	$password = "password";
	$dbname = "project";

	switch ($SearchBy){

		case "AwardNum":
			// Connect to MySQL and run prepared statement - then create an array with the results
			try {
				$conn = new PDO("mysql:host=$serverName;dbname=$dbname",$username,$password);
				$conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
				$stmt = $conn->prepare("Select investigator.Inv_Award_ID, award.Award_Title, concat(investigator.First_Name, ' ',
					investigator.Last_Name) as Investigators, sponsor.`Name` from 
					award inner join investigator on investigator.Inv_Award_ID=award.Award_ID
					inner join sponsor on investigator.Inv_Award_ID=Spon_Award_ID
					Where investigator.Inv_Award_ID LIKE '%$AwardNum%' ORDER BY investigator.Inv_Award_ID ");
				$stmt->execute();
				$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
				$array = $stmt->fetchAll();
				

				//https://stackoverflow.com/questions/23788105/how-to-add-link-in-to-array-value -- array_push
				//https://stackoverflow.com/questions/52229108/php-update-variable-when-link-is-clicked/52229499 -- adding value to link
				$linkCount = 0;
				while($linkCount<sizeof($array)){
					$pushThis = $array[$linkCount]['Inv_Award_ID'];
					array_push($array[$linkCount], "<a href='more.php?award_id=$pushThis'>View Award Info</a>");
					$linkCount++;
				}
				//The following loop is used to combine the information of awards that have the same award number. This would
				//be used for combining the names if there were multiple investigators on the same award num
				$i = 0;
				while($i<sizeof($array)-1){
					if(isset($array[$i])){
						if (isset($array[$i+1])){
							if($array[$i]['Inv_Award_ID']==$array[$i+1]['Inv_Award_ID']){
								$array[$i]['Investigators'] = $array[$i]['Investigators'].", ".$array[$i+1]['Investigators'];
								unset($array[$i+1]);
								$j = $i+2;
								while ($j<sizeof($array)-1){
									if($array[$i]['Inv_Award_ID']==$array[$j]['Inv_Award_ID'] ){
										$array[$i]['Investigators'] = $array[$i]['Investigators'].", ".$array[$j]['Investigators'];
										unset($array[$j]);
										$j++;
									}
									else{
										break;
									}
								}
								$i=$j;						
							}
							else{
								$i++;
							}					
						}
						else{
							while($i<sizeof($array)-1){
								if(isset($array[$i+1]) == FALSE){
									$i++;
								}
								else{
									break;
								}
							}
						}
					}
					else{
						$i++;
					}
				
					
				}
				
				
				if ($flag==0){// Write out the array to table

					echo "
						<form actiion = 'testing.php' method = 'post' >
						<input type ='hidden' name='flag' value= 1>
						<input type = 'hidden' name = 'AwardNum' value =$AwardNum>
						<input type = 'hidden' name = 'PrincipalInvestigator' value =$PrincipalInvestigator>
						<input type = 'hidden' name = 'Institution' value =$Institution>
						<input type = 'hidden' name = 'searchBy' value =$SearchBy>
						<input type = 'submit'  value =Sort>
						</form>";

					foreach(new TableRows(new RecursiveArrayIterator($array)) as $k=>$v) {
		    			echo $v;

		  			}
		  		}
		  		elseif ($flag==1){

		  			echo "
						<form actiion = 'testing.php' method = 'post' >
						<input type ='hidden' name='flag' value= 0>
						<input type = 'hidden' name = 'AwardNum' value =$AwardNum>
						<input type = 'hidden' name = 'PrincipalInvestigator' value =$PrincipalInvestigator>
						<input type = 'hidden' name = 'Institution' value =$Institution>
						<input type = 'hidden' name = 'searchBy' value =$SearchBy>
						<input type = 'submit'  value =Sort>
						</form>";
		  			
		  			foreach(new TableRows(new RecursiveArrayIterator(array_reverse($array))) as $k=>$v) {
		    			echo $v;

		  			}
		  		}
			}
				//Connection Error?
				catch (PDOException $e) {
					echo "Error: " . $e->getMessage();
				}
		break; # end of case AwardNum

	//Same concept as above except search by investigator
	case "PrincipalInvestigator":

			try {
				$conn = new PDO("mysql:host=$serverName;dbname=$dbname",$username,$password);
				$conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
				$stmt = $conn->prepare("Select investigator.Inv_Award_ID, award.Award_Title, concat(investigator.First_Name, ' ',
					investigator.Last_Name) as Investigators, sponsor.`Name` from 
					award inner join investigator on investigator.Inv_Award_ID=award.Award_ID
					inner join sponsor on investigator.Inv_Award_ID=Spon_Award_ID
					Where concat(investigator.First_Name, ' ',
					investigator.Last_Name) LIKE ('%$PrincipalInvestigator%') order by concat(investigator.First_name, ' ', investigator.Last_name)");
				$stmt->execute();
				$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
				$array = $stmt->fetchAll();
				$linkCount = 0;
				while($linkCount<sizeof($array)){
					$pushThis = $array[$linkCount]['Inv_Award_ID'];
					array_push($array[$linkCount], "<a href='more.php?award_id=$pushThis'>View Award Info</a>");
					$linkCount++;
				}
				$i = 0;
				while($i<sizeof($array)-1){
					if(isset($array[$i])){
						if (isset($array[$i+1])){
							if($array[$i]['Inv_Award_ID']==$array[$i+1]['Inv_Award_ID']){
								$array[$i]['Investigators'] = $array[$i]['Investigators'].", ".$array[$i+1]['Investigators'];
								unset($array[$i+1]);
								$j = $i+2;
								while ($j<sizeof($array)-1){
									if($array[$i]['Inv_Award_ID']==$array[$j]['Inv_Award_ID'] ){
										$array[$i]['Investigators'] = $array[$i]['Investigators'].", ".$array[$j]['Investigators'];
										unset($array[$j]);
										$j++;
									}
									else{
										break;
									}
								}
								$i=$j;						
							}
							else{
								$i++;
							}					
						}
						else{
							while($i<sizeof($array)-1){
								if(isset($array[$i+1]) == FALSE){
									$i++;
								}
								else{
									break;
								}
							}
						}
					}
					else{
						$i++;
					}		
					
				}	

				
				if ($flag==0){// Write out the array to table

					//Create flag button
					echo "
						<form actiion = 'testing.php' method = 'post' >
						<input type ='hidden' name='flag' value= 1>
						<input type = 'hidden' name = 'AwardNum' value =$AwardNum>
						<input type = 'hidden' name = 'PrincipalInvestigator' value =$PrincipalInvestigator>
						<input type = 'hidden' name = 'Institution' value =$Institution>
						<input type = 'hidden' name = 'searchBy' value =$SearchBy>
						<input type = 'submit'  value =Sort>
						</form>";

					foreach(new TableRows(new RecursiveArrayIterator($array)) as $k=>$v) {
		    			echo $v;

		  			}
		  		}
		  		elseif ($flag==1){

		  			echo "
						<form actiion = 'testing.php' method = 'post' >
						<input type ='hidden' name='flag' value= 0>
						<input type = 'hidden' name = 'AwardNum' value =$AwardNum>
						<input type = 'hidden' name = 'PrincipalInvestigator' value =$PrincipalInvestigator>
						<input type = 'hidden' name = 'Institution' value =$Institution>
						<input type = 'hidden' name = 'searchBy' value =$SearchBy>
						<input type = 'submit'  value =Sort>
						</form>";
		  			
		  			foreach(new TableRows(new RecursiveArrayIterator(array_reverse($array))) as $k=>$v) {
		    			echo $v;

		  			}
		  		}
			}

				catch (PDOException $e) {
					echo "Error: " . $e->getMessage();
				}
		break; # end of case PrincipalInvestigators

		//Same concept as above except search by institution/Organization
		case "Institution":

			try {
				$conn = new PDO("mysql:host=$serverName;dbname=$dbname",$username,$password);
				$conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
				$stmt = $conn->prepare("Select investigator.Inv_Award_ID, award.Award_Title, concat(investigator.First_Name, ' ',
					investigator.Last_Name) as Investigators, sponsor.`Name` from 
					award inner join investigator on investigator.Inv_Award_ID=award.Award_ID
					inner join sponsor on investigator.Inv_Award_ID=Spon_Award_ID
					Where sponsor.`Name` LIKE '%$Institution' ORDER BY sponsor.`Name`");
				$stmt->execute();
				$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
				$array = $stmt->fetchAll();
				$linkCount = 0;
				$linkCount = 0;
				while($linkCount<sizeof($array)){
					$pushThis = $array[$linkCount]['Inv_Award_ID'];
					array_push($array[$linkCount], "<a href='more.php?award_id=$pushThis'>View Award Info</a>");
					$linkCount++;
				}
				$i = 0;
				while($i<sizeof($array)-1){
					if(isset($array[$i])){
						if (isset($array[$i+1])){
							if($array[$i]['Inv_Award_ID']==$array[$i+1]['Inv_Award_ID']){
								$array[$i]['Investigators'] = $array[$i]['Investigators'].", ".$array[$i+1]['Investigators'];
								unset($array[$i+1]);
								$j = $i+2;
								while ($j<sizeof($array)-1){
									if($array[$i]['Inv_Award_ID']==$array[$j]['Inv_Award_ID'] ){
										$array[$i]['Investigators'] = $array[$i]['Investigators'].", ".$array[$j]['Investigators'];
										unset($array[$j]);
										$j++;
									}
									else{
										break;
									}
								}
								$i=$j;						
							}
							else{
								$i++;
							}					
						}
						else{
							while($i<sizeof($array)-1){
								if(isset($array[$i+1]) == FALSE){
									$i++;
								}
								else{
									break;
								}
							}
						}
					}
					else{
						$i++;
					}
				
					
				}	

				
				
				if ($flag==0){// Write out the array to table

					echo "
						<form actiion = 'testing.php' method = 'post' >
						<input type ='hidden' name='flag' value= 1>
						<input type = 'hidden' name = 'AwardNum' value =$AwardNum>
						<input type = 'hidden' name = 'PrincipalInvestigator' value =$PrincipalInvestigator>
						<input type = 'hidden' name = 'Institution' value =$Institution>
						<input type = 'hidden' name = 'searchBy' value =$SearchBy>
						<input type = 'submit'  value =Sort>
						</form>";

					foreach(new TableRows(new RecursiveArrayIterator($array)) as $k=>$v) {
		    			echo $v;

		  			}
		  		}
		  		elseif ($flag==1){

		  			echo "
						<form actiion = 'testing.php' method = 'post' >
						<input type ='hidden' name='flag' value= 0>
						<input type = 'hidden' name = 'AwardNum' value =$AwardNum>
						<input type = 'hidden' name = 'PrincipalInvestigator' value =$PrincipalInvestigator>
						<input type = 'hidden' name = 'Institution' value =$Institution>
						<input type = 'hidden' name = 'searchBy' value =$SearchBy>
						<input type = 'submit'  value =Sort>
						</form>";
		  			
		  			foreach(new TableRows(new RecursiveArrayIterator(array_reverse($array))) as $k=>$v) {
		    			echo $v;

		  			}
		  		}
			}

				catch (PDOException $e) {
					echo "Error: " . $e->getMessage();
				}
		break; # end of case AwardNum


} # Closing for switch



	?>

</body>
</html>