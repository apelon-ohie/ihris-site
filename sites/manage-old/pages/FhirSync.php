<?php

use APELON\ihrisFhirSync\ihrisSync;

//Dependencies
//require './ihrisSync.php'; //Standalone version
require './ihris-fhir-sync/src/sync/ihrisSync.php'; //GIT Version
require './config.values.php';

//Global Variables
$url = $site_url . "FhirSync.php";

//HTML Helper Functions
function button($text, $action, $level = danger) {
	echo '<input type="hidden" id="action" name="action" value="' . $action . '" />';
	echo '<input type="submit" style="display:inline" class="btn btn-' . $level . '" value="' . $text . '" /> &nbsp; ';
}
function alert($text) {
	echo '<div class="alert alert-warning" role="alert">' . $text . '</div>';
}
function valuesetTextbox($name, $valueSet) {
	echo '
		  <div style="display:inline" class="col-md-4">
				<input type="hidden" name="valuesetType" value="' . $name . '" />
			  	<input style="display:inline" id="textinput" name="valueset" type="text" placeholder="Default Value-Set: ' . $valueSet . '" class="form-control input-md">
		  </div>';
}
function formStart() {
	echo '<form style="display:inline" class="form-horizontal" action="' . $url . '" method="post">';
}
function formFinish() {
	echo '</form>';
	}

//HTML Start
//echo '<html><head>';
//require '../templates/en_US/main.html';
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
				formStart(); button('Drop Countries', 'dropCountries');formFinish();
				formStart(); button('Populate Countries', 'syncCountries', 'success');
				valuesetTextbox("Country", "valueset-c80-facilitycodes");formFinish();
				//TODO: Or, get the Value-Set from the Config
		echo '</li><br>';
		echo '<li><div class="alert alert-warning" role="alert">Facilities</div>';
			formStart(); button('Browse iHRIS Facilities', 'browseFacilities', 'primary');formFinish();
			formStart(); button('Drop Facilities', 'dropFacilities');formFinish();
			formStart(); button('Populate Facilities', 'syncFacilities', 'success');
			valuesetTextbox("Country", "valueset-c80-facilitycodes");formFinish();
			//TODO: Or, get the Value-Set from the Config
		echo '</li><br>';
		echo '<li><div class="alert alert-warning" role="alert">Healthcare-Worker Types</div>';formFinish();
			formStart(); button('Browse iHRIS Positions', 'browsePositions', 'primary');formFinish();
			formStart(); button('Drop Positions', 'dropPositions');
			formStart(); button('Populate Positions', 'syncPositions', 'success');
			valuesetTextbox("Country", "HeathCareWorkerTypes");formFinish();
			//TODO: Or, get the Value-Set from the Config
			//Value-Set Form
		echo '</li><br>';
	echo '</ul>';
} else if(isset($_POST['action'])) {
	$action = $_POST['action'];
	$is = new ihrisSync();
	
	//Authenticate MySQL
	$mysql_auth = parse_url($i2ce_site_dsn);
	//print_r($mysql_auth);
	$is->setMysqlConnection('localhost', $mysql_auth['user'], $mysql_auth['pass'], substr($mysql_auth['path'], 1));
	$is->setFhirServer($fhir_server_url, $fhir_server_username, $fhir_server_password);
	
	switch($action) {
		case 'browseCountries':
			browseCountries();
		break;
		case 'dropCountries':
			if($is->dropCountry()) {
				alert('Sync was OK!');
			} else {
				alert('Sync Failed');
			}
		break;
		case 'syncCountries':
			if($is->insertCountry("valueset-c80-facilitycodes")) {
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
			if($is->insertFacility('valueset-c80-facilitycodes')) {
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
			if($is->insertPosition("HeathCareWorkerTypes")) {
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
	$columns = array(0=>"id",1=>"parent",2=>"modified",3=>"hidden",4=>"name", 5=>"alpha_two",6=>"code",7=>"primary",8=>"location");
	startTable($columns);
	while($row = $is->fetchCountries()) {
		echo '<tr>Row-';
		for($x=0;$x<size($row);$x++) {
			print_r($row);
			echo '<td>Cell: ' . $row[$x] . '</td>';
		}
		echo'</tr>';
	}
	echo '<table>';
}

function startTable($columns) {
	echo '<table border="0"><tr>';
		for($x = 0; $x < count($columns); $x++) {
			echo '<td>' . $columns[$x] . '</td>';
		}
	echo '</tr>';
}

echo '</div></body></html>';


