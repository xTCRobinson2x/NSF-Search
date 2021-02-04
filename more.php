<!DOCTYPE html>
<html>
<head>
	<title>Search Results</title>
</head>
<body>
	<?php
		$award_id=$_GET['award_id'];

		echo "<h1>Award Information</h1><p></p>";
		/*
		echo "<table style='border: solid 1px black;'>";
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
	*/


		$serverName = "localhost";
		$username = "root";
		$password = "password";
		$dbname = "project";

		try {
				$conn = new PDO("mysql:host=$serverName;dbname=$dbname",$username,$password);
				$conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
				$stmt = $conn->prepare("Select organization.Long_Name,organization.Abbreviation,award.Min_Amd_Letter_Date,award.Max_Amd_Letter_Date,
										award.Award_ID,award.Award_Instrument,award.Program_Manager,award.Award_Effective_Date,
										award.Award_Expiration_Date,award.Award_Amount, concat(investigator.First_Name,' ',investigator.Last_name, ' ',investigator.Email,' (',investigator.Role_Code,')') as Investigators, 
										sponsor.`Name`,concat(sponsor.Street_Address,' ', sponsor.City_Name,' ', sponsor.State_Code,' ', sponsor.Zip_Code,' ', sponsor.Phone_Number) as sponsor_address,
										program_reference.Program_Reference_Code, concat(program_elements.Program_Element_Code,' -- ',program_elements.Program_Element_Text) as program_element_info
										from award INNER JOIN organization on organization.Org_Award_ID=award.Award_ID INNER JOIN investigator ON award.Award_ID=investigator.Inv_Award_ID 
										INNER JOIN sponsor ON award.Award_ID=sponsor.Spon_Award_ID INNER JOIN program_reference ON award.Award_ID=program_reference.Ref_Award_ID
										INNER JOIN program_elements ON award.Award_ID=program_elements.Elem_Award_ID where award.award_ID=$award_id");
				$stmt->execute();
				$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
				$array = $stmt->fetchAll();
				//The following loop is used to combine the values of the Program_Reference_Code. Multiple rows will be found in the initial query, so we must combine these reults to one string
				$i = 0;
				$originalSize = sizeof($array)-1;
				while($i<$originalSize){
					if(isset($array[$i])){
						if (isset($array[$i+1])){
							if($array[$i]['Investigators']==$array[$i+1]['Investigators']){
								$array[$i]['Program_Reference_Code'] = $array[$i]['Program_Reference_Code'].', '.$array[$i+1]['Program_Reference_Code']; 
								unset($array[$i+1]);
								$j = $i+2;
								while ($j<$originalSize){
									if($array[$i]['Investigators']==$array[$j]['Investigators'] ){
										$array[$i]['Program_Reference_Code'] = $array[$i]['Program_Reference_Code'].', '.$array[$j]['Program_Reference_Code'];
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
							while($i<$originalSize){
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
				// Same thing here-- combining the program element info and investigators to create one array which will hold all of the 
				// information for this award 
				$i=1;
				while ($i<$originalSize+1){
					if(isset($array[$i])){
						if (strpos($array[0]['Investigators'], $array[$i]['Investigators']) !== FALSE){
							if (strpos($array[0]['program_element_info'], $array[$i]['program_element_info']) !== FALSE){
								//If already contains the next program ele then do nothing, else add it to string
							}
							else {
								$array[0]['program_element_info'] = $array[0]['program_element_info'].', '.$array[$i]['program_element_info'];
							}
							unset($array[$i]);
						}
						elseif ($array[0]['Investigators'] != $array[$i]['Investigators']){
							$array[0]['Investigators'] = $array[0]['Investigators'].', '.$array[$i]['Investigators'];
							unset($array[$i]);
						}
					}
				
					$i++;
			}

			// $array[0] should have all the information for the award formatted at this point in the code

				//render organization name to page
				echo "NSF Organization: "; 
				echo $array[0]['Long_Name'];
				echo " (";
				echo $array[0]['Abbreviation'];
				echo ")";
				echo "<p></p>";

				// Initial Amendment Date
				echo "Intial Amendment Date: "; 
				echo $array[0]['Min_Amd_Letter_Date'];
				echo "<p></p>";

				//Latest Amendment Date
				echo "Latest Amendment Date: "; 
				echo $array[0]['Max_Amd_Letter_Date'];
				echo "<p></p>";

				//Award Number
				echo "Award Number: "; 
				echo $array[0]['Award_ID'];
				echo "<p></p>";

				//Award Instrument
				echo "Award Instrument: "; 
				echo $array[0]['Award_Instrument'];
				echo "<p></p>";

				//Program Manager
				echo "Program Manager: "; 
				echo $array[0]['Program_Manager'];
				echo "<p></p>";

				//Start Date
				echo "Start Date: "; 
				echo $array[0]['Award_Effective_Date'];
				echo "<p></p>";

				//$array[0]['Award_Expiration_Date'] = '11/20/2020'; -- test estimated echo
				//End Date
				echo "Expiration Date: "; 
				if ($array[0]['Award_Expiration_Date']>'11/19/2020'){
					echo $array[0]['Award_Expiration_Date'];
					echo " (Estimated)";
				}
				else{
					echo $array[0]['Award_Expiration_Date'];
				}
				echo "<p></p>";

				//Awarded Amount
				echo "Awared Amount: "; 
				echo $array[0]['Award_Amount'];
				echo "<p></p>";	

				//Investigator(s)
				if (strpos($array[0]['Investigators'], ',') !== FALSE){
					echo "Investigator: "; 
				}
				else{
					echo "Investigators: "; 
				}
				echo $array[0]['Investigators'];
				echo "<p></p>";	

				//Sponsor
				echo "Sponsor: "; 
				echo $array[0]['Name'];
				echo " -- ";
				echo $array[0]['sponsor_address'];
				echo "<p></p>";	

				//Program Reference Code
				echo "Program Reference Code: "; 
				echo $array[0]['Program_Reference_Code'];
				echo "<p></p>";	

				//Program Element Info
				echo "Program Element Information: "; 
				echo $array[0]['program_element_info'];
				echo "<p></p>";	



				//Write out the array to table -- used for testing 
			/*	
				foreach(new TableRows(new RecursiveArrayIterator($array)) as $k=>$v) {
					echo $v;
		  		} 
		  	*/
		} //End of Try 

				//Connection Error?
				catch (PDOException $e) {
					echo "Error: " . $e->getMessage();
				}


	?>

</body>
</html>