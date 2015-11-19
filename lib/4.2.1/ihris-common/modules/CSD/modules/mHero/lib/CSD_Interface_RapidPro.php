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
* Class CSD_Interface_RapidPro
* 
* @access public
*/


class CSD_Interface_RapidPro {

    protected $token = false;
    protected $host = false;
    protected $site_url =  false;
    protected $run_url =  false;
    protected $flow_url =  false;
    protected $contact_url =  false;
    protected $result_url =  false;
    protected $base_url =  false;
    protected $headers = array();

    public function __construct($token,$site_url  ='https://rapidpro.io') {
	$this->token = $token;
	$this->site_url = rtrim($site_url,'/');
	$this->base_url  = $this->site_url  . "/api/v1";
	$this->run_url = $this->base_url . "/runs.json";
	$this->result_url = $this->base_url . "/results.json";
	$this->flow_url = $this->base_url . "/flows.json";
	$this->contact_url = $this->base_url . "/contacts.json";
	$this->headers = array('Authorization: Token ' . $this->token,'Content-Type: application/json','Accept: application/json');
    }

    public function getServer() {
        return $this->site_url;
    }

    public function getToken() {
        return $this->token;
    }


    protected $curl = null;
    protected function ensureConnection() {
	if (! is_resource( $this->curl)){
            $this->curl = curl_init();
            curl_setopt($this->curl, CURLOPT_HTTPHEADER,$this->headers);
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
	}      
        return is_resource($this->curl);
    }


    protected $flows = false;
    public function getFlows() {
        if (!is_array($this->flows)) {
            $url = $this->flow_url;
            $flows = array();
            do {
                $this->ensureConnection();
                curl_setopt($this->curl, CURLOPT_URL, $url);
                if (! ($data  = curl_exec($this->curl))
                    || ! is_array(    $t_flows = json_decode($data,true))
                    || ! array_key_exists('results',$t_flows)
                    || ! is_array($t_flows['results'])
                    ) {
                    I2CE::raiseError("BAD response from $url (curl error:" . trim(curl_errno($this->curl)) . ':' . trim(curl_error($this->curl)) .  ")\n" . print_r($data,true));
                    break;
                }
                $url = false;
                if (array_key_exists('next',$t_flows)) {
                    $url = $t_flows['next'];
                }
                $flows = array_merge($flows,$t_flows['results']);
            } while ($url);
            $this->flows = $flows;
        }
        return $this->flows;

    }

    public function getFlowValues($flow = null,$contact = null) {
	$url = $this->run_url;
        $params = array();
        if (is_scalar($flow)) {
            $params[] =  'flow=' . $flow;
        } 
        if (is_scalar($contact)) {
            $params[]  = 'contact=' . $contact;
        }
        $url .= '?' . implode('&',$params);
	$flow_values = array();
	do {
            $this->ensureConnection();
	    curl_setopt($this->curl, CURLOPT_URL, $url);
	    if (! ($data  = curl_exec($this->curl))
		|| ! is_array(    $t_flow_values = json_decode($data,true))
		|| ! array_key_exists('results',$t_flow_values)
		|| ! is_array($t_flow_values['results'])
		) {
		I2CE::raiseError("BAD response from $url (curl error:" . trim(curl_errno($this->curl)) . ':' . trim(curl_error($this->curl)) .  ")\n".print_r($data,true));
                continue;
	    }
	    $url = false;
	    if (array_key_exists('next',$t_flow_values)) {
		$url = $t_flow_values['next'];
	    }
            foreach ($t_flow_values['results'] as $result) {
                if (!is_array($result)
                    || ! array_key_exists('contact',$result)
                    ||  (is_scalar($contact) && $result['contact']  != $contact)
                    ){
                    continue;
                }
                $flow_values[] = $result;
            }
	} while ($url);        
	return $flow_values;
    }



    public function get_flow_field_labels($flow) {
        $flow_details = $this->getFlows();
        $labels = array();
        foreach ($flow_details as $flow_data) {
            if (!is_array($flow_data)
                || ! array_key_exists('name',$flow_data)
                || ! array_key_exists('flow',$flow_data)
                || ! ($name = $flow_data['name'])
                || ! ($flow == $flow_data['flow'])
                || ! array_key_exists('rulesets',$flow_data)
                || ! is_array($flow_data['rulesets'])
                ) {
                continue;
            }
            $rulesets = $flow_data['rulesets'];
            foreach ($rulesets as $ruleset) {
                if (!is_array($ruleset)
                    || !array_key_exists('id',$ruleset)
                    || ! ($id = $ruleset['id'])
                    || !array_key_exists('label',$ruleset)
                    || ! ($label = $ruleset['label'])
                    ) {
                    continue;
                }
                $labels[] = $label;
            }
        }
        return $labels;

    }

