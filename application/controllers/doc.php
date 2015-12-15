<?php

/**
 * Description of doc
 *
 * @author Faizan Ayubi
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;
use Framework\ArrayMethods as ArrayMethods;
use \Curl\Curl;

class Doc extends Admin {
    /**
     * @before _secure, _admin, changeLayout
     */
    public function create() {
        $this->seo(array(
            "title" => "Create Doctor",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
        
        if (RequestMethods::post("action") == "createDoc") {
            $doctor = new Doctor(array(
                "name" => RequestMethods::post("name") ,
                "suffix" => RequestMethods::post("suffix") ,
                "speciality_id" => RequestMethods::post("speciality_id") ,
                "gender" => RequestMethods::post("gender"),
                "bio" => RequestMethods::post("bio"),
                "zocdoc_id" => RequestMethods::post("zocdoc_id", "") ,
                "practice_id" => RequestMethods::post("practice_id", "")
            ));
            $doctor->save();
        }
    }

    /**
     * @before _secure, _admin, changeLayout
     */
    public function change($id) {
        $this->seo(array("title" => "Update Doctor","view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $doctor = Doctor::first(array("id = ?" => $id));
        
        if (RequestMethods::post("action") == "updateDoc") {
            $doctor->name = RequestMethods::post("name");
            $doctor->suffix = RequestMethods::post("suffix");
            $doctor->speciality_id = RequestMethods::post("speciality_id");
            $doctor->bio = RequestMethods::post("bio");
            $doctor->gender = RequestMethods::post("gender");
            $doctor->save();

            $view->set("success", true);
        }

        $view->set("doctor", $doctor);
    }
    
    /**
     * @before _secure, _admin, changeLayout
     */
    public function all() {
        $this->seo(array(
            "title" => "All Doctor",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
        
        $limit = RequestMethods::get("limit", 10);
        $page = RequestMethods::get("page", 1);
        $name = RequestMethods::get("name", "");
        
        $doctors = Doctor::all(array("name LIKE ?" => "%{$name}%") , array("name","suffix", "id") , "created", "desc", $limit, $page);
        $count = Doctor::count();
        
        $view->set("count", $count);
        $view->set("doctors", $doctors);
        $view->set("page", $page);
        $view->set("limit", $limit);
        $view->set("name", $name);
    }
    
    /**
     * @before _secure, _admin, changeLayout
     */
    public function bot() {
        $this->seo(array(
            "title" => "Bot",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();

        $zip = RequestMethods::get("zipcode");
        $action = RequestMethods::get("action");
        $speciality = RequestMethods::get("speciality", 153);

        if ($action == "fetch") {
            $bot = new Shared\Doc();
            $bot->manual(array("zip" => $zip, "speciality" => $speciality), "testing");  // change "testing" to "production" when project is in running phase
            $results = Shared\Doc::newInfo();

            $results = ArrayMethods::toObject($results);
            $view->set('doctors', $results->doctors);
            $view->set('total', $results->count);
        } else {
            $view->set('doctors', array());
        }
    }

    public function find() {
        $this->JSONview();
        $view = $this->getActionView();
        $name = RequestMethods::get("name");
        $speciality_id = RequestMethods::get("speciality_id");
        $limit = RequestMethods::get("limit", 10);
        $page = RequestMethods::get("page", 1);

        $doctors = Doctor::all(array("name LIKE ?" => "%{$name}%"), array("suffix", "name", "gender", "speciality_id"), "created", "desc", $limit, $page);
        $count = Doctor::count(array("name LIKE ?" => "%{$name}%"));

        $view->set("doctors", $doctors);
        $view->set("total", $count);
    }

    public function google() {
        $this->noview();
        $input = RequestMethods::get("input");

        $curl = new Curl();
        $curl->get('https://maps.googleapis.com/maps/api/place/textsearch/json', array(
            'key' => 'AIzaSyBNnY6jX7uAmLsGFsYMw5GjLtvWT98fyeA',
            'input' => $input
        ));
        $curl->close();

        $places = $curl->response;
        $results = $places->results;
        echo json_encode($results[0]);
    }

    public function googlePlace() {
        $this->noview();
        $placeid = RequestMethods::get("placeid", "ChIJN1t_tDeuEmsRUsoyG83frY4");

        $curl = new Curl();
        $curl->get('https://maps.googleapis.com/maps/api/place/details/json', array(
            'key' => 'AIzaSyBNnY6jX7uAmLsGFsYMw5GjLtvWT98fyeA',
            'placeid' => $placeid
        ));
        $curl->close();

        $place = $curl->response;
        $result = $place->result;
        //echo "<pre>", print_r($result), "</pre>";
        echo json_encode($result);
    }
    
    public function fetch() {
        $this->JSONview();
        $view = $this->getActionView();
        $speciality_id = RequestMethods::get("speciality_id");
        $location = RequestMethods::get("location", "New York");
        $zip = RequestMethods::get("zip");
        $limit = RequestMethods::get("limit", 10);
        $page = RequestMethods::get("page", 1);

        if (is_numeric($zip)) {
            $search = DocSearch::all(array("speciality_id = ?" => $speciality_id, "zip_code = ?" => $zip), array("*"), "created", "asc", $limit, $page);
            $count = DocSearch::count(array("speciality_id = ?" => $speciality_id, "zip_code = ?" => $zip));
        } else {
            $search = DocSearch::all(array("speciality_id = ?" => $speciality_id, "city = ?" => $location), array("*"), "created", "desc", $limit, $page);
            $count = DocSearch::count(array("speciality_id = ?" => $speciality_id, "city = ?" => $location));
        }

        $results = array();
        foreach ($search as $s) {
            $doctor = Doctor::first(array("id = ?" => $s->doctor_id));
            if ($doctor->practice_id) {
                $practice = Practice::first(array("id = ?" => $doctor->practice_id), array("name"));
            } else {
                $practice = null;
            }
            $results[] = array(
                "doctor" => array(
                    "name" => $doctor->name,
                    "gender" => $doctor->gender,
                    "suffix" => $doctor->suffix,
                    "bio" => strip_tags($doctor->bio),
                    "practice" => ($practice) ? $practice->name : ""
                ),
                "location" => array(
                    "lat" => $s->latitude,
                    "long" => $s->longitude,
                    "address" => $s->address,
                    "city" => $s->city,
                    "state" => $s->state_code,
                    "zip" => $s->zip_code
                )
            );
        }
        $results = ArrayMethods::toObject($results);
        $view->set("doctors", $results);
        $view->set("total", $count);
    }
}
