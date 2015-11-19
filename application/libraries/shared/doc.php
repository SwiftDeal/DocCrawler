<?php

/**
 * Controls fetching of doctor info
 *
 * @author Hemant Mann
 */
namespace Shared;

use WebBot\lib\WebBot\Bot as Bot;
use ZocDoc\Helper as Helper;
class Doc {
    
    /**
     * @read
     */
    protected $_specialities = array("345","132","346","105","143","156","98","101","385","130","127","386","106","388","110","114","398","362","107","128","113","104","111","116","157","151","135","117","139","152","100","336","335","120","121","153","137","122","337","108","123","109","155","129","158","387","382","408","126","142","373");

    /**
     * Array of new Doctors
     * @readwrite
     */
    protected static $_results = array();

    /**
     * @readwrite
     * Stores the count of new Doctors fetched. Basically rows added in DB
     */
    protected static $_count = 0;
    
    public function __construct() {
        self::$_results = array();
        self::$_count = 0;
    }
    
    protected function _filterResult($result) {
        $result = str_replace("for(;;);", "", $result);
        return $result;
    }
    
    /**
     * Executes a bot request and returns the body of the request if request was
     * successful else returns false
     *
     * @param string $key Just a key for logging the bot request (if logging enabled)
     * @param url $url Url of the page to be fetched
     * @return string|boolean|object
     */
    protected function _executeRequest($key, $url, $scrape = false) {
        $urls = array(
            "$key" => $url
        );
        $bot = new Bot($urls);
        $bot->execute();
        $documents = $bot->getDocuments();
        $document = ($documents) ? array_pop($documents) : false;
        
        if ($document && !$scrape) {
            return $document->getHttpResponse()->getBody();
        }

        if ($document && $scrape) {
            return $document;
        }
        return false;
    }
    
    /**
     * Returns list of doctors info for a given zipcode and category (speciality).
     *
     * @param string $zip Zip code of the city
     * @param string $cat Speciality Id of the doctor
     * @return object of stdClass
     */
    protected function _processList($zip, $cat) {
        $results = array();
        $response = array();
        
        for ($i = 0; $i <= 90; $i += 10) {
            $body = $this->_executeRequest('search', Helper::searchUrl($zip, $cat, $i));
            
            $search = json_decode($this->_filterResult($body));
            $ids = $search->ids;
            
            if (!empty($ids)) {
                $body = $this->_executeRequest('doctors', Helper::doctorsList($ids));
            } 
            else {
                $body = false;
            }
            
            if ($body) {
                $result = json_decode($this->_filterResult($body));
                foreach ($result->doctor_locations as $doc) {
                    $results[] = $doc;
                }
            } 
            else {
                break;
            }
        }
        return $results;
    }

    /**
     * Saves the insurance details of the firms and plans registered with doctor
     *
     * @param \Doctor object $doc
     * @param \DocSearch object $loc
     */
    protected function _insurance($doc, $loc) {
        // find all the insurance plans registered with the doctor
        $body = $this->_executeRequest('insurance', Helper::insurance($doc->zocdoc_id));

        if (!$body) {
            return;
        }

        $insurances = json_decode($this->_filterResult($body))->Carriers;
        foreach ($insurances as $i) {
            // check if Insurance Carrier saved in db
            $carrier = \Insurance::first(array("name = ?" => $i->Name));
            if (!$carrier) {
                $carrier = new \Insurance(array(
                    "name" => $i->Name
                ));
                $carrier->save();
            }
            $plans = $i->Plans;
            foreach ($plans as $p) {
                // check if plan saved in db
                $ins_plan = \InsurancePlan::first(array("name = ?" => $p->Name));
                if (!$ins_plan) {
                    $ins_plan = new \InsurancePlan(array(
                        "name" => $p->Name,
                        "insurance_id" => $carrier->id
                    ));
                    $ins_plan->save();
                }

                // Add the plan for doctor in insurance search
                $search = \InsuranceSearch::first(array("ins_plan_id = ?" => $ins_plan->id, "doctor_id = ?" => $doc->id, "speciality_id = ?" => $doc->speciality_id));
                if (!$search) {
                    $search = new \InsuranceSearch(array(
                        "ins_plan_id" => $ins_plan->id,
                        "doctor_id" => $doc->id,
                        "speciality_id" => $doc->speciality_id,
                        "zip_code" => $loc->zip_code,
                        "city" => $loc->city
                    ));
                    $search->save();
                }
            }
        }
    }