    public function get_detailed_results_on_flow($flow ) {
        $flow_details = $this->getFlows();
        $rulesets = array();
        foreach ($flow_details as $flow_data) {
            if (!is_array($flow_data)
                || ! array_key_exists('name',$flow_data)
                || ! array_key_exists('flow',$flow_data)
                || ! ($name = $flow_data['name'])
                || ! ($flow == $flow_data['flow'])
                || ! array_key_exists('rulesets',$flow_data)
                || ! is_array($flow_data['rulesets'])
                ) {
                continue;
            }
            $rulesets = $flow_data['rulesets'];
        }
        $all_results = array();
        foreach ($rulesets as $ruleset) {
            if (!is_array($ruleset)
                || !array_key_exists('id',$ruleset)
                || ! ($id = $ruleset['id'])
                || ! ($label = $ruleset['label'])
                ) {
                continue;
            }
            $url = $this->result_url . '?ruleset=' . $id;
            $result = array();

            $this->ensureConnection();
            curl_setopt($this->curl, CURLOPT_URL, $url);
            if (! ($data  = curl_exec($this->curl))
                || ! is_array(    $resp = json_decode($data,true))
                || ! array_key_exists('results',$resp)
                || ! is_array($resp['results'])
                ||  (
                    is_scalar($flow)
                    && (
                        ! count($resp['results']) == 1
                        || ! reset($resp['results'])
                        || ! is_array($res = current($resp['results']))
                        )
                    )
                ) {
                I2CE::raiseError("BAD response for  from $url (curl error:" . trim(curl_errno($this->curl)) . ':' . trim(curl_error($this->curl)) .  ")\n:" .print_r($data,true));
                continue;
            }            
            $all_results[] =   array('label'=>$label,'result'=>$res);
        }
	return $all_results;

    }


    protected $contact_data = null;
    public function getContactData($contact) {
        if (!is_scalar($contact)) {
            return array();
        }
        if (!array_key_exists($contact, $this->contact_data)) {
            $this->contact_Data[$contact] = $this->_getContact();
        }
        return $this->contact_data[$contact];
    }

    public function getAllContactData() {
        return $this->_getContactData();
    }
                              

    protected function _getContactData($contact = null) {
	$url  = $this->contact_url;
        if (is_scalar($contact)) {        
            $url  .='?uuid=' . $contact;
        }
        $result = array();
        do {
            $this->ensureConnection();
            curl_setopt($this->curl, CURLOPT_URL, $url);
            if (! ($data  = curl_exec($this->curl))
                || ! is_array(    $resp = json_decode($data,true))
                || ! array_key_exists('results',$resp)
                || ! is_array($resp['results'])
                ||  (
                    is_scalar($contact)
                    && (
                        ! count($resp['results']) == 1
                        || ! reset($resp['results'])
                        || ! is_array($res = current($resp['results']))
                        || !  array_key_exists('uuid',$res)
                        || $res['uuid'] != $uuid
                        )
                    )
                ) {
                I2CE::raiseError("BAD response for contact $contact from $url (curl error:" . trim(curl_errno($this->curl)) . ':' . trim(curl_error($this->curl)) .  ")\n:" .print_r($data,true));
                break;
           
            }
            if (is_scalar($contact)) {
                return $resp['results'];
                break;
            }  else {
                $url = false;
                if (array_key_exists('next',$resp)) {
                    $url = $resp['next'];
                }                
                $result = array_merge($result,$resp['results']);
            }
        } while ($url);

	return $result;
    }

    public function getContactDetail($contact,$detail) { 
        $contact_data = $this->getContactData($contact);
        if (!is_scalar($detail)
            || !array_key_exists($detail,$contact_data)) {
            return false;
        } else {
            return $contact_data[$detail];
        }
    }

    public function getPhone() {
        return $this->cetContactDetails($contact,'phone');
    }

    public function getContactFieldValue($contact,$field) {
        $values = $this->getContactData($contact,'fields');
        if (!is_scalar($field)
            || !array_key_exists($field,$values)
            ) {
            return false;
        }
        return $values[$field];
    }



    

    public function start_workflow($flow,$contacts) {
        if (is_scalar($contacts)) {
            $contacts = array($contacts);
        }
        if (!is_scalar($flow)
            || !is_array($contacts)) {
            I2CE::raiseError("Invlaid flow / contacts specfified");
            return false;
        }
        $json = json_encode(
            array(
                'flow'=>$flow,
                'contact'=>$contacts
                )
            );
        return $this->post($this->run_url,$json);        
    }

