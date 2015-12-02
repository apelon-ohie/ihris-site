<?php

//namespace APELON\ihrisFhirSync;

/**
 * Description of ihrisFhirDAO
 *
 * @author vasili
 */
class ihrisFhirDAO {
    
    private $conn, $dtsServer, $dtsUser, $dtsPassword;
    
    public function __construct() {
		//Construct
    }
    
    
    /**
     * Set the iHRIS MySql backend URL, Username, Password and the databsae 
     * iHRIS is currently using
     * 
     * @param type $url MySQL Server URL
     * @param type $user MySQL Username
     * @param type $password MySQL Password
     * @param type $db MySQL Database Name
     */
    public function setMysqlConnection($url, $user, $password, $db) {
    	
        $this->conn = mysqli_connect($url, $user, $password, $db);
        
        if(!$this->conn) {
		return false;
        } else {
        	return  true;
        }
        //test
    }
    
    /**
     * Set DTS FHIRE Server Info
     * 
     * @param type $url URL of the DTS FHIR Server
     * @param type $username DTS Server Username
     * @param type $password DTS Server Password
     */
    public function setFhirServer($url, $username, $password) {
        $this->dtsServer = $url;
        $this->dtsUser = $username;
        $this->dtsPassword = $password;
        
        //TODO: Add FHIR Credential Check here, return true or false
    }
    
    /**
     * Returns an array of parsed FHIR Data from the value-set passed-in
     * @param type $valueSet DTS FHIR Value-Set name to retreive
     */
    public function getFhirData($valueSet) {
        $url = $this->dtsServer . "ValueSet/" . $valueSet . "/$" . "expand";
        $context = stream_context_create(array(
        'http' => array(
            'header' => "Authorization: Basic " . base64_encode($this->dtsUser . ":" . $this->dtsPassword) . "\r\n"
                )
            )
        );
        try {
        	$xml = file_get_contents($url, false, $context);
        } catch(Exception $e) {
        	return 'INVALID-VALUESET';
        }
        
        if($xml != null && $xml) {
        	$xml = simplexml_load_string($xml);
        	//var_dump($xml->expansion); //DEBUG
        	return $xml;
        } else {
        	return false;		
        }
    }
    
