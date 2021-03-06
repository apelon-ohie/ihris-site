<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Set up all the configuration variables that can be set for each installation.
 * 
 * The main include file will include this file for all pages for the site.
 * @see main.inc.php
 * @package iHRIS
 * @subpackage DemoManage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org> / Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since Demo-v1.a
 * @version Demo-v2.a
 */
/*****************************************************************
 *                                                               *
 *                   BEGIN USER CUSTOMIZATION                    *
 *                                                               *
 *****************************************************************/

/**
 * Copy this file to local/config.values.php and set the values you
 * need to update there.  Anything you don't set in 
 * local/config.values.php will get its value from here.
 */


/**
 * the path to the I2CE installation. 
 * You might need to set this depending on your installation
 *    Default value is  ../../../../I2CE
 */
//$version = '4.1.4';
$latest_version = '4.2.1';
$i2ce_site_i2ce_path = "/var/lib/iHRIS/lib/4.2.1/i2ce";


/**
 * the dsn to connect to your databse
 */
$i2ce_site_dsn = 'mysql://ihris_manage:apelon1@localhost/ihris_manage' ;

//FHIR Sync Data
$fhir_server_url = 'http://40.143.220.156:8081/dtsserverws/fhir/';
$fhir_server_username = 'dtsadminuser';
$fhir_server_password = 'dtsadmin';

$site_url = 'http://hardevhim.ct.apelon.com:83/manage/';

//VALUE-SETS
//FACILITIES + POSITIONS
$fhir_valueset_facilities = 'valueset-c80-facilitycodes';
$fhir_valueset_positions = 'HeathCareWorkerTypes';
//GEOGRAPHY
$fhir_valueset_countries = 'Countries';
$fhir_valueset_county = 'Countries';
$fhir_valueset_regions = 'Countries';
$fhir_valueset_district = 'Countries';

//Geographic Value-Set Mapping
$geography_valueset_map = array
(
  array(
	"223688001"=>"united-states",
	"223690000"=>"223688001"
	)
);

/**
 * Initialization string for user access.  See http://open.intrahealth.org/mediawiki/Pluggable_Authentication
 *  
 */
$i2ce_site_user_access_init = null;



/**
 * the configuration xml file for the site module.  You need to set this.
 */
$i2ce_site_module_config = "/var/lib/iHRIS/sites/manage/iHRIS-Manage-BLANK.xml";


/*****************************************************************
 *                                                               *
 *                   END USER CUSTOMIZATION                      *
 *              Do not edit anything below this line             *
 *                                                               *
 *****************************************************************/






# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
