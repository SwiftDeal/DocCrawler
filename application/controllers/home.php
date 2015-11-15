<?php

/**
 * The Default Example Controller Class
 *
 * @author Faizan Ayubi
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

class Home extends Controller {

    public function index() {
        $doctors = Doctor::count();
        $locations = DocSearch::count();

        $view = $this->getActionView();
        $view->set("doctors", $doctors);
        $view->set("locations", $locations);
    }

    public function register() {
    	$view = $this->getActionView();
    	if (RequestMethods::post("action") == "register") {
    		$doctor = new Doctor(array(
    			"name" => RequestMethods::post("name"),
    			"suffix" => RequestMethods::post("suffix", "MD"),
    			"speciality_id" => RequestMethods::post("speciality_id"),
    			"gender" => RequestMethods::post("gender", "he"),
    			"zocdoc_id" => 0,
    			"practice_id" => ""
    		));

    		$doctor->save();
    		$view->set("success", true);
    	}
    }
    
}