    //COUNTRIES
    public function dropCountry() {
        $sql = "TRUNCATE table hippo_country";
        $query = mysqli_query($this->conn, $sql);
        
        if(!$query) {
        	return false;
        } else {
        	return true;
        }
    }
    private function insertCountryQuery($id, $name, $code) {
        //Country-Code
	$explode = explode(" ", $name);
	$country_only = substr($name, 0, strpos($name, "(")-1);
	if($this->getCountryCode($country_only)) {
		$countryCode = $this->getCountryCode($country_only);	
	}else if(substr($explode[1], 0, 1)=="(") { //Single-Word Country
		$countryCode = strtoupper(substr($name, 0, 2));
	} else { //Multiple-Word Country
        	if(strcasecmp($explode[1], 'and')!=0 &&
		   strcasecmp($explode[1], 'of')!=0 &&
		   strcasecmp($explode[1], 'the')!=0){ //Second Word is not and / of / the
			$firstWord = $explode[0]; $secondWord = $explode[1];
        		$countryCode = strtoupper($firstWord[0] . $secondWord[0]);
		} else {
			$firstWord = $explode[0]; $thirdWord = $explode[2];
			$countryCode = strtoupper($firstWord[0] . $secondWord[0]);
		}
	}	
        
	//TODO: Implement parentheses issue on all Geographic functions
	//	  and discuss possible solutions to finding country code.

        $sql = "INSERT INTO "
                . "`ihris_manage`.`hippo_country` "
                    . "(`id`, "
                    . "`parent`, "
                    . "`last_modified`, "
                    . "`i2ce_hidden`,"
                    . "`name`, "
                    . "`alpha_two`, "
                    . "`code`, "
                    . "`primary`, "
                    . "`location`) "
                . " VALUES ("
                	. "'country|" . $id . "', "
                    . "'NULL', "
                    . "NOW(), "
                    . "'0', "
                    . "'" . mysqli_real_escape_string($this->conn, $name) . "', "
                    . "'" . mysqli_real_escape_string($this->conn, strtoupper($countryCode)) . "', "
                    . "'" . mysqli_real_escape_string($this->conn, $code) . "', "
                    . "'1', "
                    . "'1') ";
            $query = mysqli_query($this->conn, $sql);
            
            echo '<b>' . $sql  . '</b><br>';
            
            if(!$query) {
            	return false;
            	exit();
            } else {
            	return true;
            }
    }
    public function insertCountry($valueSet) {
        $fhirData = $this->getFhirData($valueSet)->expansion->contains;
        if(!$fhirData) {
        	return false;
        }
        $size = iterator_count($fhirData);
        for ($x = 0; $x < $size; $x++) {
			$f = $fhirData[$x];
            echo "Country Inserted: " . $f->display['value'] . " - " .  $f->code['value'] . "<br>";
            $this->insertCountryQuery($x, $f->display['value'], $f->code['value']);
        }
    }
    public function fetchCountries() {
    	$sql = "SELECT * FROM `hippo_country`";
    	$query = mysqli_query($this->conn, $sql);
    	if(mysqli_errno($this->conn)) {
    		echo 'MySQL Query Error, SQL: ' . $sql . ' ERROR: ' . mysqli_error($this->conn);
    		return false;
    	} else {
    		$posts = array();
    		while($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
    			$posts[] = $row;
    		}
    		return  $posts;
    	}
    }
    
    //REGION
    public function dropRegion() {
        $sql = "TRUNCATE table hippo_country";
        $query = mysqli_query($this->conn, $sql);
        
        if(!$query) {
        	return false;
        } else {
        	return true;
        }
    }
    public function insertRegion($valueSet) {
        $fhirData = $this->getFhirData($valueSet);
        if(!$fhirData) {
        	return false;
        }
        foreach($fhirData as $f) {
            if($f->contains != null) { //Verify this works
            ////TODO: insert Region - $this->insertRegionQuery($f->display['value'], $f->display['value']);
            }
        }
    }
    public function fetchRegion() {
    	$sql = "SELECT * FROM `hippo_country`";
    	$query = mysqli_query($this->conn, $sql);
    	if(mysqli_errno($this->conn)) {
    		echo 'MySQL Query Error, SQL: ' . $sql . ' ERROR: ' . mysqli_error($this->conn);
    		return false;
    	} else {
    		$posts = array();
    		while($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
    			$posts[] = $row;
    		}
    		return  $posts;
    	}
    }
    
    //DISTRICT
    public function dropDistrict($valueSet) {
        $sql = "TRUNCATE table hippo_district";
   		$query = mysqli_query($this->conn, $sql);
        
        if(!$query) {
        	return false;
        } else {
        	return true;
        }
    }
    public function insertDistrict() {
        $fhirData = $this->getFhirData($valueSet);
        if(!$fhirData) {
        	return false;
        }
        foreach($fhirData as $f) {
            if($f->contains != null) { //Verify this works
                //$this->insertDistrictQuery($f->display['value'], $f->display['value']); - TODO: District
            }
        }
       
    }
    public function fetchDistricts() {
    	$sql = "SELECT * FROM `hippo_district`";
    	$query = mysqli_query($this->conn, $sql);
    	if(mysqli_errno($this->conn)) {
    		echo 'MySQL Query Error, SQL: ' . $sql . ' ERROR: ' . mysqli_error($this->conn);
    		return false;
    	} else {
    		$posts = array();
    		while($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
    			$posts[] = $row;
    		}
    		return  $posts;
    	}
    }
    