    public function post($url,$json) {
        $curl_post = curl_init();
        curl_setopt($curl_post , CURLOPT_URL, $url);
        curl_setopt($curl_post, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        $headers_post = $this->headers;
        $headers_post[] = 'Content-Type: application/json';                                                                                
        $headers_post[] = 'Content-Length: ' . strlen($json);
        curl_setopt($curl_post, CURLOPT_HTTPHEADER, $headers_post);
        curl_setopt($curl_post, CURLOPT_POSTFIELDS, $json);                                                                  
        curl_setopt($curl_post, CURLOPT_RETURNTRANSFER, 1);
        $post_resp = curl_exec($curl_post);
        $post_info = array();
        if( ! ($post_resp)
            || curl_errno($curl_post)
            || !is_array($post_info = curl_getinfo($curl_post))
            || ! array_key_exists('http_code',$post_info)
            ||  $post_info['http_code']  != 200
            ) {
            I2CE::raiseError("Flow kick-off failed (curl error:" . trim(curl_errno($curl_post)) . ':' . trim(curl_error($curl_post)) .  "):" .print_r($post_info,true) );
            return false;
        }    
        curl_close($curl_post);
        return true;
    }

    //var rapidProIDAssigner 'http://rapidpro.io/' + config.authentication.rapidpro.instance;

    public function createRequest($csd_uuids,$assigning_authority = false, $code = false) {
        $ids = '';
        foreach ($csd_uuids as $id=>$csd_uuid) {
            if (!is_scalar($csd_uuid)
                || ! ($csd_uuid)) {
                continue;
            }
            $ids .= "     <csd:id entityID='" . $csd_uuid . "'/>\n" ;
        }
        if ($code) {
            $code =  "    <csd:code>" . $code . "</csd:code>";
        } else {
            $code = '';
        }
        if ($assigning_authority) {
            $assigning_authority =  "    <csd:assigningAuthorityName>" . $assigning_authority . "</csd:assigningAuthorityName>";
        } else {
            $assigning_authority = '';
        }

        return 
            "  <requestParams xmlns:csd='urn:ihe:iti:csd:2013'>" 
            . $code 
            . $ids 
            ."  </requestParams>" ;
        
    }

    //$host = http://localhost:8984/CSD/csr/ihris_manage_demo_linked_to_sierra_leone_demo/careServicesRequest
    public function getOtherIDs($csd_host,$csd_uuids, $assigning_authority = false,$code =false) {
        $other_ids = array();
        if (is_scalar($csd_uuids)) {
            $csd_uuids = array($csd_uuids);
        }
        if (!is_array($csd_uuids)) {
            I2CE::raiseError("Invalid CSD UUIDs");
            return $other_ids;
        }
        $url = rtrim($csd_host,'/')  . "/" . "urn:openhie.org:openinfoman-hwr:stored-function:bulk_health_worker_read_otherids_json";
        $request = $this->createRequest($csd_uuids,$assigning_authority,$code);
        $curl_post = curl_init();
        curl_setopt($curl_post , CURLOPT_URL, $url);
        curl_setopt($curl_post, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        $headers_post = 
            array(
                'Content-Type: application/xml',
                'Content-Length: ' . strlen($request)
                );
        curl_setopt($curl_post, CURLOPT_HTTPHEADER, $headers_post);
        curl_setopt($curl_post, CURLOPT_POSTFIELDS, $request);                                                                  
        curl_setopt($curl_post, CURLOPT_RETURNTRANSFER, 1);
	if (! ($data  = curl_exec($curl_post))
	    || ! is_array(    $all_resp = json_decode($data,true))
            || ! array_key_exists('results',$all_resp)
            || ! is_array($resp = $all_resp['results'])
            ) {
	    I2CE::raiseError("BAD response from $url (curl error:" . trim(curl_errno($curl_post)) . ':' . trim(curl_error($curl_post)) .  ")\n:" .print_r($data,true));
        } else {
            I2CE::raiseError("Matched " . count($resp) . " in request\n" .  $request);
            foreach ($resp as $res) {
                if (!is_array($res) 
                    || ! array_key_exists('entityID',$res)
                    || ! array_key_exists('otherID',$res)
                    || ! is_array( $res['otherID'])
                    || count($res['otherID']) == 0
                    || ! in_array($res['entityID'],$csd_uuids)
                    ) {
                    continue;
                }
                if ($assigning_authority) {
                    $t_other_ids = array();
                    foreach ($res['otherID'] as $other_id) {
                        if ( !array_key_exists('authority',$other_id)
                             || $other_id['authority'] != $assigning_authority
                            ) {
                            continue;
                        }
                        $t_other_ids[] = $other_id;
                    }
                    $res['otherID'] = $t_other_ids;
                }
                $other_ids[$res['entityID']] = $res;
            }
        }
        return $other_ids;
    }



    public function lookupOtherID($csd_host,$csd_uuid, $assigning_authority) {
        $rapidpro_id = false;
        $matches = $this->getOtherIDs($csd_host,$csd_uuid,$assigning_authority) ;
        foreach ($matches as $details) {
            if (!is_array($details)
                || ! array_key_exists('entityID',$details)
                || $details['entityID'] != $csd_uuid
                || ! array_key_exists('otherID',$details)
                || !is_array($other_ids = $details['otherID'])
                ) {
                continue;
            }
            foreach ($other_ids as $other_id) {
                if ( !array_key_exists('authority',$other_id)
                     || !array_key_exists('code',$other_id)
                     || !array_key_exists('value',$other_id)
                     || $other_id['authority'] != $assigning_authority
                    ) {
                    continue;
                }
            }
            $rapidpro_id = $other_id['value'];
            break;
        }
        return $rapidpro_id;
    }


    public function createPhoneRequest($phone) {
        $countryCode = '+231'; //need to make config
        $phones = array();
        foreach (explode('/',$phone) as $number) {
            $number = preg_replace('/[^\\d\\+]/', '',trim($number));
            if (!is_string($number)
                ||strlen($number) == 0) {
                continue;
            }
            if ($number[0] == '+') {
                $phones[] = $number;
            } else  if ($number[0] != '0') {
                $phones[] = $countryCode . $number;
            }  else if ($number[1] == '0') {
                $phones[] = str_slice($number,2);
            } else {
                $phones[] = $countryCode + str_slice($number,1);
            }
        }
        $xml = "
                   <requestParams xmlns:csd='urn:ihe:iti:csd:2013'> 
";
        foreach ($phones as $numbers) {
            $xml .="
                       <csd:contactPoint><csd:codedType>" . $number . "</csd:codedType></csd:contactPoint>";
        }
        $xml .="
                   </requestParams>
" ;
        return $xml;
        
    }



    public function lookupPhone($csd_host,$phone) {
        $url = rtrim($csd_host,'/')  . "/" . "urn:openhie.org:openinfoman-hwr:stored-function:provider_phone_search";
        $request = $this->createPhoneRequest($phone);
        echo $request;
        $curl_post = curl_init();
        curl_setopt($curl_post , CURLOPT_URL, $url);
        curl_setopt($curl_post, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        $headers_post = 
            array(
                'Content-Type: application/xml',
                'Content-Length: ' . strlen($request)
                );
        curl_setopt($curl_post, CURLOPT_HTTPHEADER, $headers_post);
        curl_setopt($curl_post, CURLOPT_POSTFIELDS, $request);                                                                  
        curl_setopt($curl_post, CURLOPT_RETURNTRANSFER, 1);
	if (! ($data  = curl_exec($curl_post))
	    // || ! is_array(    $resp = json_decode($data,true))
	    // || ! array_key_exists('entityID',$resp)
            // || $resp['entityID'] != $csd_uuid
	    // || ! array_key_exists('otherID',$resp)
	    // || ! is_array($other_ids = $resp['otherID'])
            ) {
	    I2CE::raiseError("BAD response for contact $phone from $url (curl error:" . trim(curl_errno($curl_post)) . ':' . trim(curl_error($curl_post)) .  ")\n:" .print_r($data,true));
            return array();
        }
        return $data;
        //return $other_ids;
    }



    public function set_flow_options($template,$selected_flow,$node =null) {
        if (!$template instanceof I2CE_Template) {
            return false;
        }
        $flow_details = $this->getFlows();
        $selected_data = false;
        $options =array();
        foreach ($flow_details as $flow_data) {
            if (!is_array($flow_data)
                || ! array_key_exists('name',$flow_data)
                || ! array_key_exists('flow',$flow_data)
                || ! ($name = $flow_data['name'])
                || ! ($flow = $flow_data['flow'])
                ) {
                continue;
            }
            $attrs = array('value'=>$flow ,'text'=> $name);
            if ($flow == $selected_flow) {
                $selected_data = $flow_data;
                $attrs['selected'] = 'selected';
            }
            $options[] = $attrs;
        }
        if (is_array($selected_data)) {
            $template->setDisplayData('has_stats',1);
            $keys = array('runs','participants','completed_runs','name');
            foreach ($keys as $k) {
                if (!array_key_exists($k,$selected_data)
                    ) {
                    continue;
                }
                $template->setDisplayData($k,$selected_data[$k]);                    
            }
        } else {
            $template->setDisplayData('has_stats',0);
        }
        $template->setDisplayData('flow',$options,$node);
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
