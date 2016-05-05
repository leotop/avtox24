<?
/**
 * Linemedia Autoportal
 * Autodecdoc module
 * common-settings
 * 
 * @author  Linemedia
 * @since   22/01/2012
 * @link    http://auto.linemedia.ru/
 */


$u = new CAdminPopup(
    "mnu_PART_SEARCH_URL",
    "mnu_PART_SEARCH_URL",
    array(
        array(
            'TEXT' => "Артикул",
            'ONCLICK' => "__SetUrlVar('#ARTICLE#', 'mnu_PART_SEARCH_URL', 'PART_SEARCH_URL')",
            'TITLE' => "#ARTICLE# - Артикул"
        ),
    ),
    array("zIndex" => 2000)
);
$u->Show();

?>
<script type="text/javascript">
    function __SetUrlVar(id, mnu_id, el_id)
    {
        var mnu_list = eval(mnu_id);
        var obj_ta = document.getElementById(el_id);
        obj_ta.focus();
        obj_ta.value += id;

        mnu_list.PopupHide();
        BX.fireEvent(obj_ta, 'change');
        obj_ta.focus();
    }
    
    function __ShUrlVars(div, el_id)
    {
        var pos = jsUtils.GetRealPos(div);
        var mnu_list = eval('mnu_'+el_id);
        setTimeout(function(){mnu_list.PopupShow(pos); }, 10);
    }
</script>
<tr>
    <td width="50%">
        <label for="LM_AUTO_TECDOC_PART_SEARCH_PAGE">
            <?= GetMessage('LM_AUTO_TECDOC_PART_SEARCH_PAGE') ?>:
        </label>
    </td>
    <td>
        <input type="text" name="LM_AUTO_TECDOC_PART_SEARCH_PAGE" id="PART_SEARCH_URL" size="40" maxlength="255" value="<?= COption::GetOptionString('linemedia.autotecdoc', 'LM_AUTO_TECDOC_PART_SEARCH_PAGE') ?>" />
        <input type="button" onclick="__ShUrlVars(this, 'PART_SEARCH_URL')" value='...' />
    </td>
</tr>

