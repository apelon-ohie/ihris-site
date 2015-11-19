<?php
/**
* © Copyright 2009 IntraHealth International, Inc.
* 
* This File is part of I2CE 
* 
* I2CE is free software; you can redistribute it and/or modify 
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
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v3.2.69
* @since v3.2.69
* @filesource 
*/ 
/** 
* Class I2CE_FormField_MAPPED
* 
* @access public
*/


abstract class I2CE_FormField_MAPPED extends I2CE_FormField_DB_STRING { 

    /**
     * Componentizes the given $db_value based on component
     * @param string $db_value.  The non-componentized value
     * @param array $forms of stirng. The form names which we wish to componentize.
     * @param string $component The component we wish to encode
     * @returns string The componentized db_value
     */
    abstract public function getComponentizedValue($db_value,$forms,$component);

    /**
     * Componentizes the given $db_value based on component
     * @param string $db_ref.  The reference to the data
     * @param array $forms of stirng. The form names which we wish to componentize.
     * @param string $component The component we wish to encode
     * @returns string The componentized db_value
     */
    abstract public function getSQLComponentization($db_ref,$forms,$component);


    abstract public function getDefaultDisplayStyle($type);



    

    /**
     * Hooked method to remap a given id on a given form and field
     * @param I2CE_List $lsit
     * @param string $oldid
     * @param string $newid
     */
    public function remapField($form,$oldid,$newid) {
        I2CE::raiseError("Remapping of field not implement for " . get_class($this));
        return false;
    }






    public function getDisplayedStyle($type = 'default') {
        $default_style = $this->getDefaultDisplayStyle($type);
        $path = "meta/display/$type/style";
        if ( !$this->optionsHasPath($path) || ! is_scalar( $style = $this->getOptionsByPath($path) )) {
            $style =  $default_style;
        }
        if ($style !== $default_style) {
            $checkMethod = "checkStyle_$style";
            if (!$this->_hasMethod($checkMethod) || !$this->$checkMethod()) {
                $style = $default_style;
            }
        }
        return $style;
    }

    public function getDisplayedFields($type = 'default', $check_forms = true) {            
        $path = "meta/display/$type/fields";
        if (!$this->optionsHasPath($path) || ! is_scalar( $fields = $this->getOptionsByPath($path) )) {
            $path = "meta/form";
            if (!$check_forms  || !$this->optionsHasPath($path) || ! is_array( $forms = $this->getOptionsByPath($path) )) {
                return array($this->name);
            } else {
                return $forms;
            }
        }
        return explode(':', $fields);
    }




    public function getFormOrders ($type = 'default') {
        $path = "meta/display/orders/$type";
        if (!$this->optionsHasPath($path) || ! is_array( $orders = $this->getOptionsByPath($path) )) {
            return array();
        }
        return $orders;
    }


    /**
     * Checks to see if this field can have map to any form or not.   Set to true in meta/form_any
     *@returns boolean
     */
    public function canSelectAnyForm() {
        $path = "meta/form_any";
        return ($this->optionsHasPath($path) && $this->getOptionsByPath($path));
    }

    /**
     * Checks to see which forms this form can map to.
     * @returns array()
     */
    public function getSelectableForms() {            
        if ($this->getName() == 'remap'  && $this->getContainer() instanceof I2CE_Form) {
            return array($this->getContainer()->getName());
        }
        if ($this->canSelectAnyForm()) {
            return I2CE::getConfig()->getKeys("/modules/forms/forms");
        }
        $path = "meta/form";
        if (!$this->optionsHasPath($path) || ! is_array( $forms = $this->getOptionsByPath($path) )) {
            return array($this->name);
        }
        return $forms;
    }

    /**
     * Return a report name to use for getting the display data for this mapped field instead of using
     * the direct data or from cached tables when that may be very slow.  This is generally for
     * more complicated displays using a tree view.
     * @param string $type
     * @return string
     */
    public function getDisplayReport( $type = 'default' ) {
        $path = "meta/display_report/$type";
        if ( !$this->optionsHasPath($path) || !is_array( $report = $this->getOptionsByPath( $path ) ) ) {
            $path = "meta/display_report/default";
            if ( !$this->optionsHasPath($path) || !is_array( $report = $this->getOptionsByPath( $path ) ) ) {
                return null;
            }
        }
        return $report;
    }



    protected $alternate_limits = array();
    
