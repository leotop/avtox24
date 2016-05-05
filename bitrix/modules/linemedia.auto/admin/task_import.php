<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/csv_data.php");

if (!CModule::IncludeModule('linemedia.auto')) {
    ShowError(GetMessage('LM_AUTO_DOWNLOADER_NO_MAIN_MODULE'));
    return;
}

if (!CModule::IncludeModule('linemedia.autodownloader')) {
    ShowError(GetMessage('LM_AUTO_DOWNLOADER_NO_MODULE'));
    return;
}

// объект контроля доступа
$lm_rights = new LinemediaAutoRightsEntity(LinemediaAutoRightsEntity::$ENTITY_TYPE_PRICE);
$userPermission = $lm_rights->getDefaultRights();
if (strcmp($userPermission, LM_AUTO_MAIN_ACCESS_DENIED) == 0) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

set_time_limit(0);

$STEP = IntVal($STEP);
if ($STEP <= 0) {
	$STEP = 1;
}
if ($REQUEST_METHOD=="POST" && strlen($backButton) > 0) {
	$STEP = $STEP - 2;
}
if ($REQUEST_METHOD == "POST" && strlen($backButton2) > 0) {
	$STEP = 1;
}


/* 
 * По умолчанию первая строка не является заголовком
 */
$first_names_r = 'N';

$max_execution_time = IntVal($max_execution_time);
if ($max_execution_time <= 0) {
	$max_execution_time = 30;
}
if (strlen($CUR_LOAD_SESS_ID) <= 0) {
	$CUR_LOAD_SESS_ID = "CL".time();
}

$bAllLinesLoaded = True;
$CUR_FILE_POS = IntVal($CUR_FILE_POS);
$strError = "";
$line_num = 0;
$correct_lines = 0;
$error_lines = 0;
$killed_lines = 0;

/////////////////////////////////////////////////////////////////////

$arCatalogAvailProdFields = array(
		array("value"=>"article_original", "field"=>"article_original", "important"=>"Y", "name"=>GetMessage("LM_AUTO_DOWNLOADER_ARTICLE_ORIGINAL")),
		array("value"=>"brand_original", "field"=>"brand_original", "important"=>"Y", "name"=>GetMessage("LM_AUTO_DOWNLOADER_BRAND_ORIGINAL")),
		array("value"=>"article_analog", "field"=>"article_analog", "important"=>"Y", "name"=>GetMessage("LM_AUTO_DOWNLOADER_ARTICLE_ANALOG")),
		array("value"=>"brand_analog", "field"=>"brand_analog", "important"=>"Y", "name"=>GetMessage("LM_AUTO_DOWNLOADER_BRAND_ANALOG")),
	);

$defCatalogAvailProdFields = "article_original,brand_original,article_analog,brand_analog";

/////////////////////////////////////////////////////////////////////

function GetValueByCodeTmp($code)
{
	global $NUM_FIELDS;
	for ($i = 0; $i < $NUM_FIELDS; $i++) {
		if ($GLOBALS["field_".$i] == $code) {
			return $i;
		}
	}
	return -1;
}

