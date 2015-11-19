<?php

/**
 * Package to Sync FHIR Data with iHRIS
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_FHIR_Sync extends I2CE_Page {
	
	protected function action_enable() {
		echo '<h1>CHECK OK<h1>';
		return true;
	}
	
	protected function action_initialize() {
		return true;
	}
                
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
