<?php


class FhirPage extends I2CE_Page {
	
	protected function initPage() {
		parent::setAccess('admin');
	
	}
	
	protected function action() {
		parent::action();
	
		echo 'TEST 12 12';
		
	}
	
}