/////////////////////////////////////////////////////////////////////
if (($REQUEST_METHOD == "POST" || $CUR_FILE_POS > 0) && $STEP > 1 && check_bitrix_sessid()) {
	//*****************************************************************//
	if ($STEP > 1) {
		//*****************************************************************//
		$DATA_FILE_NAME = "";

		if (is_uploaded_file($_FILES["DATA_FILE"]["tmp_name"])) {
			if (strtolower(GetFileExtension($_FILES["DATA_FILE"]["name"])) != "csv") {
				$strError .= GetMessage("IBLOCK_ADM_IMP_NOT_CSV")."<br>";
			} else {
				$DATA_FILE_NAME = "/".COption::GetOptionString("main", "upload_dir", "upload")."/".basename($_FILES["DATA_FILE"]["name"]);
				if ($APPLICATION->GetFileAccessPermission($DATA_FILE_NAME) >= "W") {
					copy($_FILES["DATA_FILE"]["tmp_name"], $_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME);
				} else {
					$DATA_FILE_NAME = "";
                }
			}
		}

		if (strlen($strError) <= 0) {
			if (strlen($DATA_FILE_NAME) <= 0) {
				if (strlen($URL_DATA_FILE) > 0) {
					$URL_DATA_FILE = trim(str_replace("\\", "/", trim($URL_DATA_FILE)), "/");
					$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$URL_DATA_FILE);
					if (
						(strlen($FILE_NAME) > 1) &&
						($FILE_NAME === "/".$URL_DATA_FILE) &&
						file_exists($_SERVER["DOCUMENT_ROOT"].$FILE_NAME) &&
						is_file($_SERVER["DOCUMENT_ROOT"].$FILE_NAME) &&
						($APPLICATION->GetFileAccessPermission($FILE_NAME) >= "W")
					) {
						$DATA_FILE_NAME = $FILE_NAME;
					}
				}
			}

			if (strlen($DATA_FILE_NAME) <= 0)
				$strError .= GetMessage("IBLOCK_ADM_IMP_NO_DATA_FILE")."<br>";
		}

		if (strlen($strError) <= 0) {
			if ($CUR_FILE_POS>0 && is_set($_SESSION, $CUR_LOAD_SESS_ID) && is_set($_SESSION[$CUR_LOAD_SESS_ID], "LOAD_SCHEME")) {
				parse_str($_SESSION[$CUR_LOAD_SESS_ID]["LOAD_SCHEME"]);
				$STEP = 4;
			}
		}

		if (strlen($strError) > 0) {
			$STEP = 1;
        }
		//*****************************************************************//
	}

	if ($STEP > 2) {
		//*****************************************************************//
		$csvFile = new CCSVData();
		$csvFile->LoadFile($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME);

		if ($fields_type!="F" && $fields_type!="R")
			$strError .= GetMessage("IBLOCK_ADM_IMP_NO_FILE_FORMAT")."<br>";

		$arDataFileFields = array();
		if (strlen($strError) <= 0) {
			$fields_type = (($fields_type=="F") ? "F" : "R" );

			$csvFile->SetFieldsType($fields_type);

			if ($fields_type == "R") {
				$first_names_r = (($first_names_r=="Y") ? "Y" : "N" );
				$csvFile->SetFirstHeader(($first_names_r=="Y")?true:false);

				$delimiter_r_char = "";
				switch ($delimiter_r) {
					case "TAB":
						$delimiter_r_char = "\t";
						break;
					case "ZPT":
						$delimiter_r_char = ",";
						break;
					case "SPS":
						$delimiter_r_char = " ";
						break;
					case "OTR":
						$delimiter_r_char = substr($delimiter_other_r, 0, 1);
						break;
					case "TZP":
						$delimiter_r_char = ";";
						break;
				}

				if (strlen($delimiter_r_char) != 1) {
					$strError .= GetMessage("IBLOCK_ADM_IMP_NO_DELIMITER")."<br>";
                }
				if (strlen($strError) <= 0) {
					$csvFile->SetDelimiter($delimiter_r_char);
				}
			} else {
				$first_names_f = (($first_names_f=="Y") ? "Y" : "N" );
				$csvFile->SetFirstHeader(($first_names_f=="Y")?true:false);

				if (strlen($metki_f) <= 0) {
					$strError .= GetMessage("IBLOCK_ADM_IMP_NO_METKI")."<br>";
                }
				if (strlen($strError) <= 0) {
					$arMetkiTmp = preg_split("/[\D]/i", $metki_f);

					$arMetki = array();
					for ($i = 0; $i < count($arMetkiTmp); $i++) {
						if (IntVal($arMetkiTmp[$i]) > 0) {
							$arMetki[] = IntVal($arMetkiTmp[$i]);
						}
					}

					if (!is_array($arMetki) || count($arMetki)<1)
						$strError .= GetMessage("IBLOCK_ADM_IMP_NO_METKI")."<br>";

					if (strlen($strError) <= 0) {
						$csvFile->SetWidthMap($arMetki);
					}

				}
			}

			if (strlen($strError) <= 0) {
				$bFirstHeaderTmp = $csvFile->GetFirstHeader();
				$csvFile->SetFirstHeader(false);
				if ($arRes = $csvFile->Fetch()) {
					for ($i = 0; $i < count($arRes); $i++) {
						$arDataFileFields[$i] = $arRes[$i];
					}
				} else {
					$strError .= GetMessage("IBLOCK_ADM_IMP_NO_DATA")."<br>";
				}
				$NUM_FIELDS = count($arDataFileFields);
			}
		}

		if (strlen($strError)>0) {
			$STEP = 2;
        }
		//*****************************************************************//
	}

	if ($STEP > 3) {
		//*****************************************************************//
		$bFieldsPres = False;
		for ($i = 0; $i < $NUM_FIELDS; $i++) {
			if (strlen(${"field_".$i})>0) {
				$bFieldsPres = True;
				break;
			}
		}
		if (!$bFieldsPres)
			$strError .= GetMessage("IBLOCK_ADM_IMP_NO_FIELDS")."<br>";

		if (strlen($strError)<=0)
		{
			$csvFile->SetPos($CUR_FILE_POS);
			if ($CUR_FILE_POS<=0 && $bFirstHeaderTmp)
			{
				$arRes = $csvFile->Fetch();
			}

			if ($CUR_FILE_POS>0 && is_set($_SESSION, $CUR_LOAD_SESS_ID))
			{
				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "tmpid"))
					$tmpid = $_SESSION[$CUR_LOAD_SESS_ID]["tmpid"];
				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "line_num"))
					$line_num = intval($_SESSION[$CUR_LOAD_SESS_ID]["line_num"]);
				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "correct_lines"))
					$correct_lines = intval($_SESSION[$CUR_LOAD_SESS_ID]["correct_lines"]);
				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "error_lines"))
					$error_lines = intval($_SESSION[$CUR_LOAD_SESS_ID]["error_lines"]);
				if (is_set($_SESSION[$CUR_LOAD_SESS_ID], "killed_lines"))
					$killed_lines = intval($_SESSION[$CUR_LOAD_SESS_ID]["killed_lines"]);
			}


			// Prepare arrays for elements load
			$strAvailProdFields = $defCatalogAvailProdFields;
			$arAvailProdFields = explode(",", $strAvailProdFields);
			$arAvailProdFields_names = array();
			for ($i = 0; $i < count($arAvailProdFields); $i++)
			{
				for ($j = 0; $j < count($arCatalogAvailProdFields); $j++)
				{
					if ($arCatalogAvailProdFields[$j]["value"]==$arAvailProdFields[$i])
					{
						$arAvailProdFields_names[$arAvailProdFields[$i]] = array(
							"field" => $arCatalogAvailProdFields[$j]["field"],
							"important" => $arCatalogAvailProdFields[$j]["important"]
							);
						break;
					}
				}
			}

			$arSectionCache = array();
			$arEnumCache = array();

