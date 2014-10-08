<?php
class HL7
{

function create_MSH($X12info,$compEleSep) {

	$MSH	 =	array();

	$MSH[0] = "MSH";							
	
	$MSH[1] = "^~\&";								
	
    $MSH[2] = " " ;		  
	
	$MSH[3] = "GA0000";								
												
	$MSH[4] = " ";		
	
	$MSH[5] = "VAERS PROCESSOR";				
	
	$MSH[6] = "20010331605";		
	
	$MSH[7] = " ";				
	
	$MSH[8] = "ORU";		
	
	$MSH[9] = "^";		
	
	$MSH[10] = "RO1";		
	
	$MSH[11] = "20010422GA03";								
	
	$MSH[12] = "T";			
	
	$MSH[13] = "2.3.1";		
	
	$MSH[14] = " ";				  
	
	$MSH[15] =  " ";			
	
	$MSH['Created'] = "|";		

	return trim($MSH['Created']);
	
}

function create_PID($X12info,$compEleSep) {

	$PID	 =	array();

	$PID[0] = "PID";							
	
	$PID[1] = " ";								
	
    $PID[2] =" " ;		  
	
	$PID[3] = "1234^^^^SR~1234-12^^^^LR~00725^^^^MR";								

	$PID[4] = "Doe^John^Fitzgerald^JR^^^L";		
	
	$PID[5] = " ";				
	
	$PID[6] = "20001007";		
	
	$PID[7] = "M";				
	
	$PID[8] = "2106-3^White^HL70005";		
	
	$PID[9] = "123 Peachtree St^APT 3B^Atlanta^GA^30210^^M^^GA067";		
	
	$PID[10] = "(678) 555-1212^^PRN";				
	
	$PID['Created'] = "|";		

	return trim($PID['Created']);
	
}

function create_ORC($X12info,$compEleSep) {


	$ORC	 =	array();

	$ORC[0] = "ORC";							
	
	$ORC[1] = "CN";								
	
    $ORC[2] =" " ;		  
	
	$ORC[3] = " ";								

	$ORC[4] = " ";		
	
	$ORC[5] = " ";				
	
	$ORC[6] = " ";		
	
	$ORC[7] = " ";				
	
	$ORC[8] = " ";		
	
	$ORC[9] = " ";		
	
	$ORC[10] = " ";	
	
	$ORC[11] = " ";			
	
	$ORC[12] = "1234567^Welby^Marcus^J^Jr^Dr.^MD^L";
	
	$ORC[13] = " ";
	
	$ORC[14] = " ";
	
	$ORC[15] = " ";
	
	$ORC[16] = " ";
	
	$ORC[17] = " ";
	
	$ORC[18] = " ";
	
	$ORC[19] = " ";
	
	$ORC[20] = " ";
	
	$ORC[21] = "Peachtree Clinic";
	
	$ORC[22] = "101 Main Street^^Atlanta^GA^38765^^O^^GA121";
	
	$ORC[23] = "(404) 554-9097^^WPN";
	
	$ORC[24] = "101 Main Street^^Atlanta^GA^38765^^O^^GA121";

	$ORC['Created'] = "|";		

	return trim($ORC['Created']);
	
}

function create_RXA($X12info,$compEleSep) {

	$RXA	 =	array();

	$RXA[0] = "RXA";							
	
	$RXA[1] = "1";								
	
    $RXA[2] ="19910907" ;		  
	
	$RXA[3] = "19910907";								

	$RXA[4] = "03^MMR^CVX";		
	
	$RXA[5] = ".5";				
	
	$RXA[6] = "ML^^ISO+";		
	
	$RXA[7] = " ";				
	
	$RXA[8] = " ";		
	
	$RXA[9] = "1234567890^SMITH^SALLY^S^^^^^^^^^VEI~1234567891^O=BRIAN^ROBERT^A^^DR^MD^^^^^^OEI";		
	
	$RXA[10] = "^^^CHILD HEALTHCARE CLINIC^^^^^101 MAIN STREET^^BOSTON^MA";	
	
	$RXA[11] = " ";			
	
	$RXA[12] = " ";
	
	$RXA[13] = " ";
	
	$RXA[14] = "W2348796456";
	
	$RXA[15] = "19920731";
	
	$RXA[16] = "MSD^MERCK^MVX";

	$RXA['Created'] = "|";		

	return trim($RXA['Created']);
	
}

}