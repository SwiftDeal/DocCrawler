<?php
namespace ZocDoc;

class Helper {
	private function __construct() {
		// do nothing
	}

	private function __clone() {
		// do nothing
	}

	protected static function path() {
		$file = dirname(__FILE__). '/data/';
		return $file;
	}

	public static function zip_codes() {
		$file = self::path().'major-codes.php';
		require_once($file);
		return $zip_codes;
	}

	public static function lastRunCode($code = "") {
		$file = self::path(). 'last.txt';
		if ($code) {
			file_put_contents($file, $code);
		} else {
			$content = file_get_contents($file);
			return ($content) ? $content : false;	
		}
	}

	public static function searchUrl($zip, $speciality, $offset) {
	    return 'https://www.zocdoc.com/search/searchresults?Address=' . $zip . '&ForceReskin=false&Gender=-1&HospitalId=-1&InsuranceId=-1&InsurancePlanId=-1&LanguageId=1&ProcedureId=12&SpecialtyId=' . $speciality . '&SubSpecialtyId=-1&LimitToThisSpecialty=false&ExcludedSpecialtyIds=&Offset=' . $offset . '&PatientTypeChild=false&genderChanged=false&languageChanged=false&IsPolarisRevealed=False&StartDate=null&_=1444650384181';
	}
	
	public static function doctorsList($ids) {
	    $date_end = date("Y-m-d", strtotime(date("Y-m-d") . "+3 day"));
	    return 'https://www.zocdoc.com/api/1/appointments/doctor_location/' . $ids . '?start=' . $date_end . '&length=3&procedure_id=12&refinement_id=-1&insurance_plan_id=-1&fullDoctorInformation=false';
	}

	public static function insurance($doc_id) {
		return 'https://www.zocdoc.com/insuranceinformation/ProfessionalInsurances?id=' . $doc_id;
	}

}