function FetchAssoc(&$csvFile)
{
	global $NUM_FIELDS;
	$ar = $csvFile->Fetch();
	if($ar)
	{
		$result = array();
		for($i = 0; $i < $NUM_FIELDS; $i++)
			$result[$GLOBALS["field_".$i]] = trim($ar[$i]);
		return $result;
	}
	return $ar;
}
			
			// Main loop
			while($arRes = FetchAssoc($csvFile))
			{
				$strErrorR = "";
				$line_num++;
                
                /*
                * *********************************** Добавление аналога в БД **************************************
                */
                $import_id = basename($DATA_FILE_NAME) . '_' . date('Y-m-d_G:i');
                
                $analog = new LinemediaAutoAnalogsSimpleAnalog();
                
                try{
                    $analog->add(array(
                        'import_id'         => $import_id,
                        'article_original'  => $arRes['article_original'],
                        'brand_title_original' => $arRes['brand_original'],
                        'article_analog'    => $arRes['article_analog'],
                        'brand_title_analog'   => $arRes['brand_analog'],
                    ));
                } catch (Exception $e) {
                    $strErrorR = $e->GetMessage();
                }
                
				if (strlen($strErrorR)<=0)
				{
					$correct_lines++;
				}
				else
				{
					$error_lines++;
					$strError .= $strErrorR;
				}

				if (intval($max_execution_time)>0 && (getmicrotime()-START_EXEC_TIME)>intval($max_execution_time))
				{
					$bAllLinesLoaded = False;
					break;
				}
			}
		}

		if (strlen($strError)>0)
		{
			$strError .= GetMessage("IBLOCK_ADM_IMP_TOTAL_ERRS")." ".$error_lines.".<br>";
			$strError .= GetMessage("IBLOCK_ADM_IMP_TOTAL_COR1")." ".$correct_lines." ".GetMessage("IBLOCK_ADM_IMP_TOTAL_COR2")."<br>";
			$STEP = 3;
		}
		//*****************************************************************//
	}
	//*****************************************************************//
}
/////////////////////////////////////////////////////////////////////

