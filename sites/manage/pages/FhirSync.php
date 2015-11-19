<?php

use APELON\ihrisFhirSync\ihrisSync;
use APELON\ihrisFhirSync\APELON\ihrisFhirSync;

//Dependencies
require './ihrisSync.php'; //Standalone version
//require './ihris-fhir-sync/src/sync/ihrisSync.php'; //GIT Version
require './config.values.php';

//Global Variables
$url = $site_url . "FhirSync.php";

//Config Value Checks
if(!isset($fhir_valueset_country) 
		|| !isset($fhir_valueset_positions) 
		|| !isset($fhir_valueset_facilities)
		|| !isset($fhir_valueset_county)
		|| !isset($fhir_valueset_region)
		|| !isset($fhir_valueset_district)) {
	alertDanger("The Default ValueSet was not set in config.values.php");
} 
// $is = new ihrisSync(); TODO: Fhir and DB Check
// $dbCheck = $is->setMysqlConnection('localhost', $mysql_auth['user'], $mysql_auth['pass'], substr($mysql_auth['path'], 1));
// $fhirCheck = $is->setFhirServer($fhir_server_url, $fhir_server_username, $fhir_server_password);
// if(!$dbCheck) {
// 	alertDanger("Could not connect to MySQL DB with configured parameters");
// }
// if(!$fhirCheck) {
// 	alertDanger("Could not connect to FHIR Server with configured parameters");
// }

//HTML Helper Functions
function button($text, $action, $level = danger) {
	echo '<input type="hidden" id="action" name="action" value="' . $action . '" />';
	echo '<input type="submit" style="display:inline" class="btn btn-' . $level . '" value="' . $text . '" /> &nbsp; ';
}
function alert($text) {
	echo '<div class="alert alert-warning" role="alert">' . $text . '</div>';
}
function alertDanger($text) {
	echo '<div class="alert alert-danger" role="alert">' . $text . '</div>';
}
function valuesetTextbox($name, $valueSet) {
	echo '
		  <div style="display:inline" class="col-md-4">
				<input type="hidden" name="valuesetType" value="' . $name . '" />
				<input type="hidden" name="defaultValueset" value="' . $valueSet . '" />
			  	<input style="display:inline" id="textinput" name="valueset" type="text" placeholder="Default Value-Set: ' . $valueSet . '" class="form-control input-md">
		  </div>';
}
function formStart() {
	global $url;
	echo '<form style="display:inline" class="form-horizontal" action="' . $url . '" method="post">';
}
function formFinish() {
	echo '</form>';
}

//HTML Start
echo '<html><head>';
echo '<style type="text/css">
		ul {
			    list-style-type: none;
			}
		</style>';
echo '</head><body>';
echo '<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-MfvZlkHCEqatNoGiOXveE8FIwMzZg4W85qfrfIFBfYc= sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">';
echo '<div class="container">';
echo '<h2>Terminology Asset Management&nbsp;&nbsp;&nbsp;';
echo '<a href="' . $url . '"><small>(Home) </small></a></h2>';
echo '<hr>';

if(!isset($_POST['action'])) {
	echo '<ul style="display:inline" style="list-style-type: none">';
		echo '<li><div class="alert alert-warning" role="alert">Countries</div>';
				formStart(); button('Browse iHRIS Countries', 'browseCountries', 'primary');formFinish();
				formStart(); button('Drop Countries', 'dropCountries', 'danger');formFinish();
				formStart(); button('Populate Countries', 'syncCountries', 'success');
				valuesetTextbox("Country", $fhir_valueset_country);formFinish();
		echo '</li><br>';
		echo '<li><div class="alert alert-warning" role="alert">Facilities</div>';
			formStart(); button('Browse iHRIS Facilities', 'browseFacilities', 'primary');formFinish();
			formStart(); button('Drop Facilities', 'dropFacilities', 'danger');formFinish();
			formStart(); button('Populate Facilities', 'syncFacilities', 'success');
			valuesetTextbox("Country", $fhir_valueset_facilities);formFinish();
		echo '</li><br>';
		echo '<li><div class="alert alert-warning" role="alert">Healthcare-Worker Types</div>';formFinish();
			formStart(); button('Browse iHRIS Positions', 'browsePositions', 'primary');formFinish();
			formStart(); button('Drop Positions', 'dropPositions', 'danger');
			formStart(); button('Populate Positions', 'syncPositions', 'success');
			valuesetTextbox("Country", $fhir_valueset_positions);formFinish();
			//Value-Set Form
		echo '</li><br>';
	echo '</ul>';
} else if(isset($_POST['action'])) {
	$action = $_POST['action'];
	if(isset($_POST['valueset'])) {
		if($_POST['valueset'] != "") {
			$valueset = $_POST['valueset'];
			echo 'ValueSet: "' . $valueset . '"<br>';
		} else {
			$valueset = $_POST['defaultValueset'];
		}
	} else {
		$valueset = $_POST['defaultValueset'];
	}
	$is = new ihrisSync();
	
	//Authenticate MySQL
	$mysql_auth = parse_url($i2ce_site_dsn);
	//print_r($mysql_auth);
	$is->setMysqlConnection('localhost', $mysql_auth['user'], $mysql_auth['pass'], substr($mysql_auth['path'], 1));
	$is->setFhirServer($fhir_server_url, $fhir_server_username, $fhir_server_password);
	
	switch($action) {
		case 'browseCountries':
			echo 'Browse Countries<br>';
			browseCountries();
			//print_r($is->fetchCountries());
		break;
		case 'dropCountries':
			if($is->dropCountry()) {
				alert('Sync was OK!');
			} else {
				alert('Sync Failed');
			}
		break;
		case 'syncCountries':
			if($is->insertCountry($valueset)) {
				alert('Sync was OK!');
			} else {
				alert('Sync Failed');
			}
		break;
			
		case 'dropFacilities':
			if($is->dropFacility()) {
				alert('Sync was OK!');
			} else {
				alert('Sync Failed');
			}
		break;
		case 'syncFacilities':
			if($is->insertFacility($valueset)) {
				alert('Sync was OK!');
			} else {
				alert('Sync Failed');
			}
		break;
		
		case 'dropPositions':
			if($is->dropPosition()) {
				alert('Sync was OK!');
			} else {
				alert('Sync Failed');
			}
		break;
		case 'syncPositions':
			if($is->insertPosition($valueset)) {
				alert('Sync was OK!');
			} else {
				alert('Sync Failed');
			}
		break;
		
		default:
			echo 'No vaiable action was set';
		break;
	}
}

function browseCountries() {
	$table_columns = array(0=>"id",1=>"parent",2=>"modified",3=>"hidden",4=>"name", 5=>"alpha_two",6=>"code",7=>"primary",8=>"location");
	startTable($table_columns);
	global $is;
	$fetch_data = $is->fetchCountries();
	echo 'Count: ' . count($row) . '<br>';
	while($row = $fetch_data) {
		echo '<tr>';
		for($y=0;$y < count($row);$y++) {
			echo '<td>' . $row[$y] . '</td>';
		}
		echo'</tr>';
	}
	echo '<table>';
}

function startTable($columns) {
	echo '<table border="0"><tr>';
	echo 'Count: ' . count($columns) . '<br>';
		for($x = 0; $x < count($columns); $x++) {
			echo '<td>' . $columns[$x] . '</td>';
		}
	echo '</tr>';
}

echo '</div></body></html>';