    public function setAlternateLimits($limits,$type = 'default') {
        $this->alternate_limits[$type] = $limits;
    }
    public function restoreLimits($type = 'default') {
        unset($this->alternate_limits[$type]);
    }
    public function getFormLimits ($type = 'default') {
        if (array_key_exists($type,$this->alternate_limits)) {
            return $this->alternate_limits[$type];
        }
        $path = "meta/limits/$type";
        if (!I2CE_ModuleFactory::instance()->isEnabled('form-limits')
            || !$this->optionsHasPath($path) || ! is_array( $limits = $this->getOptionsByPath($path) )) {
            $limits = array();
        }
        if ( $this->optionsHasPath( "meta/enable_limits_add" ) && is_array( $enabled_add = $this->getOptionsByPath( "meta/enable_limits_add" ) ) ) {
            $limits_add = array();
            foreach( $enabled_add as $module => $enable ) {
                if ( $enable == 1 ) {
                    if ( $this->optionsHasPath( "meta/limits_add/$module" ) ) {
                        $limits_add[$module] = $this->getOptionsByPath( "meta/limits_add/$module" );
                    }
                }
            }
            foreach( $limits_add as $module => $form_limit ) {
                foreach( $form_limit as $form => $limit ) {
                    if ( array_key_exists( $form, $limits ) ) {
                        if ( $limits[$form]['operator'] == 'AND' ) {
                            $limits[$form]['operand'][] = $limit;
                        } else {
                            $limits[$form] = array(
                                    'operator' => 'AND',
                                    'operand' => array( 0 => $limits[$form],
                                        1 => $limit )
                                    );
                        }
                    } else {
                        $limits[$form] = $limit;
                    }
                }
            }
        }
        return $limits;
    }

    /**
     *Gets any additional dynamic limits 
     * @param I2CE_Tempalte $template
     * @param DOMElment  $node
     * @param mixed $limit a json encoded string of limit data or an array of limit data
     * @returns array who keys are selectable form norms with additional limits and values are the limit data
     */
    protected function getAdditionalLimits($template,$node,$limit) {
        $add_limits = array();
        if (!$template instanceof I2CE_Template || !$node instanceof DOMNode) {
            return $add_limits;
        }
        if (is_string($limit)) {
            $limit = json_decode($limit,true);
        }
        if (!is_array($limit)) {
            $limit = array();
        }
        $forms = $this->getSelectableForms();
        $ff = I2CE_FormFactory::instance();        
        foreach ($limit as $limitForm=>$limitFields) {
            if (!in_array($limitForm,$forms)) {
                continue;
            }
            $formObj= $ff->createContainer($limitForm);
            if (!$formObj instanceof I2CE_Form) {
                continue;
            }
            foreach ($limitFields as $limitField=>$limitStyles) {
                $fieldObj = $formObj->getField($limitField);
                if (!$fieldObj instanceof I2CE_FormField) {
                    continue;
                }
                $allowedStyles = $fieldObj->getLimitStyles();
                foreach ($limitStyles as $limitStyle => $displayValue) {
                    if (!is_string($displayValue) || strlen($displayValue) == 0) {
                        continue;
                    }
                    if (!array_key_exists($limitStyle,$allowedStyles)) {
                        continue;
                    }
                    $limitData = $allowedStyles[$limitStyle];
                    if (!is_array($limitData) || !count($limitData) == 1 || !current($limitData) == 'value') {
                        continue;
                    }
                    $value = null;
                    if ($displayValue[0] == '$')  { 
                        $value = $template->getData('DISPLAY',$displayValue,$node,false,false);
                    } else {
                        //assume it is a form value
                        list($t_form,$t_field) = array_pad(explode('+',$displayValue,2),2,'');
                        if (!$t_form || !$t_field) {
                            continue;
                        }
                        $t_formObj = $template->getData('FORM',$t_form,$node,false,false);
                        if ((!$t_formObj instanceof I2CE_Form) || ($t_formObj->getName() != $t_form)) {
                            continue;
                        }
                        $t_fieldObj = $t_formObj->getField($t_field);
                        if (!$t_fieldObj instanceof I2CE_FormField) {
                            continue;
                        }
                        $value = $t_fieldObj->getDBValue();
                    }
                    if (!is_scalar($value) || $value === null) {
                        continue;
                    }
                    //we are good to go
                    if (!array_key_exists($limitForm,$add_limits)) {
                        $add_limits[$limitForm] = array( 'operator'=>'AND', 'operand'=>array());
                    }
                    $add_limits[$limitForm]['operand'][] = array(
                        'operator'=>'FIELD_LIMIT',
                        'field'=>$limitField,
                        'style'=>$limitStyle,
                        'data'=>array(
                            'value'=>$value
                            )
                        );
                        
                }
            }
        }
        return $add_limits;

    }