$APPLICATION->SetTitle(GetMessage("IBLOCK_ADM_IMP_PAGE_TITLE").$STEP);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
CAdminMessage::ShowMessage($strError);

if (!$bAllLinesLoaded) {
	$strParams = bitrix_sessid_get()."&CUR_FILE_POS=".$curFilePos."&CUR_LOAD_SESS_ID=".urlencode($CUR_LOAD_SESS_ID)."&STEP=4&URL_DATA_FILE=".urlencode($DATA_FILE_NAME)."&IBLOCK_ID=".$IBLOCK_ID."&fields_type=".urlencode($fields_type)."&max_execution_time=".IntVal($max_execution_time);
	if ($fields_type=="R")
		$strParams .= "&delimiter_r=".urlencode($delimiter_r)."&delimiter_other_r=".urlencode($delimiter_other_r)."&first_names_r=".urlencode($first_names_r);
	else
		$strParams .= "&metki_f=".urlencode($metki_f)."&first_names_f=".urlencode($first_names_f);
	?>

	<?= GetMessage("IBLOCK_ADM_IMP_AUTO_REFRESH");?>
	<a href="<?= $APPLICATION->GetCurPage()?>?lang=<?= LANG?>&<?= $strParams ?>"><?= GetMessage("IBLOCK_ADM_IMP_AUTO_REFRESH_STEP");?></a><br>

	<script language="JavaScript" type="text/javascript">
	<!--
	function DoNext()
	{
		window.location="<?= $APPLICATION->GetCurPage()?>?lang=<?= LANG?>&<?= $strParams ?>";
	}
	setTimeout('DoNext()', 2000);
	//-->
	</script>
	<?
}
?>

<form method="POST" action="<?= $sDocPath?>?lang=<?= LANG ?>" ENCTYPE="multipart/form-data" name="dataload" id="dataload">

