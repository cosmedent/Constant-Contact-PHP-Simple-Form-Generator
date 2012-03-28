<?php
require_once('../setup/functions.php');
require_once('../setup/build_functions.php');
include_once('../cc_class.php');
$ccConfigOBJ = new CC_Config();
$ccContactOBJ = new CC_Contact();
$ccListOBJ = new CC_List(); 

	if (!empty($_REQUEST)) {
	
	
		$postFields = array();
		
		// ## PROCESS BASIC FIELDS ## //
		$postFields["email_address"] = $_REQUEST["EmailAddress"];
		$postFields["first_name"] = $_REQUEST["FirstName"];
		$postFields["last_name"] = $_REQUEST["LastName"];
		$postFields["middle_name"] = $_REQUEST["MiddleName"];
		$postFields["company_name"] = $_REQUEST["CompanyName"];
		$postFields["job_title"]= $_REQUEST["JobTitle"];
		$postFields["home_number"] = $_REQUEST["HomePhone"];
		$postFields["work_number"] = $_REQUEST["WorkPhone"];
		$postFields["address_line_1"] = $_REQUEST["Addr1"];
		$postFields["address_line_2"] = $_REQUEST["Addr2"];
		$postFields["address_line_3"] = $_REQUEST["Addr3"];
		$postFields["city_name"] = $_REQUEST["City"];
		$postFields["state_code"] = $_REQUEST["StateCode"];
		$postFields["state_name"] = $_REQUEST["StateName"];
		$postFields["country_code"] = $_REQUEST["CountryCode"];
		$postFields["zip_code"] = $_REQUEST["PostalCode"];
		$postFields["sub_zip_code"] = $_REQUEST["SubPostalCode"];
		$postFields["notes"] = $_REQUEST["Note"];
		$postFields["mail_type"] = $_REQUEST["EmailType"];
		
		$postFields["success_url"] = $_REQUEST["SuccessURL"];
		$postFields["failure_url"] = $_REQUEST["FailureURL"];
		
		$postFields["request_type"] = $_REQUEST["RequestType"];
		
		// ## PROCESS CUSTOM FIELDS ## //
		$postFields["custom_fields"] = array();
		foreach($_REQUEST as $key=>$val) {
			
			if (strncmp($key, 'CustomField', strlen('CustomField')) === 0) {
				$postFields["custom_fields"][substr($key, strlen('CustomField'), strlen($key)-1)] = $val;
			}

		}

		// ## PROCESS LISTS ## //
		$allLists = $ccListOBJ->getLists('', true);	
		foreach ($allLists as $k=>$item) {
			if($_REQUEST['Lists'] && !empty($_REQUEST['Lists'])){
				if (in_array($item['title'],$_REQUEST['Lists'])) {
					$postFields["lists"][] = $item['id'];
				}
			}
			else {
				if (in_array($item['title'],$ccConfigOBJ->contact_lists)) {
					$postFields["lists"][] = $item['id'];
				}
			}
		}
		
		
		$contactXML = $ccContactOBJ->createContactXML(null,$postFields);
		
		$return_code = $ccContactOBJ->addSubscriber($contactXML);
		
		if($postFields['request_type'] == 'ajax'){ $postFields["success_url"]=''; $postFields["failure_url"]=''; }
		
		if ($return_code==201) {
			$error = false;
			if($postFields["success_url"]){	header('Location:'.$postFields["success_url"]); }
			else { echo '<div id="code" title="201"></div><h3>Thank you!</h3><p>Your interest preferences have been successfully recorded.</p>'; }
		} else if ($return_code==409) {
			$error = true;
			if($postFields["failure_url"]){	header('Location:'.$postFields["failure_url"]); }
			else { echo '<div id="code" title="409"></div><h3>We\'re Sorry!</h3><p>It appears that you are already a subscriber of our mailing list.</p>'; }
		} else {
			$error = true;
			if($postFields["failure_url"]){	header('Location:'.$postFields["failure_url"]); }
			else { echo '<div id="code" title="'.$return_code.'"></div><h3>We\'re Sorry!</h3><p>It appears that you were not added to our mailing list. 
			This may be due to one or more of the following reasons:
			<ol>
				<li>You have misspelled your email address</li>
				<li>You did not choose a list to subscribe to</li>
				<li>The system may be busy. Please try again later</li>
			</ol>
			</p>'; }
		}




	}


?>