    /**
     * Try to get the bio of the doctor from his profile if found then scrapes
     * the info else return empty string
     */
    protected function _bio() {
        // Get doctor bio from his profile
        $result = "";
        if ($zocdoc->doctor->url) {
            $doc = $this->_executeRequest('bio', 'http://zocdoc.com'. $zocdoc->doctor->url, true);
            if ($doc) {
                try {
                    $el = $doc->query('//*[@id="html"]/body/div[2]/div/div[2]/div/div[1]/div[2]/div/div/div')->item(0);
                    $result = "<pre>". $el->nodeValue ."</pre>";    
                } catch (\Exception $e) {
                    // Could not find the info
                }
            }
        }
        return $result;
    }
    
    protected function _save($response, $saveData = false) {
        foreach ($response as $zocdoc) {
            $location_exits = false;
            $newDoc = false;
            
            // check if the practice listed in our database
            if (isset($zocdoc->practice->id)) {
                $practice = \Practice::first(array(
                    "zocdoc_id = ?" => $zocdoc->practice->id
                ));
                if (!$practice) {
                    $practice = new \Practice(array(
                        "zocdoc_id" => $zocdoc->practice->id,
                        "name" => $zocdoc->practice->name
                    ));
                    $practice->save();
                }
            }
            
            // Check if the doctor saved in our database
            if (isset($zocdoc->doctor->id)) {
                $doctor = \Doctor::first(array(
                    "zocdoc_id = ?" => $zocdoc->doctor->id
                ));
                if ($doctor) {
                    $location = \DocSearch::all(array(
                        "doctor_id = ?" => $doctor->id,
                        "speciality_id = ?" => $doctor->speciality_id
                    ));
                    if ($location) {
                        foreach ($location as $l) {
                            if ($l->latitude == $zocdoc->location->lat && $l->longitude == $zocdoc->location->lon) {
                                $location_exits = true;
                            }
                        }
                    }
                } 
                else {
                    $doctor = new \Doctor(array(
                        "name" => $zocdoc->doctor->name,
                        "gender" => $zocdoc->doctor->gender,
                        "bio" => $this->_bio(),
                        "suffix" => $zocdoc->doctor->suffix,
                        "speciality_id" => $zocdoc->doctor->specialty->id,
                        "zocdoc_id" => $zocdoc->doctor->id,
                        "practice_id" => @$practice->id
                    ));
                    $doctor->save();
                    $newDoc = true;
                }
                
                if (!$location_exits) {
                    $addr = explode(",", $zocdoc->location->address_line_2);
                    $addr[1] = trim($addr[1]);
                    $codes = explode(" ", $addr[1]);
                    $location = new \DocSearch(array(
                        "doctor_id" => $doctor->id,
                        "speciality_id" => $doctor->speciality_id,
                        "address" => $zocdoc->location->address_line_1,
                        "city" => $addr[0],
                        "state_code" => $codes[0],
                        "zip_code" => (int) $codes[1],
                        "latitude" => $zocdoc->location->lat,
                        "longitude" => $zocdoc->location->lon
                    ));
                    $location->save();
                }
            }

            $this->_insurance($doctor, $location);

            if ($newDoc && $saveData) {
                self::$_results[] = array(
                    "name" => $doctor->name,
                    "gender" => $doctor->gender,
                    "address" => $location->address,
                    "zip" => $location->zip_code
                );
                self::$_count++;
            }
        }
    }
    
    public function fetch($check = false) {
        $codes = Helper::zip_codes();
        $total = count($codes);
        
        $last = Helper::lastRunCode();
        if ($check) {
            if ($last) {
                $index = $last;
            } else {
                $index = 0;
            }
        } else {
            $index = 0;
        }
        
        for ($i = $index; $i < $total; ++$i) {
            foreach ($this->_specialities as $key => $sp) {
                $response = $this->_processList($codes[$i], $sp);
                $this->_save($response);
            }

            if ($check) {
                Helper::lastRunCode($i);    
            }
        }
    }

    /**
     * Fetches all the doctors for a given zipcode and saves the new ones in db
     */
    public function manual($arr, $env = "production") {
        switch ($env) {
            case 'production':
                foreach ($this->_specialities as $key => $sp) {
                    $response = $this->_processList($arr["zip"], $sp);
                    $this->_save($response, "saveData");
                }
                break;
            
            case 'testing':
                $response = $this->_processList($arr["zip"], $arr["speciality"]);
                $this->_save($response, "saveData");
                break;
        }
    }

    /**
     * Returns the new Doctors fetched from the request
     * @return array
     */
    public static function newInfo() {
        $return = array();
        $return["doctors"] = self::$_results;
        $return["count"] = self::$_count;
        return $return;
    }
}
