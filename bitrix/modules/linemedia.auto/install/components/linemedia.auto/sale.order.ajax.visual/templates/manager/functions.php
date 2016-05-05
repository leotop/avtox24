<?
function LMFindUserID($tag_name, $tag_value, $user_name="", $form_name = "form1", $tag_size = "3", $tag_maxlength="", $button_value = "...", $tag_class="typeinput", $button_class="tablebodybutton",
					 $search_page="/bitrix/admin/user_search.php", $get_user_page ='/bitrix/admin/get_user.php',$templateFolder)
{
	/** @global CMain $APPLICATION */
	global $APPLICATION;
	CJSCore::Init(array("jquery"));
	$tag_name_x = preg_replace("/([^a-z0-9]|\\[|\\])/is", "x", $tag_name);
	if($APPLICATION->GetGroupRight("main") >= "R") {
		$strReturn = "
			<input type=\"text\" name=\"".$tag_name."\" id=\"".$tag_name."\" value=\"".htmlspecialcharsbx($tag_value)."\" size=\"".$tag_size."\" maxlength=\"".$tag_maxlength."\" class=\"".$tag_class."\">
			<iframe style=\"width:0px; height:0px; border:0px\" src=\"javascript:''\" name=\"hiddenframe".$tag_name."\" id=\"hiddenframe".$tag_name."\"></iframe>
			<input class=\"".$button_class."\" type=\"button\" name=\"FindUser\" id=\"FindUser\" OnClick=\"window.open('".$search_page."?lang=".LANGUAGE_ID."&FN=".$form_name."&FC=".$tag_name."', '', 'scrollbars=yes,resizable=yes,width=760,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));\" value=\"".$button_value."\">
			<span id=\"div_".$tag_name."\" class=\"adm-filter-text-search\">".$user_name."</span>
			<script type=\"text/javascript\">
		";


		$strReturn.= "</script>";
	}
	else {
		$strReturn = "
		<input type=\"text\" name=\"$tag_name\" id=\"$tag_name\" value=\"".htmlspecialcharsbx($tag_value)."\" size=\"$tag_size\" maxlength=\"strMaxLenght\">
		<input type=\"button\" name=\"FindUser\" id=\"FindUser\" OnClick=\"window.open('".$search_page."?lang=".LANGUAGE_ID."&FN=$form_name&FC=$tag_name', '', 'scrollbars=yes,resizable=yes,width=760,height=560,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5));\" value=\"$button_value\">
		$user_name
		";
	}

	return $strReturn;
}