    //COUNTY
    private function insertCountyQuery($id, $name, $district) {
    	    	$sql = "INSERT INTO "
                . "`ihris_manage`.`hippo_county` "
                    . "(`id`, "
                    . "`parent`, "
                    . "`last_modified`, "
                    . "`i2ce_hidden`,"
                    . "`district`,"
                    . "`name` "
                    . ") "
                . " VALUES ("
                	. "'facility|" . $id . "', "
                    . "'NULL', "
                    . "NOW(), "
                    . "'0', "
                    . "'district|" . $district . "',"
                    . "'" . mysqli_real_escape_string($this->conn, $name) . "') ";
    	    	
    	$query = mysqli_query($this->conn, $sql);
    	 
    	if(!$query) {
    		return false;
    	} else {
    		return true;
    	}
    }
    public function dropCounty() {
        $sql = "TRUNCATE table hippo_county";
        $query = mysqli_query($this->conn, $sql);
        
        if(!$query) {
        	return false;
        } else {
        	return true;
        }
    }
    /**
     * You can create a set of County's based on a Value Set and a District ID (that the County's belong to)
     * @param unknown $valueSet to create the County's from
     * @param unknown $districtId of all the County's being created here
     */
    public function insertCounty($valueSet, $districtId) {
	    $fhirData = $this->getFhirData($valueSet)->expansion->contains;
	    if(!$fhirData) {
	    	return false;
	    }
        $size = iterator_count($fhirData);
        for ($x = 0; $x < $size; $x++) {
			$f = $fhirData[$x];
			echo "County Inserted: " . $f->display['value'] . " - " .  $f->code['value'] . "<br>";
            $this->insertFacilityQuery($x, $f->display['value'], $districtId); 
        }
    }
    public function fetchCounty() {
    	$sql = "SELECT * FROM `hippo_country`";
    	$query = mysqli_query($this->conn, $sql);
    	if(mysqli_errno($this->conn)) {
    		echo 'MySQL Query Error, SQL: ' . $sql . ' ERROR: ' . mysqli_error($this->conn);
    		return false;
    	} else {
    		$posts = array();
    		while($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
    			$posts[] = $row;
    		}
    		return  $posts;
    	}
    }
    
    //FACILITY
    private function insertFacilityQuery($id, $name) {
    	$sql = "INSERT INTO "
                . "`ihris_manage`.`hippo_facility_type` "
                    . "(`id`, "
                    . "`parent`, "
                    . "`last_modified`, "
                    . "`i2ce_hidden`,"
                    . "`name` "
                    . ") "
                . " VALUES ("
                	. "'facility|" . $id . "', "
                    . "'NULL', "
                    . "NOW(), "
                    . "'0', "
                    . "'" . mysqli_real_escape_string($this->conn, $name) . "') ";
    	$query = mysqli_query($this->conn, $sql);
    	
    	if(!$query) {
    		return false;
    	} else {
    		return true;
    	}
    }
    public function dropFacility() {
        $sql = "TRUNCATE table hippo_facility_type";
    	$query = mysqli_query($this->conn, $sql);
        
        if(!$query) {
        	return false;
        } else {
        	return true;
        }
    }
    public function insertFacility($valueSet) {
   		$fhirData = $this->getFhirData($valueSet)->expansion->contains;
   		if(!$fhirData) {
   			return false;
   		}
        $size = iterator_count($fhirData);
        for ($x = 0; $x < $size; $x++) {
			$f = $fhirData[$x];
			echo "Facility Inserted: " . $f->display['value'] . " - " .  $f->code['value'] . "<br>";
            $this->insertFacilityQuery($x, $f->display['value']);
        }
    }
    public function fetchFacilities() {
    	$sql = "SELECT * FROM hippo_facility_type";
    	$query = mysqli_query($this->conn, $sql);
    	if(mysqli_errno($this->conn)) {
    		echo 'MySQL Query Error, SQL: ' . $sql . ' ERROR: ' . mysqli_error($this->conn);
    		return false;
    	} else {
    		$posts = array();
    		while($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
    			$posts[] = $row;
    		}
    		return  $posts;
    	}
    	
    }
    