<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("IBLOCK_ADM_IMP_TAB1"), "ICON" => "iblock", "TITLE" => GetMessage("IBLOCK_ADM_IMP_TAB1_ALT")),
	array("DIV" => "edit2", "TAB" => GetMessage("IBLOCK_ADM_IMP_TAB2"), "ICON" => "iblock", "TITLE" => GetMessage("IBLOCK_ADM_IMP_TAB2_ALT")),
	array("DIV" => "edit3", "TAB" => GetMessage("IBLOCK_ADM_IMP_TAB3"), "ICON" => "iblock", "TITLE" => GetMessage("IBLOCK_ADM_IMP_TAB3_ALT")),
	array("DIV" => "edit4", "TAB" => GetMessage("IBLOCK_ADM_IMP_TAB4"), "ICON" => "iblock", "TITLE" => GetMessage("IBLOCK_ADM_IMP_TAB4_ALT")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();

if ($STEP == 1) {
	?>
 	<tr>
		<td><?= GetMessage("IBLOCK_ADM_IMP_DATA_FILE") ?></td>
		<td>
			<input type="text" name="URL_DATA_FILE" value="<?= htmlspecialchars($URL_DATA_FILE)?>" size="30">
			<input type="button" value="<?= GetMessage("IBLOCK_ADM_IMP_OPEN") ?>" OnClick="BtnClick()">
			<?
			CAdminFileDialog::ShowScript
			(
				Array(
					"event" => "BtnClick",
					"arResultDest" => array("FORM_NAME" => "dataload", "FORM_ELEMENT_NAME" => "URL_DATA_FILE"),
					"arPath" => array("SITE" => SITE_ID, "PATH" =>"/".COption::GetOptionString("main", "upload_dir", "upload") . '/linemedia.autoanalogssimple/new/'),
					"select" => 'F',// F - file only, D - folder only
					"operation" => 'O',// O - open, S - save
					"showUploadTab" => true,
					"showAddToMenuTab" => false,
					"fileFilter" => 'csv',
					"allowAllFiles" => true,
					"SaveConfig" => true,
				)
			);
			?>
		</td>
	</tr>
	<?
}

$tabControl->EndTab();
?>

<?
$tabControl->BeginNextTab();

if ($STEP == 2) {
	?>
	<tr>
		<td valign="middle" colspan="2">
			<script language="JavaScript">
    			function DeactivateAllExtra()
    			{
    				document.getElementById("table_r").disabled = true;
    				document.getElementById("table_r1").disabled = true;
    				document.getElementById("table_r2").disabled = true;
    				document.getElementById("table_f").disabled = true;
    				document.getElementById("table_f1").disabled = true;
    				document.getElementById("table_f2").disabled = true;
    
    				document.dataload.metki_f.disabled = true;
    				/*document.dataload.first_names_f.disabled = true; */
    				document.getElementById("first_names_f_Y").disabled = true;
    
    				var i;
    				for (i = 0 ; i < document.dataload.delimiter_r.length; i++) {
    					document.dataload.delimiter_r[i].disabled = true;
    				}
    				document.dataload.delimiter_other_r.disabled = true;
    				/*document.dataload.first_names_r.disabled = true; */
    				document.getElementById("first_names_r_Y").disabled = true;
    			}
    
    			function ChangeExtra()
    			{
    				if (document.dataload.fields_type[0].checked) {
    					document.getElementById("table_r").disabled = false;
    					document.getElementById("table_r1").disabled = false;
    					document.getElementById("table_r2").disabled = false;
    					document.getElementById("table_f").disabled = true;
    					document.getElementById("table_f1").disabled = true;
    					document.getElementById("table_f2").disabled = true;
    
    					var i;
    					for (i = 0 ; i < document.dataload.delimiter_r.length; i++) {
    						document.dataload.delimiter_r[i].disabled = false;
    					}
    					document.dataload.delimiter_other_r.disabled = false;
    					/* document.dataload.first_names_r.disabled = false; */
    					document.getElementById("first_names_r_Y").disabled = false;
    
    					document.dataload.metki_f.disabled = true;
    					/* document.dataload.first_names_f.disabled = true; */
    					document.getElementById("first_names_f_Y").disabled = true;
    
    					document.dataload.submit_btn.disabled = false;
    				} else {
    					if (document.dataload.fields_type[1].checked) {
    						document.getElementById("table_r").disabled = true;
    						document.getElementById("table_r1").disabled = true;
    						document.getElementById("table_r2").disabled = true;
    						document.getElementById("table_f").disabled = false;
    						document.getElementById("table_f1").disabled = false;
    						document.getElementById("table_f2").disabled = false;
    
    						var i;
    						for (i = 0 ; i < document.dataload.delimiter_r.length; i++) {
    							document.dataload.delimiter_r[i].disabled = true;
    						}
    						document.dataload.delimiter_other_r.disabled = true;
    						/* document.dataload.first_names_r.disabled = true; */
    						document.getElementById("first_names_r_Y").disabled = true;
    
    						document.dataload.metki_f.disabled = false;
    						/* document.dataload.first_names_f.disabled = false; */
    						document.getElementById("first_names_f_Y").disabled = false;
    
    						document.dataload.submit_btn.disabled = false;
    					}
    				}
    			}
			</script>

			<input type="radio" name="fields_type" id="fields_type_R" value="R" <?if ($fields_type=="R" || strlen($fields_type)<=0) echo "checked";?> onClick="ChangeExtra()"><label for="fields_type_R"><?= GetMessage("IBLOCK_ADM_IMP_RAZDELITEL") ?></label><br>
			<input type="radio" name="fields_type" id="fields_type_F" value="F" <?if ($fields_type=="F") echo "checked";?> onClick="ChangeExtra()"><label for="fields_type_F"><?= GetMessage("IBLOCK_ADM_IMP_FIXED") ?></label>

		</td>
	</tr>

	<tr id="table_r" class="heading">
		<td colspan="2"><?= GetMessage("IBLOCK_ADM_IMP_RAZDEL1") ?></td>
	</tr>
	<tr id="table_r1">
		<td valign="top" width="40%"><?= GetMessage("IBLOCK_ADM_IMP_RAZDEL_TYPE") ?></td>
		<td valign="top" width="60%">
			<input type="radio" name="delimiter_r" id="delimiter_r_TZP" value="TZP" <?if ($delimiter_r=="TZP" || strlen($delimiter_r)<=0) echo "checked"?>><label for="delimiter_r_TZP"><?= GetMessage("IBLOCK_ADM_IMP_TZP") ?></label><br>
			<input type="radio" name="delimiter_r" id="delimiter_r_ZPT" value="ZPT" <?if ($delimiter_r=="ZPT") echo "checked"?>><label for="delimiter_r_ZPT"><?= GetMessage("IBLOCK_ADM_IMP_ZPT") ?></label><br>
			<input type="radio" name="delimiter_r" id="delimiter_r_TAB" value="TAB" <?if ($delimiter_r=="TAB") echo "checked"?>><label for="delimiter_r_TAB"><?= GetMessage("IBLOCK_ADM_IMP_TAB") ?></label><br>
			<input type="radio" name="delimiter_r" id="delimiter_r_SPS" value="SPS" <?if ($delimiter_r=="SPS") echo "checked"?>><label for="delimiter_r_SPS"><?= GetMessage("IBLOCK_ADM_IMP_SPS") ?></label><br>
			<input type="radio" name="delimiter_r" id="delimiter_r_OTR" value="OTR" <?if ($delimiter_r=="OTR") echo "checked"?>><label for="delimiter_r_OTR"><?= GetMessage("IBLOCK_ADM_IMP_OTR") ?></label>
			<input type="text" name="delimiter_other_r" size="3" value="<?= htmlspecialchars($delimiter_other_r) ?>">
		</td>
	</tr>
	<tr id="table_r2">
		<td><?= GetMessage("IBLOCK_ADM_IMP_FIRST_NAMES") ?></td>
		<td>
			<input type="hidden" name="first_names_r" id="first_names_r_N" value="N">
			<input type="checkbox" name="first_names_r" id="first_names_r_Y" value="Y" <?if ($first_names_r!="N") echo "checked"?>>
		</td>
	</tr>

	<tr id="table_f" class="heading">
		<td colspan="2"><?= GetMessage("IBLOCK_ADM_IMP_FIX1") ?></td>
	</tr>
	<tr id="table_f1">
		<td valign="top" width="40%">
			<?= GetMessage("IBLOCK_ADM_IMP_FIX_MET") ?><br>
			<small><?= GetMessage("IBLOCK_ADM_IMP_FIX_MET_DESCR") ?></small>
		</td>
		<td valign="top" width="60%">
			<textarea name="metki_f" rows="7" cols="3"><?= htmlspecialchars($metki_f) ?></textarea>
		</td>
	</tr>
	<tr id="table_f2">
		<td><?= GetMessage("IBLOCK_ADM_IMP_FIRST_NAMES") ?></td>
		<td>
			<input type="hidden" name="first_names_f" id="first_names_f_N" value="N">
			<input type="checkbox" name="first_names_f" id="first_names_f_Y" value="Y" <?if ($first_names_f=="Y") echo "checked"?>>
		</td>
	</tr>

	<tr class="heading">
		<td colspan="2"><?= GetMessage("IBLOCK_ADM_IMP_DATA_SAMPLES") ?></td>
	</tr>
	<tr>
		<td align="center" colspan="2">
			<?
			$sContent = "";
			if(strlen($DATA_FILE_NAME)>0)
			{
				$DATA_FILE_NAME = trim(str_replace("\\", "/", trim($DATA_FILE_NAME)), "/");
				$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$DATA_FILE_NAME);
				if((strlen($FILE_NAME) > 1) && ($FILE_NAME == "/".$DATA_FILE_NAME) && $APPLICATION->GetFileAccessPermission($FILE_NAME)>="W")
				{
					$file_id = fopen($_SERVER["DOCUMENT_ROOT"].$FILE_NAME, "rb");
					$sContent = fread($file_id, 10000);
					fclose($file_id);
				}
			}
			?>
			<textarea name="data" wrap="OFF" rows="7" cols="80"><?= htmlspecialchars($sContent) ?></textarea>
		</td>
	</tr>
	<?
}