    /**
     *@returns array where keys are ids, values are arrays with the following keys 'value', 'display'
     */
    public function getMapOptions($type='default', $show_hidden= 0,$flat = true,$add_limits = array()) {
        $forms = $this->getSelectableForms();
        $fields = $this->getDisplayedFields($type);
        $limits = $this->getFormLimits($type);
        $report = $this->getDisplayReport($type);
        if (is_array($add_limits) && count($add_limits) > 0) {
            if (!is_array($limits) || count($limits) > 0) {
                $limits = $add_limits;
            } else{
                //need to go through each form and possibly merge limits
                foreach ($add_limits as $form=>$formLimits)  {
                    if (!array_key_exists($form,$limits) || !is_array($limits[$form]) || count($limits[$form]) == 0) {
                        $limits[$form] = $formLimits;
                    }  else {
                        $limits[$form] = array(
                            'operator'=>'AND',
                            'operand'=>array(0=>$limits[$form], 1=>$formLimits)
                            );
                    }
                }
            }
        }
        $orders = $this->getFormOrders($type);
        //echo "For " . ($this->getContainer() instanceof I2CE_Form ? $this->getContainer()->getName() . " + " : "" ) . $this->getName() . " using " . $report . "\n";
        //I2CE::raiseError( "For " . ($this->getContainer() instanceof I2CE_Form ? $this->getContainer()->getName() . " + " : "" ) . $this->getName() . " using " . print_r($limits,true) );
        $data = I2CE_DataTree::buildDataTree( $fields,$forms,$limits, $orders, $show_hidden, $report );        
        if (!$data) {
            //I2CE::raiseError("Could not build data tree for " .implode(',',$fields) . " and " . implode(",",$forms));
            return array();
        }
        if ($flat) {
            $data = I2CE_DataTree::flattenDataTree( $data );
        } 
        return $data;
    }
    

    
    /**
     * Process the header of an editable node
     * @param I2CE_Template $template
     * @param DOMNode $node;
     * @param DOMNode $head_node;
     */
    protected function processHeaderEditable($template,$node,$head_node) {
        parent::processHeaderEditable($template,$node,$head_node);
        if ( !$node->hasAttribute( "addlink" ) || ! ($href = $node->getAttribute('addlink') )) {
            return;
        }
        if ($node->hasAttribute('addlinktext')) {
            //$add_link = $template->createElement('span');
            $add_link = $template->createElement('a',array('name'=>'add_link'),$node->getAttribute('addlinktext'));
            $head_node->appendChild($add_link);
        } else  {
            $add_link = $template->appendFileByNode('list_add_link.html','span',$head_node);
            if ( !( $add_link instanceof DOMNode)) {
                return;
            }
        }
        $template->setDisplayDataImmediate("add_link",$href,$add_link);
        if ( $node->hasAttribute( "addtask" ) ) {
            $template->setAttribute('task', $node->getAttribute( "addtask" ) ,null,".//a",$add_link);
        }
    }




    
     // /modules/forms/forms/$form/fields/$field/meta/forms =   array(county,district)
     //  selectable forms for this field    // if it does not exist it is $field
     //
     // /modules/forms/forms/$form/fields/$field/meta/display/default/fields = county+district:district+region:[region]:country
     // /modules/forms/forms/$form/fields/$field/meta/display/default/fields = county:district+cssc_region:[cssc_region+cssc_country]:country
     //  if it does not exist, it is "$field"
     //
     //
     // /modules/forms/forms/$form/fields/$field/meta/display/fancy/fields = facility:position
     // /modules/forms/forms/$form/fields/$field/meta/limits/fancy/position = BLAH (status = position_status|open)
     // /modules/forms/forms/$form/fields/$field/meta/limits/fancy/facility =NULL
     // /modules/forms/forms/$form/fields/$field/meta/display/fancy/style = tree
     // /modules/forms/forms/$form/fields/$field/meta/display/fancy/style = list

     /**
      * Set up the default editable display for this form field.
      * @return array of DOMNode
      */
    public function processDOMEditable( $node, $template, $form_node ) {
        $display = 'default';
        if ($form_node->hasAttribute('display')) {
            $t_display = $form_node->getAttribute('display');
            if (strtolower($t_display) != 'true' && strtolower($t_display) != 'false') {
                $display = $t_display;
            }
        }
        return $this->_processDOMEditable($node,$template,$form_node,$display);
    }

     /**
      * Set up the default editable display for this form field.
      * @return array of DOMNode
      */    
    protected function _processDOMEditable( $node, $template,  $form_node, $display ) {
        $permissionParser = new I2CE_PermissionParser($template,new I2CE_User());
        $show_hidden = 0;
        if ($form_node->hasAttribute('show_i2ce_hidden') && $permissionParser->hasTask('can_hide_list_members')) {
            $show_hidden = (int) $form_node->getAttribute('show_i2ce_hidden');
	    if ($show_hidden < 0 || $show_hidden > 2) {
		$show_hidden = 0;
	    }
        }
        $form_node->removeAttribute('show_i2ce_hidden');
        if ($form_node->hasAttribute('selectableforms')) {
            $forms = $this->getSelectableForms();
            $t_forms  = explode(':',$form_node->getAttribute('selectableforms'));
            $t_forms = array_intersect($forms,$t_forms);
            if (count($t_forms) > 0) {
                $forms = $t_forms;
            }
            $form_node->removeAttribute('selectableforms');
        } else {
            $forms = $this->getSelectableForms();
        }
        if ($form_node->hasAttribute('show')) {
            $type = $form_node->getAttribute('show');
            $form_node->removeAttribute('show');
        } else {
            $type = 'default';
        }
        $limits = $this->getFormLimits($type);
        $limit_val = false;
        $calculated_limit_val = null;
        if ($form_node->hasAttribute('limit_field') 
            && ($limit_field = $form_node->getAttribute('limit_field'))
            &&  $form_node->hasAttribute('limit_val') 
            && ($limit_val = $form_node->getAttribute('limit_val'))) {
            if ((strpos($limit_val,':')) !== false) {
                list($form,$field) =  array_pad(explode(':',$limit_val),2,''); 
                //var_dump($template->getForm($form,$form_node));
                if (($formObj = $template->getForm($form,$node)) instanceof I2CE_Form && $formObj->getName() == $form && ($fieldObj  = $formObj->getField($field)) instanceof I2CE_FormField) {
                    $calculated_limit_val = $fieldObj->getDBValue();
                }
                $limit_val = $template->getData('DISPLAY',substr($limit_val,1),$form_node);
            } else if (strlen($limit_val) > 0 && $limit_val[0] == '$') {
                $limit_val = $template->getData('DISPLAY',substr($limit_val,1),$form_node);
            } else {
                $calculated_limit_val = $limit_val;
            }
            if (is_scalar($calculated_limit_val)) {
                $new_limits = array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>$limit_field,
                    'style'=>'equals',
                    'data'=>array(
                        'value'=>$calculated_limit_val
                        )
                    );
                $limit_index = $this->getName();
                if (is_array($limits) && array_key_exists($limit_index,$limits) && is_array($limits[$limit_index]) && count($limits[$limit_index]) > 0) {
                    $limits[$limit_index] = array(
                        'operator'=>'AND',
                        'operand' => array(
                            0=>$limits[$limit_index],
                            1=>$new_limits
                            ));
                } else {
                    $limits[$limit_index] = $new_limits;
                }
                $this->setAlternateLimits($limits);
            }
        }
        $orders = $this->getFormOrders($type);
        $style = $this->getDisplayedStyle($display);        
        $method = 'create_DOMEditable_' . $display;
        if (!$this->_hasMethod($method)) {
            $method = 'create_DOMEditable_' . $style;
            if (!$this->_hasMethod($method)) {
                $method =   'create_DOMEditable_' . $this->getDefaultDisplayStyle($type);
            }
        }
        $this->$method($node,$template,$form_node,$show_hidden);
        $this->restoreLimits();
    }


    
    public function __call($method,$params) { 
        if (preg_match('/^processDOMEditable_(.+)$/',$method,$matches)) {
            if ($this->hasDisplay($matches[1])) {
                if (parent::_hasMethod($method)) {
                    return parent::__call($method,$params);//check if there is a fuzzy method first.  if not pass it to the default handler
                } else {
                    $params[] = $matches[1];
                    return call_user_func_array(array($this,'_processDOMEditable'), $params);
                }
            }
        }
        return parent::__call($method,$params);
    }


    public function _hasMethod($method,$getFuzzy = false,$returnErrors = false) {  //this is b/c of the laziness above
        if (preg_match('/^processDOMEditable_(.+)$/',$method,$matches)) {
            return $this->hasDisplay($matches[1]);
        }
        return parent::_hasMethod($method,$getFuzzy,$returnErrors);
    }





}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
