<?php

namespace APELON\ihrisFhirSync;


use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Description of ihrisSync
 *
 * @author vasili
 */
class ihrisSync {
    
    private $conn, $dtsServer, $dtsUser, $dtsPassword;
    
    public function __construct() {
    	$this->log = new Logger('fhir-ihris');
    	$this->log->pushHandler(new StreamHandler('./sync.log', Logger::INFO));
    	$this->log->addInfo("Logger Initialized");
    	echo "ihris-sync initialized ok<br>";
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
        
        if(mysqli_connect_errno()) {
        	$this->log->addError("Database Connection Failure: " . mysqli_connect_error());
        	exit();
        } else {
        	$this->log->addInfo("Database OK");
        }
        
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
        	echo "Failure connecting to DTS FHIR Server";
        	$this->log->addError("Failure connecting to DTS FHIR Server");
        	$this->log->addError((String) $e->getMessage() . "<br>" . $e->getTraceAsString());
        }
        
        if($xml != null && $xml) {
        	$this->log->addInfo("FHIE fetch OK");
        	
        	$xml = simplexml_load_string($xml);
        	//var_dump($xml->expansion); //TODO: Remove after testing
        	//$fhir = new SimpleXMLElement($xml);
        	return $xml;
        } else {
        	$this->log->addError("Error Fetching XML using file_get_contents()");		
        }
    }
    
    public function dropCountry() {
        $sql = "TRUNCATE table hippo_country";
        $query = mysqli_query($this->conn, $sql);
        
        if(!$query) {
        	$this->log->addError("Drop Country Query Failed: " . mysqli_error($this->conn));
        	exit();
        } else {
        	$this->log->addInfo("Drop Country Query OK ");
        }
    }
    
    private function insertCountryQuery($id, $name, $code) {
        $explode = explode(" ", $name);
        $firstWord = $explode[0];  $secondWord = $explode[1];
        $countryCode = $firstWord[0] . $secondWord[0];
        
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
            
            $this->log->addInfo("Sql:" . $sql);
            
            if(!$query) {
            	$this->log->addError("Insert Country Query Failed: " . mysqli_error($this->conn));
            	exit();
            } else {
            	$this->log->addInfo("Insert Country Query OK ");
            }
    }
    
    public function insertCountry($valueSet) {
        $fhirData = $this->getFhirData($valueSet)->expansion->contains;
        $size = iterator_count($fhirData);
        for ($x = 0; $x < $size; $x++) {
			$f = $fhirData[$x];
            echo "Country Inserted: " . $f->display['value'] . " - " .  $f->code['value'] . "<br>";
            $this->insertCountryQuery($x, $f->display['value'], $f->code['value']);
        }
    }
    
    public function dropRegion() {
        $sql = "TRUNCATE table hippo_country";
        $query = mysqli_query($this->conn, $sql);
        
        if(!$query) {
        	$this->log->addError("Drop Region Query Failed: " . mysqli_error($this->conn));
        	exit();
        } else {
        	$this->log->addInfo("Drop Region Query OK ");
        }
    }
    
    public function insertRegion($valueSet) {
        $fhirData = $this->getFhirData($valueSet);
        
        foreach($fhirData as $f) {
            if($f->contains != null) { //Verify this works
            ////TODO: insert Region - $this->insertRegionQuery($f->display['value'], $f->display['value']);
            }
        }
    }
    
    public function dropDistrict($valueSet) {
        $sql = "TRUNCATE table hippo_country";
   		$query = mysqli_query($this->conn, $sql);
        
        if(!$query) {
        	$this->log->addError("Drop District Query Failed: " . mysqli_error($this->conn));
        	exit();
        } else {
        	$this->log->addInfo("Drop District Query OK ");
        }
    }
    
    public function insertDistrict() {
        $fhirData = $this->getFhirData($valueSet);
        
        foreach($fhirData as $f) {
            if($f->contains != null) { //Verify this works
                //$this->insertDistrictQuery($f->display['value'], $f->display['value']); - TODO: District
            }
        }
       
    }
    
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
    	 
    	$this->log->addInfo("Sql:" . $sql);
    
    	if(!$query) {
    		$this->log->addError("Insert County Query Failed: " . mysqli_error($this->conn));
    		exit();
    	} else {
    		$this->log->addInfo("Insert County Type Query OK ");
    	}
    }
    
    public function dropCounty() {
        $sql = "TRUNCATE table hippo_country";
        $query = mysqli_query($this->conn, $sql);
        
        if(!$query) {
        	$this->log->addError("Drop County Query Failed: " . mysqli_error($this->conn));
        	exit();
        } else {
        	echo 'Drop County Query OK <br>';
        	$this->log->addInfo("Drop County Query OK ");
        }
    }
    
    /**
     * You can create a set of County's based on a Value Set and a District ID (that the County's belong to)
     * @param unknown $valueSet to create the County's from
     * @param unknown $districtId of all the County's being created here
     */
    public function insertCounty($valueSet, $districtId) {
    $fhirData = $this->getFhirData($valueSet)->expansion->contains;
        
        $size = iterator_count($fhirData);
        for ($x = 0; $x < $size; $x++) {
			$f = $fhirData[$x];
			echo "County Inserted: " . $f->display['value'] . " - " .  $f->code['value'] . "<br>";
            $this->insertFacilityQuery($x, $f->display['value'], $districtId); 
        }
        
    }
    
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
    	
    	$this->log->addInfo("Sql:" . $sql);
    
    	if(!$query) {
    		$this->log->addError("Insert Facility Type Query Failed: " . mysqli_error($this->conn));
    		exit();
    	} else {
    		$this->log->addInfo("Insert Facility Type Query OK ");
    	}
    }
    
    public function dropFacility() {
        $sql = "TRUNCATE table hippo_country";
    	$query = mysqli_query($this->conn, $sql);
        
        if(!$query) {
        	$this->log->addError("Drop Facility Query Failed: " . mysqli_error($this->conn));
        	exit();
        } else {
        	$this->log->addInfo("Drop Facility Query OK ");
        }
    }
    
    public function insertFacility($valueSet) {
   		$fhirData = $this->getFhirData($valueSet)->expansion->contains;
        
        $size = iterator_count($fhirData);
        for ($x = 0; $x < $size; $x++) {
			$f = $fhirData[$x];
			echo "Facility Inserted: " . $f->display['value'] . " - " .  $f->code['value'] . "<br>";
            $this->insertFacilityQuery($x, $f->display['value']);
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
    	 
    	$this->log->addInfo("Sql:" . $sql);
    
    	if(!$query) {
    		$this->log->addError("Insert Position Failed: " . mysqli_error($this->conn));
    		exit();
    	} else {
    		$this->log->addInfo("Insert Position Query OK ");
    	}
    }
    
    public function dropPosition() {
        $sql = "TRUNCATE table hippo_position_type";
    	$query = mysqli_query($this->conn, $sql);
        
        if(!$query) {
        	$this->log->addError("Drop Position Query Failed: " . mysqli_error($this->conn));
        	exit();
        } else {
        	$this->log->addInfo("Drop Position Query OK ");
        }
    }
    
    public function insertPosition($valueSet) {
    	$fhirData = $this->getFhirData($valueSet)->expansion->contains;
        
        $size = iterator_count($fhirData);
        for ($x = 0; $x < $size; $x++) {
			$f = $fhirData[$x];
            echo "Position Inserted: " . $f->display['value'] . " - " .  $f->code['value'] . "<br>";
            $this->insertPositionQuery($x, $f->display['value']);
        }
        
    }
    
   
}
