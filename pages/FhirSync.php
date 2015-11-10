<?php

use APELON\ihrisFhirSync\ihrisSync;
require './ihrisSync.php';
require './config.values.php';
$url = "http://hardevhim.ct.apelon.com:83/manage/FhirSync.php";

function button($text, $action, $level = danger) {
	$actionUrl = $url . '?action=';
	echo '<a href="' . $actionUrl . $action . '" class="btn btn-' . $level . '">' . $text . '</a> &nbsp; ';
}
function alert($text) {
	echo '<div class="alert alert-warning" role="alert">' . $text . '</div>';
}

echo '<html><body>';
echo '<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-MfvZlkHCEqatNoGiOXveE8FIwMzZg4W85qfrfIFBfYc= sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">';
echo '<div class="container">';
echo '<h2>Terminology Asset Management';
button("Home", "home", "success");
echo '&nbsp;&nbsp;&nbsp; </h2>';
echo '<hr>';

if(!isset($_GET['action']) || $_GET['action'] == 'home') {
	echo '<ul style="list-style-type: none">';
		echo '<li><div class="alert alert-warning" role="alert">Countries</div>';
			button('Browse Countries', 'browseCountries', 'primary');
			button('Drop Countries', 'dropCountries');
			button('Populate Countries', 'syncCountries', 'success');
		echo '</li><br>';
		echo '<li><div class="alert alert-warning" role="alert">Facilities</div>';
			button('Browse Facilities', 'browseFacilities', 'primary');
			button('Drop Facilities', 'dropFacilities');
			button('Populate Facilities', 'syncFacilities', 'success');
		echo '</li><br>';
		echo '<li><div class="alert alert-warning" role="alert">Healthcare-Worker Types</div>';
			button('Browse Positions', 'browsePositions', 'primary');
			button('Drop Positions', 'dropPositions');
			button('Populate Positions', 'syncPositions', 'success');
		echo '</li><br>';
	echo '</ul>';
} else if(isset($_GET['action'])) {
	$action = $_GET['action'];
	$is = new ihrisSync();
	
	//Authenticate MySQL
	$mysql_auth = parse_url($i2ce_site_dsn);
	//print_r($mysql_auth);
	$is->setMysqlConnection('localhost', $mysql_auth['user'], $mysql_auth['pass'], substr($mysql_auth['path'], 1));
	$is->setFhirServer($fhir_server_url, $fhir_server_username, $fhir_server_password);
	
	switch($action) {
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


echo '</div></body></html>';