$tabControl->EndTab();
?>

<?/*    Поля    */
$tabControl->BeginNextTab();

if ($STEP == 3)
{
	?>
	<tr class="heading">
		<td colspan="2"><?= GetMessage("IBLOCK_ADM_IMP_FIELDS_SOOT") ?></td>
	</tr>

	<?
	$arAvailFields = array();

	$strVal = $defCatalogAvailProdFields;
	$arVal = explode(",", $strVal);
	$arCatalogAvailProdFields_tmp = $arCatalogAvailProdFields;
	for ($i = 0; $i < count($arVal); $i++)
	{
		for ($j = 0; $j < count($arCatalogAvailProdFields_tmp); $j++)
		{
			if ($arVal[$i]==$arCatalogAvailProdFields_tmp[$j]["value"]
				&& $arVal[$i]!="IE_ID")
			{
				$arAvailFields[] = array("value"=>$arCatalogAvailProdFields_tmp[$j]["value"], "name"=>$arCatalogAvailProdFields_tmp[$j]["name"]);
				break;
			}
		}
	}

	for ($k = 0; $k < $NUM_CATALOG_LEVELS; $k++)
	{
		$strVal = $defCatalogAvailGroupFields;
		$arVal = explode(",", $strVal);
		for ($i = 0; $i < count($arVal); $i++)
		{
			for ($j = 0; $j < count($arCatalogAvailGroupFields); $j++)
			{
				if ($arVal[$i]==$arCatalogAvailGroupFields[$j]["value"])
				{
					$arAvailFields[] = array("value"=>$arCatalogAvailGroupFields[$j]["value"].$k, "name"=>GetMessage("IBLOCK_ADM_IMP_FI_GROUP_LEV")." ".($k+1).": ".$arCatalogAvailGroupFields[$j]["name"]);
					break;
				}
			}
		}
	}

	for ($i = 0; $i < count($arDataFileFields); $i++)
	{
		?>
		<tr>
			<td valign="top" width="40%">
				<b><?= GetMessage("IBLOCK_ADM_IMP_FIELD") ?> <?= $i+1 ?></b> (<?= htmlspecialchars($arDataFileFields[$i]);?>):
			</td>
			<td valign="top" width="60%">
				<select name="field_<?= $i ?>">
					<option value=""> - </option>
					<?
					for ($j = 0; $j < count($arAvailFields); $j++)
					{
						$bSelected = ${"field_".$i}==$arAvailFields[$j]["value"];
						if(!$bSelected && !isset(${"field_".$i}))
							$bSelected = $arAvailFields[$j]["value"]==$arDataFileFields[$i];
						if(!$bSelected && !isset(${"field_".$i}))
							$bSelected = $arAvailFields[$j]["code"]==$arDataFileFields[$i];
						?>
						<option value="<?= $arAvailFields[$j]["value"] ?>" <?if ($bSelected) echo "selected" ?>><?= htmlspecialchars($arAvailFields[$j]["name"]) ?></option>
						<?
					}
					?>
				</select>
			</td>
		</tr>
		<?
	}
	?>

	<tr class="heading">
		<td colspan="2"><?= GetMessage("IBLOCK_ADM_IMP_ADDIT_SETTINGS") ?></td>
	</tr>
	<tr>
		<td valign="top"><?= GetMessage("IBLOCK_ADM_IMP_AUTO_STEP_TIME");?>:</td>
		<td valign="top" align="left">
			<input type="text" name="max_execution_time" size="6" value="<?= htmlspecialchars($max_execution_time)?>"><br>
			<small><?= GetMessage("IBLOCK_ADM_IMP_AUTO_STEP_TIME_NOTE");?><br></small>
		</td>
	</tr>

	<tr class="heading">
		<td colspan="2"><?= GetMessage("IBLOCK_ADM_IMP_DATA_SAMPLES") ?></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<?
			$sContent = "";
			if (strlen($DATA_FILE_NAME)>0) {
				$DATA_FILE_NAME = trim(str_replace("\\", "/", trim($DATA_FILE_NAME)), "/");
				$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$DATA_FILE_NAME);
				if ((strlen($FILE_NAME) > 1) && ($FILE_NAME == "/".$DATA_FILE_NAME) && $APPLICATION->GetFileAccessPermission($FILE_NAME)>="W") {
					$file_id = fopen($_SERVER["DOCUMENT_ROOT"].$FILE_NAME, "rb");
					$sContent = fread($file_id, 10000);
					fclose($file_id);
				}
			}
			?>
			<textarea name="data" wrap="OFF" rows="7" cols="80"><?= htmlspecialchars($sContent) ?></textarea>
		</td>
	</tr>
	<?
}

