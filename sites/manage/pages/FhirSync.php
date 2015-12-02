<?php
//test
//use APELON\ihrisFhirSync\ihrisFhirDAO;
//use APELON\ihrisFhirSync\APELON\ihrisFhirSync;

//Dependencies
require './config.values.php'; //CONFIG
require './ihrisFhirDAO.php'; //DATA LAYER LIB
// require './ihris-fhir-sync/src/sync/ihrisSync.php'; //DATA LAYER LIB (GIT REPO)

//Sync-Tool URL
$url = $site_url . "FhirSync.php";

//Config Value Checks
if(!isset($fhir_valueset_countries) 
		|| !isset($fhir_valueset_positions) 
		|| !isset($fhir_valueset_facilities)
		|| !isset($fhir_valueset_county)
		|| !isset($fhir_valueset_regions)
		|| !isset($fhir_valueset_district)
		|| !isset($geography_valueset_map)) {
	alertDanger("The Default ValueSet was not set in config.values.php");
} 

$is = new ihrisFhirDAO();

//Authenticate MySQL & FHIR
$mysql_auth = parse_url($i2ce_site_dsn); //TODO: Fix localhost in MySQL Auth
$mysql_check = $is->setMysqlConnection('localhost', $mysql_auth['user'], $mysql_auth['pass'], substr($mysql_auth['path'], 1));
$fhir_check = $is->setFhirServer($fhir_server_url, $fhir_server_username, $fhir_server_password);

// if(!$fhir_check) {
// 	alertDanger("FHIR Connection failed. Please verify credentials in config.values.php");
// }
if($mysql_check==false) {
	alertDanger("MySQL Connection failed. Please verify credentials in config.values.php");
}

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
		echo '<li><div class="alert alert-warning" role="alert">Facilities</div>';
			formStart(); button('Browse iHRIS Facilities', 'browseFacilities', 'primary');formFinish();
			formStart(); button('Drop Facilities', 'dropFacilities', 'danger');formFinish();
			formStart(); 
				button('Populate Facilities', 'syncFacilities', 'success');
				valuesetTextbox("Facilities", $fhir_valueset_facilities);
			formFinish();
		echo '</li><br>';
		echo '<li><div class="alert alert-warning" role="alert">Healthcare-Worker Types</div>';formFinish();
			formStart(); button('Browse iHRIS Positions', 'browsePositions', 'primary');formFinish();
			formStart(); button('Drop Positions', 'dropPositions', 'danger');formFinish();
			formStart(); 
				button('Populate Positions', 'syncPositions', 'success');
				valuesetTextbox("Positions", $fhir_valueset_positions);
			formFinish();
		echo '</li><br>';
		echo '<li><div class="alert alert-warning" role="alert">Countries</div>';
		formStart(); button('Browse iHRIS Countries', 'browseCountries', 'primary');formFinish();
		formStart(); button('Drop Countries', 'dropCountries', 'danger');formFinish();
		formStart();
			button('Populate Countries', 'syncCountries', 'success');
			valuesetTextbox("Country", $fhir_valueset_countries);
		formFinish();
		echo '</li><br>';
		echo '<li><div class="alert alert-warning" role="alert">Regions</div>';formFinish();
			formStart(); button('Browse iHRIS Regions', 'browseRegions', 'primary');formFinish();
			formStart(); button('Drop Regions', 'dropRegions', 'danger');formFinish();
			formStart();
				button('Populate Regions', 'syncRegions', 'success');
				valuesetTextbox("Regions", $fhir_valueset_regions);
			formFinish();
		echo '</li><br>';
		echo '<li><div class="alert alert-warning" role="alert">Districts</div>';formFinish();
		formStart(); button('Browse iHRIS Districts', 'browseDistricts', 'primary');formFinish();
		formStart(); button('Drop Districts', 'dropDistricts', 'danger');formFinish();
		formStart();
			button('Populate Districts', 'syncDistricts', 'success');
			valuesetTextbox("Districts", $fhir_valueset_districts);
		formFinish();
		echo '</li><br>';
		echo '<li><div class="alert alert-warning" role="alert">Counties</div>';formFinish();
		formStart(); button('Browse iHRIS Counties', 'browseCounties', 'primary');formFinish();
		formStart(); button('Drop Counties', 'dropCounties', 'danger');formFinish();
		formStart();
			button('Populate Counties', 'syncCounties', 'success');
			valuesetTextbox("Counties", $fhir_valueset_counties);
		formFinish();
		echo '</li><br>';
	echo '</ul>';
} else if(isset($_POST['action'])) {
	
	$action = $_POST['action'];
	
	if(isset($_POST['valueset'])) {
		if($_POST['valueset'] != "") {
			$valueset = $_POST['valueset'];
		} else {
			$valueset = $_POST['defaultValueset'];
		}
		alert('ValueSet: "' . $valueset . '"<br>');
	} else {
		$valueset = "";
		if(substr($action, 0, 4) == 'sync') {
			alertDanger("Value-Set was Null. Set VS in Config or VS Text-Box.");
		}
	}
	
	alert("ACTION: " . $action); //DEBUG
	
	switch($action) {
		//BROWSE
		case 'browseCountries':
			echo 'Browse Countries<br>';
			browseCountries();
		break;
		case 'browseRegions':
			echo 'Browse Regions<br>';
			browseRegions();
		break;
		case 'browseDistricts':
			echo 'Browse Districts<br>';
			browseDistricts();
		break;
		case 'browseCounties':
			echo 'Browse Counties<br>';
			browseCounties();
		break;
		case 'browsePositions':
			echo 'Browse Positions<br>';
			browsePositions();
		break;
		case 'browseFacilities':
			echo 'Browse Facilities<br>';
			browseFacilities();
		break;
		//DROP
		case 'dropPositions':
			if($is->dropPosition()) {
				alert('Drop Positions');
			} else {
				alertDanger('Drop Positions Failed');
			}
		break;
		case 'dropFacilities':
			if($is->dropFacility()) {
				alert('Drop Facilities OK!');
			} else {
				alertDanger('Drop Facilities Failed');
			}
		break;
		case 'dropCountries':
			if($is->dropCountry()) {
				alert('Drop Countries OK!');
			} else {
				alertDanger('Drop Countries Failed');
			}
		break;
		case 'dropRegions':
			if($is->dropRegions()) {
				alert('Drop Regions OK!');
			} else {
				alertDanger('Drop Regions Failed');
			}
		break;
		case 'dropDistricts':
			if($is->dropDistricts()) {
				alert('Drop Districts OK!');
			} else {
				alertDanger('Drop Districts Failed');
			}
		break;
		case 'dropCounties':
			if($is->dropCounties()) {
				alert('Drop Counties OK!');
			} else {
				alertDanger('Drop Counties Failed');
			}
		break;
		//SYNC
		case 'syncCountries':
			$insertCountyResult = $is->insertCountry($valueset);
			if($insertCountryResult) {
				if($insertCountyResult != 'INVALID-VALUESET') {
					alert('Sync was OK!');
				} else {
					alertDanger('Sync Failed. Valueset "' . $valueset . '" does not exist');
				}
			} else {
				alertDanger('Sync Failed');
			}
		break;
		case 'syncRegions':
			if($is->insertRegions($valueset)) {
				alert('Sync was OK!');
			} else {
				alertDanger('Sync Failed');
			}
		break;
		case 'syncDistricts':
			if($is->insertDistricts($valueset)) {
				alert('Sync was OK!');
			} else {
				alertDanger('Sync Failed');
			}
		break;
		case 'syncCounties':
			if($is->insertCounties($valueset)) {
				alert('Sync was OK!');
			} else {
				alertDanger('Sync Failed');
			}
		break;
		case 'syncFacilities':
			if($is->insertFacility($valueset)) {
				alert('Sync OK!');
			} else {
				alertDanger('Sync Failed');
			}
		break;
		case 'syncPositions':
			if($is->insertPosition($valueset)) {
				alert('Sync OK!');
			} else {
				alerDangert('Sync Failed');
			}
		break;
		
		default:
			echo 'No vaiable action was set';
		break;
	}
}

