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
    
    protected function filterResult($result) {
        $result = str_replace("for(;;);", "", $result);
        return $result;
    }
    
    protected function executeRequest($key, $url) {
        $urls = array(
            "$key" => $url
        );
        $bot = new Bot($urls);
        $bot->execute();
        $documents = $bot->getDocuments();
        $document = ($documents) ? array_pop($documents) : false;
        
        if ($document) {
            return $document->getHttpResponse()->getBody();
        }
        return false;
    }
    
    protected function searchUrl($zip, $speciality, $offset) {
        return 'https://www.zocdoc.com/search/searchresults?Address=' . $zip . '&ForceReskin=false&Gender=-1&HospitalId=-1&InsuranceId=-1&InsurancePlanId=-1&LanguageId=1&ProcedureId=12&SpecialtyId=' . $speciality . '&SubSpecialtyId=-1&LimitToThisSpecialty=false&ExcludedSpecialtyIds=&Offset=' . $offset . '&PatientTypeChild=false&genderChanged=false&languageChanged=false&IsPolarisRevealed=False&StartDate=null&_=1444650384181';
    }
    
    protected function doctorsList($ids) {
        $date_end = date("Y-m-d", strtotime(date("Y-m-d") . "+3 day"));
        return 'https://www.zocdoc.com/api/1/appointments/doctor_location/' . $ids . '?start=' . $date_end . '&length=3&procedure_id=12&refinement_id=-1&insurance_plan_id=-1&fullDoctorInformation=false';
    }
    
    /**
     * Returns list of doctors info for a given zipcode and category (speciality).
     *
     * @param string $zip Zip code of the city
     * @param string $cat Speciality Id of the doctor
     * @return object of stdClass
     */
    protected function processList($zip, $cat) {
        $results = array();
        $response = array();
        
        for ($i = 0; $i <= 90; $i += 10) {
            $body = $this->executeRequest('search', $this->searchUrl($zip, $cat, $i));
            
            $search = json_decode($this->filterResult($body));
            $ids = $search->ids;
            
            if (!empty($ids)) {
                $body = $this->executeRequest('doctors', $this->doctorsList($ids));
            } 
            else {
                $body = false;
            }
            
            if ($body) {
                $result = json_decode($this->filterResult($body));
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
    
    protected function save($response, $saveData = false) {
        foreach ($response as $zocdoc) {
            $location = null;
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
                    $location = \DocSearch::first(array(
                        "doctor_id = ?" => $doctor->id
                    ));
                    if ($location) {
                        if ($location->latitude != $zocdoc->location->lat || $location->longitude != $zocdoc->location->lon) {
                            $location = null;
                            $newDoc = true;
                        }
                    }
                } 
                else {
                    $doctor = new \Doctor(array(
                        "name" => $zocdoc->doctor->name,
                        "gender" => $zocdoc->doctor->gender,
                        "suffix" => $zocdoc->doctor->suffix,
                        "speciality_id" => $zocdoc->doctor->specialty->id,
                        "zocdoc_id" => $zocdoc->doctor->id,
                        "practice_id" => @$practice->id
                    ));
                    $doctor->save();
                    $newDoc = true;
                }
                
                if (!$location) {
                    
                    // a new location for the doctor
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
    
    public function fetch() {
        $codes = Helper::zip_codes();
        
        $last = Helper::lastRunCode();
        foreach ($codes as $key => $zip) {
            if ($last) {
                if ($last != $zip) {
                    continue;
                }
            } 
            else {
                $last = $zip;
            }
            
            foreach ($this->_specialities as $key => $sp) {
                $response = $this->processList($zip, $sp);
                $this->save($response);
            }
            Helper::lastRunCode($last);
        }
    }

    /**
     * Fetches all the doctors for a given zipcode and saves the new ones in db
     */
    public function manual($zip, $env = "production") {
        switch ($env) {
            case 'production':
                foreach ($this->_specialities as $key => $sp) {
                    $response = $this->processList($zip, $sp);
                    $this->save($response, "saveData");
                }
                break;
            
            case 'testing':
                $response = $this->processList($zip, '153');
                $this->save($response, "saveData");
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
