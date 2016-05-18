<?
IncludeModuleLangFile(__FILE__);
$LM_AUTO_MAIN_API_INFORM_TECDOC = COption::GetOptionString( 'linemedia.auto', 'LM_AUTO_MAIN_API_INFORM_TECDOC', 'Y' ) == 'Y';
// TODO: доработать API linemedia с целью получения данных о редакции Автоэксперта
	$example_response_array = array(
		1	=> array(
			'name' => 'Быстрый старт'
		),
		2 => array(
			'name' => 'Профи'
		),
		3 => array(
			'name' => 'Эксперт'
		)
	);
	
	$example_result_response = 1;
	
	$result = $example_response_array[$example_result_response]['name'];
?><tr>
    <td colspan="2">
    	<?= BeginNote();?>
	    <?=GetMessage('LM_AUTO_MAIN_API_TYPE_AUTOEXPERT')?><?= $result ?>
	    <?= EndNote(); ?>
    </td>
</tr>


<tr>
    <td colspan="2">
    	<?= BeginNote();?>
	    <?=GetMessage('LM_AUTO_MAIN_API_NOTE')?>
	    <?= EndNote(); ?>
    </td>
</tr>

<tr>
    <td width="50%" valign="top"><label for="LM_AUTO_MAIN_API_ID"><?=GetMessage( 'LM_AUTO_MAIN_API_ID' );?>:</td>
    <td valign="top">
        <input size="5" type="text" name="LM_AUTO_MAIN_API_ID" id="LM_AUTO_MAIN_API_ID" value="<?=COption::GetOptionString( 'linemedia.auto', 'LM_AUTO_MAIN_API_ID', '' )?>">
    </td>
</tr>

<? /* API CONNECTION SETTINGS */ ?>
<tr class="heading">
    <td colspan="2"><?=GetMessage( 'LM_AUTO_MAIN_API_GROUP_TITLE' )?></td>
</tr>
<tr>
    <td width="50%" valign="top"><label for="LM_AUTO_MAIN_API_URL"><?=GetMessage( 'LM_AUTO_MAIN_API_URL' );?>:</td>
    <td valign="top">
        <input type="text" name="LM_AUTO_MAIN_API_URL" id="LM_AUTO_MAIN_API_URL" value="<?=COption::GetOptionString( 'linemedia.auto', 'LM_AUTO_MAIN_API_URL', 'api.auto-expert.info' )?>">
    </td>
</tr>
<tr>
    <td width="50%" valign="top"><label for="LM_AUTO_MAIN_API_ID"><?=GetMessage( 'LM_AUTO_MAIN_API_FORMAT' );?>:</td>
    <td valign="top">
        <select name="LM_AUTO_MAIN_API_FORMAT" id="LM_AUTO_MAIN_API_FORMAT">
            <?
                $options = array(
                    'json' => 'JSON',
                    'xml' => 'XML',
                    'serialized' => 'Serialization',
                );
                $selected = COption::GetOptionString( 'linemedia.auto', 'LM_AUTO_MAIN_API_FORMAT', '' );
                
                foreach($options AS $id => $title) {
                    ?><option<?=($selected==$id)?' selected':''?> value="<?=$id?>"><?=$title?></option><?
                }
            ?>
        </select>
    </td>
</tr>
<tr>
    <td width="50%" valign="top"><label for="LM_AUTO_MAIN_API_INFORM_TECDOC"><?=GetMessage( 'LM_AUTO_MAIN_API_INFORM_TECDOC' );?>:</td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_API_INFORM_TECDOC" id="LM_AUTO_MAIN_API_INFORM_TECDOC" value="Y" <?=($LM_AUTO_MAIN_API_INFORM_TECDOC) ? 'checked="checked"':''?>>
    </td>
</tr>
