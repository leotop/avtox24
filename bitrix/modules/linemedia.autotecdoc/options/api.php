<?
/**
 * Linemedia Autoportal
 * Autotecdoc module
 * api
 *
 * @author  Linemedia
 * @since   22/01/2012
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);?>
<tr>
    <td colspan="2">
    	<?= BeginNote();?>
	    <?=GetMessage('LM_AUTO_TECDOC_API_NOTE')?>
	    <?= EndNote(); ?>
    </td>
</tr>

<tr>
    <td width="50%" valign="top"><label for="LM_AUTO_TECDOC_API_ID"><?=GetMessage( 'LM_AUTO_TECDOC_API_ID' );?>:</td>
    <td valign="top">
        <input size="5" type="text" name="LM_AUTO_TECDOC_API_ID" id="LM_AUTO_TECDOC_API_ID" value="<?=COption::GetOptionString( 'linemedia.autotecdoc', 'LM_AUTO_TECDOC_API_ID', '' )?>">
    </td>
</tr>

<? /* API CONNECTION SETTINGS */ ?>
<tr class="heading">
    <td colspan="2"><?=GetMessage( 'LM_AUTO_TECDOC_API_GROUP_TITLE' )?></td>
</tr>
<tr>
    <td width="50%" valign="top"><label for="LM_AUTO_TECDOC_API_URL"><?=GetMessage( 'LM_AUTO_TECDOC_API_URL' );?>:</td>
    <td valign="top">
        <input type="text" name="LM_AUTO_TECDOC_API_URL" id="LM_AUTO_TECDOC_API_URL" value="<?=COption::GetOptionString( 'linemedia.autotecdoc', 'LM_AUTO_TECDOC_API_URL', 'api.auto-expert.info' )?>">
    </td>
</tr>
<tr>
    <td width="50%" valign="top"><label for="LM_AUTO_TECDOC_API_ID"><?=GetMessage( 'LM_AUTO_TECDOC_API_FORMAT' );?>:</td>
    <td valign="top">
        <select name="LM_AUTO_TECDOC_API_FORMAT" id="LM_AUTO_TECDOC_API_FORMAT">
            <?
                $options = array(
                    'json' => 'JSON',
                    'xml' => 'XML',
                    'serialized' => 'Serialization',
                );
                $selected = COption::GetOptionString( 'linemedia.autotecdoc', 'LM_AUTO_TECDOC_API_FORMAT', '' );
                
                foreach($options AS $id => $title) {
                    ?><option<?=($selected==$id)?' selected':''?> value="<?=$id?>"><?=$title?></option><?
                }
            ?>
        </select>
    </td>
</tr>