    //POSITIONS
    public function fetchPositions() {
    	$sql = "SELECT * FROM hippo_position_type";
    	$query = mysqli_query($this->conn, $sql);
    	if(mysqli_errno($this->conn)) {
    		echo 'MySQL Query Error, SQL: ' . $sql . ' ERROR: ' . mysqli_error($this->conn);
    		return false;
    	} else {
    		$posts = array();
    		while($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
    			$posts[] = $row;
    		}
    		return  $posts;
    	}
    }
    private function insertPositionQuery($id, $name) {
    	$sql = "INSERT INTO "
                . "`ihris_manage`.`hippo_position_type` "
                    . "(`id`, "
                    . "`parent`, "
                    . "`last_modified`, "
                    . "`i2ce_hidden`,"
                    . "`name` "
                    . ") "
                . " VALUES ("
                	. "'position|" . $id . "', "
                    . "'NULL', "
                    . "NOW(), "
                    . "'0', "
                    . "'" . mysqli_real_escape_string($this->conn, $name) . "') ";
    	$query = mysqli_query($this->conn, $sql);
    	 
    	if(!$query) {
    		return false;
    	} else {
    		return true;
    	}
    }
    public function dropPosition() {
        $sql = "TRUNCATE table `hippo_position_type`";
    	$query = mysqli_query($this->conn, $sql);
        if(!$query) {
        	return false;
        } else {
        	return true;
        }
    }
    public function insertPosition($valueSet) {
    	$fhirData = $this->getFhirData($valueSet)->expansion->contains;
    	if(!$fhirData) {
    		return false;
    	}
        $size = iterator_count($fhirData);
        for ($x = 0; $x < $size; $x++) {
			$f = $fhirData[$x];
            echo "Position Inserted: " . $f->display['value'] . " - " .  $f->code['value'] . "<br>";
            $this->insertPositionQuery($x, $f->display['value']);
        }
    }
    