$tabControl->EndTab();
?>

<?
$tabControl->BeginNextTab();

if ($STEP == 4) {
	?>
	<tr>
		<td valign="middle" colspan="2" nowrap>
			<b><?
			if (!$bAllLinesLoaded)
				echo GetMessage("IBLOCK_ADM_IMP_AUTO_REFRESH_CONTINUE");
			else
				echo GetMessage("IBLOCK_ADM_IMP_SUCCESS");
			?></b>
		</td>
	</tr>
	<tr>
		<td valign="middle" colspan="2" nowrap>
			<?= GetMessage("IBLOCK_ADM_IMP_SU_ALL") ?> <b><?= $line_num ?></b><br>
			<?= GetMessage("IBLOCK_ADM_IMP_SU_CORR") ?> <b><?= $correct_lines ?></b><br>
			<?= GetMessage("IBLOCK_ADM_IMP_SU_ER") ?> <b><?= $error_lines ?></b><br>
			<?
    			if ($outFileAction == "D") {
    				echo GetMessage("IBLOCK_ADM_IMP_SU_KILLED")." <b>".$killed_lines."</b>";
    			} elseif ($outFileAction == "F") {
                    
    			} else {	// H
                    echo GetMessage("IBLOCK_ADM_IMP_SU_HIDED")." <b>".$killed_lines."</b>";
    			}
			?>

		</td>
	</tr>
<?
}
$tabControl->EndTab();
?>

