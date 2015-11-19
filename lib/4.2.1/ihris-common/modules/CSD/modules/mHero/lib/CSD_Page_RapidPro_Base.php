<?php
/**
* © Copyright 2014 IntraHealth International, Inc.
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
* @package ihris-common
* @subpackage CSD
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class CSD_Page_RapidPro_Base
* 
* @access public
*/


abstract class CSD_Page_RapidPro_Base extends I2CE_Page {


    protected $host = false;
    protected $token = false;
    protected $slug = null;
    public $rapidpro = null;
    protected $csd_host = null;

    public  function __construct( $args,$request_remainder , $get = null, $post = null) {
	parent::__construct( $args,$request_remainder , $get, $post );
	
	if (array_key_exists('server_host',$this->args)
	    && is_scalar($this->args['server_host'])
	    ){
	    $this->host = $this->args['server_host'];
	}
	if (array_key_exists('api_token',$this->args)
	    && is_scalar($this->args['api_token'])
	    ){
	    $this->token = $this->args['api_token'];
	}
	if (array_key_exists('slug',$this->args)
	    && is_scalar($this->args['slug'])
	    ){
	    $this->slug = $this->args['slug'];
	}
	if (array_key_exists('csd_host',$this->args)
	    && is_scalar($this->args['csd_host'])
	    ){
	    $this->csd_host = $this->args['csd_host'];
	}

	$this->rapidpro = new CSD_Interface_RapidPro($this->token,$this->host);

    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