    public function getCountryCode($country) {
        $country_code_map = array(    
		"IM" => "Isle of Man",
		"IL" => "Israel",
		"IT" => "Italy",
"JM" => "Jamaica",
"JP" => "Japan",
"JE" => "Jersey",
"JT" => "Johnston Island",
"JO" => "Jordan",
"KZ" => "Kazakhstan",
"KE" => "Kenya",
"KI" => "Kiribati",
"KW" => "Kuwait",
"KG" => "Kyrgyzstan",
"LA" => "Laos",
"LV" => "Latvia",
"LB" => "Lebanon",
"LS" => "Lesotho",
"LR" => "Liberia",
"LY" => "Libya",
"LI" => "Liechtenstein",
"LT" => "Lithuania",
"LU" => "Luxembourg",
"MO" => "Macau SAR China",
"MK" => "Macedonia",
"MG" => "Madagascar",
"MW" => "Malawi",
"MY" => "Malaysia",
"MV" => "Maldives",
"ML" => "Mali",
"MT" => "Malta",
"MH" => "Marshall Islands",
"MQ" => "Martinique",
"MR" => "Mauritania",
"MU" => "Mauritius",
"YT" => "Mayotte",
"FX" => "Metropolitan France",
"MX" => "Mexico",
"FM" => "Micronesia",
"MI" => "Midway Islands",
"MD" => "Moldova",
"MC" => "Monaco",
"MN" => "Mongolia",
"ME" => "Montenegro",
"MS" => "Montserrat",
"MA" => "Morocco",
"MZ" => "Mozambique",
"MM" => "Myanmar [Burma]",
"NA" => "Namibia",
"NR" => "Nauru",
"NP" => "Nepal",
"NL" => "Netherlands",
"AN" => "Netherlands Antilles",
"NT" => "Neutral Zone",
"NC" => "New Caledonia",
"NZ" => "New Zealand",
"NI" => "Nicaragua",
"NE" => "Niger",
"NG" => "Nigeria",
"NU" => "Niue",
"NF" => "Norfolk Island",
"KP" => "North Korea",
"VD" => "North Vietnam",
"MP" => "Northern Mariana Islands",
"NO" => "Norway",
"OM" => "Oman",
"PC" => "Pacific Islands Trust Territory",
"PK" => "Pakistan",
"PW" => "Palau",
"PS" => "Palestinian Territories",
"PA" => "Panama",
"PZ" => "Panama Canal Zone",
"PG" => "Papua New Guinea",
"PY" => "Paraguay",
"YD" => "People's Democratic Republic of Yemen",
"PE" => "Peru",
"PH" => "Philippines",
"PN" => "Pitcairn Islands",
"PL" => "Poland",
"PT" => "Portugal",
"PR" => "Puerto Rico",
"QA" => "Qatar",
"RO" => "Romania",
"RU" => "Russia",
"RW" => "Rwanda",
"RE" => "Réunion",
"BL" => "Saint Barthélemy",
"SH" => "Saint Helena",
"KN" => "Saint Kitts and Nevis",
"LC" => "Saint Lucia",
"MF" => "Saint Martin",
"PM" => "Saint Pierre and Miquelon",
"VC" => "Saint Vincent and the Grenadines",
"WS" => "Samoa",
"SM" => "San Marino",
"SA" => "Saudi Arabia",
"SN" => "Senegal",
"RS" => "Serbia",
"CS" => "Serbia and Montenegro",
"SC" => "Seychelles",
"SL" => "Sierra Leone",
"SG" => "Singapore",
"SK" => "Slovakia",
"SI" => "Slovenia",
"SB" => "Solomon Islands",
"SO" => "Somalia",
"ZA" => "South Africa",
"GS" => "South Georgia and the South Sandwich Islands",
"KR" => "South Korea",
"ES" => "Spain",
"LK" => "Sri Lanka",
"SD" => "Sudan",
"SR" => "Suriname",
"SJ" => "Svalbard and Jan Mayen",
"SZ" => "Swaziland",
"SE" => "Sweden",
"CH" => "Switzerland",
"SY" => "Syria",
"ST" => "São Tomé and Príncipe",
"TW" => "Taiwan",
"TJ" => "Tajikistan",
"TZ" => "Tanzania",
"TH" => "Thailand",
"TL" => "Timor-Leste",
"TG" => "Togo",
"TK" => "Tokelau",
"TO" => "Tonga",
"TT" => "Trinidad and Tobago",
"TN" => "Tunisia",
"TR" => "Turkey",
"TM" => "Turkmenistan",
"TC" => "Turks and Caicos Islands",
"TV" => "Tuvalu",
"UM" => "U.S. Minor Outlying Islands",
"PU" => "U.S. Miscellaneous Pacific Islands",
"VI" => "U.S. Virgin Islands",
"UG" => "Uganda",
"UA" => "Ukraine",
"SU" => "Union of Soviet Socialist Republics",
"AE" => "United Arab Emirates",
"GB" => "United Kingdom",
"US" => "United States",
"ZZ" => "Unknown or Invalid Region",
"UY" => "Uruguay",
"UZ" => "Uzbekistan",
"VU" => "Vanuatu",
"VA" => "Vatican City",
"VE" => "Venezuela",
"VN" => "Vietnam",
		"WK" => "Wake Island",
		"WF" => "Wallis and Futuna",
		"EH" => "Western Sahara",
		"YE" => "Yemen",
		"ZM" => "Zambia",
		"ZW" => "Zimbabwe",
		"AX" => "Åland Islands",
		);

		$search = array_search($country, $country_code_map);
		if($search) {
			return $search;
		} else {
			return false;
		}
    }     
}


