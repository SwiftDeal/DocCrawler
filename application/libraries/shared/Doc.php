<?php
use WebBot\lib\WebBot\Bot as Bot;
use ZocDoc\Helper as Helper;
/**
 * Controls fetching of doctor info
 *
 * @author Hemant Mann
 */
namespace Shared;

class Doc {
	/**
	 * @read
	 */
	protected $_specialities = array("345","132","346","105","143","156","98","101","385","130","127","386","106","387","110","114","398","362","373","107","128","113","104","110","116","157","151","135","117","139","152","100","336","335","119","121","153","137","122","337","108","123","109","155","129","158","387","382","408","126","142");

	public function __construct() {
		
	}

	protected function filterResult() {
		$result = str_replace("for(;;);", "", $result);
		return $result;
	}

	protected function executeRequest($key, $url) {
		$urls = array(
			"$key" => $url
		);
		$bot = new Bot($urls);
		$bot->execute();
		$document = array_pop($bot->getDocuments());

		if ($document) {
			return $document->getHttpResponse()->getBody();	
		}
		return false;
		
	}

	protected function searchUrl($zip, $speciality, $offset) {
		return 'https://www.zocdoc.com/search/searchresults?Address='.$zip.'&ForceReskin=false&Gender=-1&HospitalId=-1&InsuranceId=-1&InsurancePlanId=-1&LanguageId=1&ProcedureId=12&SpecialtyId='.$speciality.'&SubSpecialtyId=-1&LimitToThisSpecialty=false&ExcludedSpecialtyIds=&Offset='.$offset.'&PatientTypeChild=false&genderChanged=false&languageChanged=false&IsPolarisRevealed=False&StartDate=null&_=1444650384181';
	}

	function doctorsList($ids) {
		$date_end = date("Y-m-d", strtotime(date("Y-m-d")."+3 day"));
		return 'https://www.zocdoc.com/api/1/appointments/doctor_location/'.$ids.'?start='.$date_end.'&length=3&procedure_id=12&refinement_id=-1&insurance_plan_id=-1&fullDoctorInformation=false';
	}

	protected function processList($zip, $cat) {
		$results = array();
		$response = array();

		for ($i = 0; $i <= 90; $i +=10) {
			$body = $this->executeRequest('search', $this->searchUrl($zip, $cat, $i));

			$search = json_decode($this->filterResult($body));
			$ids = $search->ids;

			if (!empty($ids)) {
				$body = $this->executeRequest('doctors', $this->doctorsList($ids));	
			} else {
				$body = false;
			}

			if ($body) {
				$result = json_decode($this->filterResult($body));

				$list = $result->doctor_locations;
				foreach ($list as $key => $value) {
					$results[] = $value;
				}	
			} else {
				break;
			}
		}
		return $results;
	}

	public function fetch() {
		$codes = Helper::zip_codes();

		$name = Helper::path() . "last.txt";
		$last = file_get_contents($name);
		foreach ($codes as $key => $zip) {
			if (!empty(trim($last))) {
				if ($last != $zip) {
					continue;
				}
			} else {
				$last = $zip;
			}

			foreach ($this->_specialities as $key => $val) {
				// $response = $this->processList($zip, $val);
				// do something with the response
			}
		
			file_put_contents($name, $last);
		
		}
	}
}
