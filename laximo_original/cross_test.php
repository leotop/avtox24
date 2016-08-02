<?
ini_set('display_errors', 1);
// Including localization file
include_once('config.php');
include_once('localization_'.Config::$ui_localization.'.php');
include('guayaquillib'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'requestAm.php');

function getLaximoCrosses($article) {
	$request = new GuayaquilRequestAM('ru_RU');
	$request->appendFindOEM($article, "crosses");
	$data = $request->query();
	
	$return = array();

	if ($request->error != '') {
	    //echo $request->error;
	} else {
		$data = simplexml_load_string($data);
		$data = $data[0]->FindOEM->detail;
	
		if ($data) {
	        foreach ($data as $detail) {
	            foreach ($detail->replacements->replacement as $replacement) {
	            	$detail = $replacement->detail;
	            	$return[strtolower($detail['oem']) . "|" . strtoupper($detail['manufacturer'])] = array(
						'title' => (string) $detail['name'],
						'article' => (string) $detail['oem'],
						'brand_title' => (string) $detail['manufacturer'],
						'analog_type' => 1,
						'analog-source' => 'Laximo'
					);
	            }
	        }
	    }
		
		return $return;
	}
}
?>