//BROWSE
function browsePositions() {
	$table_columns = array(0=>"id",1=>"parent",2=>"i2ce_hidden",3=>"i2ce_hidden",4=>"name");
	startTable($table_columns);
	global $is;
	foreach($is->fetchPositions() as $row) {
		echo '<tr>';
			foreach($table_columns as $tc) {
				echo '<td>' . $row[$tc] . '</td>';
			}
		echo'</tr>';
	}
	echo '<table>';
}

function browseFacilities() {
	$table_columns = array(0=>"id",1=>"parent",2=>"last_modified",3=>"i2ce_hidden",4=>"name");
	startTable($table_columns);
	global $is;
	foreach($is->fetchFacilities() as $row) {
		echo '<tr>';
			foreach($table_columns as $tc) {
				echo '<td>' . $row[$tc] . '</td>';
			}
		echo'</tr>';
	}
	echo '<table>';
}

function browseCountries() {
	$table_columns = array(0=>"id",1=>"parent",2=>"last_modified",3=>"i2ce_hidden",4=>"name", 5=>"alpha_two",6=>"code",7=>"primary",8=>"location");
	startTable($table_columns);
	global $is;
	foreach($is->fetchCountries() as $row) {
		echo '<tr>';
			foreach($table_columns as $tc) {
				echo '<td>' . $row[$tc] . '</td>';
			}
		echo'</tr>';
	}
	
	echo '<table>';
}

function browseRegions() {
	$table_columns = array(0=>"id",1=>"parent",2=>"last_modified",3=>"i2ce_hidden",4=>"name", 5=>"alpha_two",6=>"code",7=>"primary",8=>"location");
	startTable($table_columns);
	global $is;
	foreach($is->fetchRegions() as $row) {
		echo '<tr>';
		foreach($table_columns as $tc) {
			echo '<td>' . $row[$tc] . '</td>';
		}
		echo'</tr>';
	}

	echo '<table>';
}

function browseDistricts() {
	$table_columns = array(0=>"id",1=>"parent",2=>"last_modified",3=>"i2ce_hidden",4=>"name", 5=>"alpha_two",6=>"code",7=>"primary",8=>"location");
	startTable($table_columns);
	global $is;
	foreach($is->fetchDistricts() as $row) {
		echo '<tr>';
		foreach($table_columns as $tc) {
			echo '<td>' . $row[$tc] . '</td>';
		}
		echo'</tr>';
	}

	echo '<table>';
}

function browseCounty() {
	$table_columns = array(0=>"id",1=>"parent",2=>"last_modified",3=>"i2ce_hidden",4=>"name", 5=>"alpha_two",6=>"code",7=>"primary",8=>"location");
	startTable($table_columns);
	global $is;
	foreach($is->fetchDistricts() as $row) {
		echo '<tr>';
		foreach($table_columns as $tc) {
			echo '<td>' . $row[$tc] . '</td>';
		}
		echo'</tr>';
	}

	echo '<table>';
}

function startTable($columns) {
	echo '<table border="1" padding="4"><tr>';
		for($x = 0; $x < count($columns); $x++) {
			echo '<td>' . $columns[$x] . '</td>';
		}
	echo '</tr>';
}

echo '</div></body></html>';

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:

