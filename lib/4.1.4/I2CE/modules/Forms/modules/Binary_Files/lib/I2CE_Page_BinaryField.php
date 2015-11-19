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
* @package i2ce
* @subpackage i2ce
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.0
* @since v4.0.0
* @filesource 
*/ 
/** 
* Class I2CE_Page_BinaryField
* 
* @access public
*/


class I2CE_Page_BinaryField extends I2CE_Page{


    /**
     * Handles creating hte I2CE_TemplateMeister templates and loading any default templates
     * @returns boolean true on success
     */
    protected function initializeTemplate() {
        //we don't want any tempaltes for this
        return true;
    }





    /**
     * Handles GET requests for a binary field
     * The relevant get variables are:
     *<ul><li> cat -- the I2CE_FileSearch category we should be looking for</li>
     *    <li> name -- the filename we are looking for</li>
     *</ul>
     *As an alternative (mainly b/c libxml's xmlSetProp (which is used by PHP's DOM) which
     * will automatically escape &)  you can set the variable encoded=ENC_BLAH   where ENC_BLAH is
     * an urlencoded string  with the cat and name variables set e.g urlencode("cat=SCRIPTS&name=somescript.js")
     */
    public function display($supress_output = false) {
        if ( $_SERVER['REQUEST_METHOD'] != "GET" && $_SERVER['REQUEST_METHOD'] != "HEAD" || !$this->request_exists('formid') || !$this->request_exists('field') ){
            //do nothing if it is not a GET request
            exit();
        }        
        $field = $this->request('field');
        $formid = $this->request('formid');
        if ($this->request_exists('tmp_key')) {
            if (! ($formObj = I2CE_FormFactory::instance()->createContainer($formid)) instanceof I2CE_Form) {
                exit();
            }
            if (!$field ||  ! ($fieldObj=$formObj->getField($field)) instanceof I2CE_FormField_BINARY_FILE) {
                exit();
            }
            $fieldObj->setTempKey($this->request('tmp_key'));
            $fieldObj->setFromTemporaryTable();
        } else {
            if (!$this->request_exists('formid')) {
                exit();
            }

            if (! ($formObj = I2CE_FormFactory::instance()->createContainer($formid)) instanceof I2CE_Form) {
                exit();
            }
            if (!$field ||  ! ($fieldObj=$formObj->getField($field)) instanceof I2CE_FormField_BINARY_FILE) {
                exit();
            }
            $formObj->populate();
        }
        //we are good to go  dump and die.        
        $cacheTime = 60;  // defaults to 1 minute
        if (I2CE::getConfig()->setIfIsSet($cacheTime,'/modules/BinFields/cache_time')) {
            $caheTime = $cacheTime * 60;
        }
        I2CE_Dumper::prepForDump(
            $fieldObj->getModTime(),
            $fieldObj->getContentLength(),
            $fieldObj->getHeaders(),
            $cacheTime);
        echo $fieldObj->getValue();
        exit();
    }





}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