<? $tabControl->Buttons() ?>

<? if ($STEP < 4): ?>
	<input type="hidden" name="STEP" value="<?= $STEP + 1;?>" />
	<?= bitrix_sessid_post() ?>
	<? if ($STEP > 1): ?>
		<input type="hidden" name="URL_DATA_FILE" value="<?= htmlspecialchars($DATA_FILE_NAME) ?>" />
		<input type="hidden" name="IBLOCK_ID" value="<?= $IBLOCK_ID ?>" />
	<? endif ?>

	<? if ($STEP <> 2): ?>
		<input type="hidden" name="fields_type" value="<?= htmlspecialchars($fields_type) ?>" />
		<input type="hidden" name="delimiter_r" value="<?= htmlspecialchars($delimiter_r) ?>" />
		<input type="hidden" name="delimiter_other_r" value="<?= htmlspecialchars($delimiter_other_r) ?>" />
		<input type="hidden" name="first_names_r" value="<?= htmlspecialchars($first_names_r) ?>" />
		<input type="hidden" name="metki_f" value="<?= htmlspecialchars($metki_f) ?>" />
		<input type="hidden" name="first_names_f" value="<?= htmlspecialchars($first_names_f) ?>" />
	<? endif ?>

	<? if ($STEP <> 3): ?>
		<? foreach ($_POST as $name => $value): ?>
			<? if (preg_match("/^field_(\\d+)$/", $name)): ?>
				<input type="hidden" name="<?= $name?>" value="<?= htmlspecialchars($value) ?>" />
			<? endif ?>
		<? endforeach ?>
		<input type="hidden" name="max_execution_time" value="<?= htmlspecialchars($max_execution_time)?>">
	<? endif ?>

	<? if ($STEP > 1): ?>
	   <input type="submit" name="backButton" value="&lt;&lt; <?= GetMessage("IBLOCK_ADM_IMP_BACK") ?>" />
	<? endif ?>
	<input type="submit" value="<?= ($STEP == 3) ? GetMessage("IBLOCK_ADM_IMP_NEXT_STEP_F") : GetMessage("IBLOCK_ADM_IMP_NEXT_STEP") ?> &gt;&gt;" name="submit_btn" />

	<? if ($STEP == 2) { ?>
		<script language="JavaScript">
			DeactivateAllExtra();
			ChangeExtra();
		</script>
    <? } ?>
<? else: ?>
	<input type="submit" name="backButton2" value="&lt;&lt; <?= GetMessage("IBLOCK_ADM_IMP_2_1_STEP") ?>" />
<? endif ?>

<? $tabControl->End() ?>

</form>

<script language="JavaScript">
    <!--
    <? if ($STEP < 2): ?>
        tabControl.SelectTab("edit1");
        tabControl.DisableTab("edit2");
        tabControl.DisableTab("edit3");
        tabControl.DisableTab("edit4");
    <? elseif ($STEP == 2): ?>
        tabControl.SelectTab("edit2");
        tabControl.DisableTab("edit1");
        tabControl.DisableTab("edit3");
        tabControl.DisableTab("edit4");
    <? elseif ($STEP == 3): ?>
        tabControl.SelectTab("edit3");
        tabControl.DisableTab("edit1");
        tabControl.DisableTab("edit2");
        tabControl.DisableTab("edit4");
    <? elseif ($STEP > 3): ?>
        tabControl.SelectTab("edit4");
        tabControl.DisableTab("edit1");
        tabControl.DisableTab("edit2");
        tabControl.DisableTab("edit3");
    <? endif; ?>
    //-->
</script>

<? require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php") ?>
