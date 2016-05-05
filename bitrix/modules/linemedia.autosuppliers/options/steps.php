<?
IncludeModuleLangFile( __FILE__ );

/*
 * Подключим на страницу jquery
 */
CJSCore::Init(array('jquery'));

$statuses = array();
$res = CSaleStatus::GetList();
while ($status = $res->Fetch()) {
    $statuses[$status['ID']] = $status;
}

/*
 * Сохраненные шаги
 */
$keys = (array) unserialize(COption::GetOptionString('linemedia.autosuppliers', LinemediaAutoSuppliersStep::STEPS_KEY));

$steps = array();
foreach ($keys as $key) {
    $steps[$key] = (array) unserialize(COption::GetOptionString('linemedia.autosuppliers', 'LM_AUTO_SUPPLIERS_STEP_'.$key));
}

?>

<style type="text/css">
    .lm-auto-suppliers-step-table label {
        display: none !important;
    }
    
    .lm-auto-suppliers-step-table .lm-auto-td-label label {
        display: block !important;
    }
    
    #bx-admin-prefix .adm-designed-checkbox.lm-auto-adm-checkbox {
        display: block !important;
    }
</style>

<script type="text/javascript">
    var index = <?= count($steps) ?>;
    
    $(document).ready(function() {
        $('#lm-auto-suppliers-add-step').click(function() {
            var addtr = $(this).closest('tr');
            var step = $('#lm-auto-suppliers-step-clone').clone();
            
            index++;
            
            step.attr('id', null);
            step.find('select[name="LM_AUTO_SUPPLIERS_STEP_FILTER_STATUSES"]').attr('name', 'LM_AUTO_SUPPLIERS_STEP_FILTER_STATUSES[' + index + '][]');
            
            step.find("input[id^='LM_AUTO_SUPPLIERS_STEP_CAN_REQUEST']").attr('id', 'LM_AUTO_SUPPLIERS_STEP_CAN_REQUEST_' + index);
            step.find("input[id^='LM_AUTO_SUPPLIERS_STEP_CAN_REQUEST']").attr('name', 'LM_AUTO_SUPPLIERS_STEP_CAN_REQUEST[' + index + ']');
            step.find("input[id^='LM_AUTO_SUPPLIERS_STEP_CAN_REQUEST']").siblings('input[type="hidden"]').attr('name', 'LM_AUTO_SUPPLIERS_STEP_CAN_REQUEST[' + index + ']');
            
            step.find('input[id^="LM_AUTO_SUPPLIERS_STEP_CAN_MAIL"]').attr('id', 'LM_AUTO_SUPPLIERS_STEP_CAN_MAIL_' + index);
            step.find('input[id^="LM_AUTO_SUPPLIERS_STEP_CAN_MAIL"]').attr('name', 'LM_AUTO_SUPPLIERS_STEP_CAN_MAIL[' + index + ']');
            step.find('input[id^="LM_AUTO_SUPPLIERS_STEP_CAN_MAIL"]').siblings('input[type="hidden"]').attr('name', 'LM_AUTO_SUPPLIERS_STEP_CAN_MAIL[' + index + ']');
            
            addtr.before(step);
        });
        
        $('.lm-auto-suppliers-remove-step').live('click', function() {
            $(this).closest('.lm-auto-suppliers-step').remove();
            index--;
        });
    });
</script>

<tr class="heading">
    <td valign="top" colspan="2" class="lm-auto-td-label">
        <label for="LM_AUTO_SUPPLIERS_STEPS_SETTINGS">
            <?= GetMessage('LM_AUTO_SUPPLIERS_STEPS_SETTINGS') ?>:
        </label>
    </td>
</tr>
<tr>
    <td valign="top" colspan="2">
        <?= BeginNote() ?>
        <?= GetMessage('LM_AUTO_SUPPLIERS_ADD_DESC') ?>
        <?= EndNote() ?>
    </td>
</tr>
<tr id="lm-auto-suppliers-step-clone" class="lm-auto-suppliers-step">
    <td valign="top" colspan="2">
        <table class="lm-auto-suppliers-step-table">
            <tr>
                <td align="right" valign="top" width="50%" class="lm-auto-td-label">
                    <label for="LM_AUTO_SUPPLIERS_STEP_TITLE">
                        <b><?= GetMessage('LM_AUTO_SUPPLIERS_STEP_TITLE') ?></b>:
                    </label>
                </td>
                <td>
                    <input type="text" name="LM_AUTO_SUPPLIERS_STEP_TITLE[]" value="" />
                </td>
            </tr>
            <tr>
                <td align="right" valign="top" width="50%" class="lm-auto-td-label">
                    <label for="LM_AUTO_SUPPLIERS_STEP_FILTER_STATUSES">
                        <?= GetMessage('LM_AUTO_SUPPLIERS_STEP_FILTER_STATUSES') ?>:
                    </label>
                </td>
                <td>
                    <select name="LM_AUTO_SUPPLIERS_STEP_FILTER_STATUSES" size="6" multiple="multiple">
                        <? foreach ($statuses as $id => $status) { ?>
                            <option value="<?= $id ?>"><?= $status['NAME'] ?> <? if (!empty($status['DESCRIPTION'])) { ?> (<?= $status['DESCRIPTION'] ?>)<? } ?></option>
                        <? } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right" valign="top" width="50%" class="lm-auto-td-label">
                    <label for="LM_AUTO_SUPPLIERS_STEP_DEFAULT_STATUS">
                        <?= GetMessage('LM_AUTO_SUPPLIERS_STEP_DEFAULT_STATUS') ?>:
                    </label>
                </td>
                <td>
                    <select name="LM_AUTO_SUPPLIERS_STEP_DEFAULT_STATUS[]">
                        <? foreach ($statuses as $id => $status) { ?>
                            <option value="<?= $id ?>"><?= $status['NAME'] ?> <? if (!empty($status['DESCRIPTION'])) { ?> (<?= $status['DESCRIPTION'] ?>)<? } ?></option>
                        <? } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right" valign="top" width="50%" class="lm-auto-td-label">
                    <label for="LM_AUTO_SUPPLIERS_STEP_CAN_REQUEST">
                        <?= GetMessage('LM_AUTO_SUPPLIERS_STEP_CAN_REQUEST') ?>:
                    </label>
                </td>
                <td>
                    <input type="hidden" name="LM_AUTO_SUPPLIERS_STEP_CAN_REQUEST[0]" value="N" />
                    <input type="checkbox" class="adm-designed-checkbox lm-auto-adm-checkbox" name="LM_AUTO_SUPPLIERS_STEP_CAN_REQUEST[0]" value="Y" id="LM_AUTO_SUPPLIERS_STEP_CAN_REQUEST" />
                </td>
            </tr>
            <tr>
                <td align="right" valign="top" width="50%" class="lm-auto-td-label">
                    <label for="LM_AUTO_SUPPLIERS_STEP_CAN_MAIL">
                        <?= GetMessage('LM_AUTO_SUPPLIERS_STEP_CAN_MAIL') ?>:
                    </label>
                </td>
                <td>
                    <input type="hidden" name="LM_AUTO_SUPPLIERS_STEP_CAN_MAIL[0]" value="N" />
                    <input type="checkbox" class="adm-designed-checkbox lm-auto-adm-checkbox" name="LM_AUTO_SUPPLIERS_STEP_CAN_MAIL[0]" value="Y" id="LM_AUTO_SUPPLIERS_STEP_CAN_MAIL" />
                </td>
            </tr>
            <tr>
                <td align="right" valign="top" width="50%" class="lm-auto-td-label">
                    <label for="LM_AUTO_SUPPLIERS_STEP_CAN_UPLOAD">
                        <?= GetMessage('LM_AUTO_SUPPLIERS_STEP_CAN_UPLOAD') ?>:
                    </label>
                </td>
                <td>
                    <input type="hidden" name="LM_AUTO_SUPPLIERS_STEP_CAN_UPLOAD[0]" value="N" />
                    <input type="checkbox" class="adm-designed-checkbox lm-auto-adm-checkbox" name="LM_AUTO_SUPPLIERS_STEP_CAN_UPLOAD[0]" value="Y" id="LM_AUTO_SUPPLIERS_STEP_CAN_UPLOAD" />
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <a href="javascript:void(0);" class="lm-auto-suppliers-remove-step">
                        <?= GetMessage('LM_AUTO_SUPPLIERS_REMOVE_STEP') ?>
                    </a>
                </td>
            </tr>
            <tr><td colspan="2"><hr style="opacity: 0.3;" /></td></tr>
        </table>
    </td>
</tr>


<? foreach ($steps as $i => $step) { ?>
    <tr class="lm-auto-suppliers-step">
        <td valign="top" colspan="2">
            <table class="lm-auto-suppliers-step-table">
                <tr>
                    <td align="right" valign="top" width="50%" class="lm-auto-td-label">
                        <label for="LM_AUTO_SUPPLIERS_STEP_TITLE">
                            <b><?= GetMessage('LM_AUTO_SUPPLIERS_STEP_TITLE') ?></b>:
                        </label>
                    </td>
                    <td>
                        <input type="text" name="LM_AUTO_SUPPLIERS_STEP_TITLE[<?= $i ?>]" value="<?= $step['title'] ?>" />
                    </td>
                </tr>
                <tr>
                    <td align="right" valign="top" width="50%" class="lm-auto-td-label">
                        <label for="LM_AUTO_SUPPLIERS_STEP_FILTER_STATUSES">
                            <?= GetMessage('LM_AUTO_SUPPLIERS_STEP_FILTER_STATUSES') ?>:
                        </label>
                    </td>
                    <td>
                        <select name="LM_AUTO_SUPPLIERS_STEP_FILTER_STATUSES[<?= $i ?>][]" size="6" multiple="multiple">
                            <? foreach ($statuses as $id => $status) { ?>
                                <option value="<?= $id ?>" <?= (in_array($id, $step['filter-statuses'])) ? ('selected') : ('') ?>>
                                    <?= $status['NAME'] ?> <? if (!empty($status['DESCRIPTION'])) { ?> (<?= $status['DESCRIPTION'] ?>)<? } ?>
                                </option>
                            <? } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align="right" valign="top" width="50%" class="lm-auto-td-label">
                        <label for="LM_AUTO_SUPPLIERS_STEP_DEFAULT_STATUS">
                            <?= GetMessage('LM_AUTO_SUPPLIERS_STEP_DEFAULT_STATUS') ?>:
                        </label>
                    </td>
                    <td>
                        <select name="LM_AUTO_SUPPLIERS_STEP_DEFAULT_STATUS[<?= $i ?>]">
                            <? foreach ($statuses as $id => $status) { ?>
                                <option value="<?= $id ?>" <?= ($id == $step['default-status']) ? ('selected') : ('') ?>>
                                    <?= $status['NAME'] ?> <? if (!empty($status['DESCRIPTION'])) { ?> (<?= $status['DESCRIPTION'] ?>)<? } ?>
                                </option>
                            <? } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align="right" valign="top" width="50%" class="lm-auto-td-label">
                        <label for="LM_AUTO_SUPPLIERS_STEP_CAN_REQUEST">
                            <?= GetMessage('LM_AUTO_SUPPLIERS_STEP_CAN_REQUEST') ?>:
                        </label>
                    </td>
                    <td>
                        <input type="hidden" name="LM_AUTO_SUPPLIERS_STEP_CAN_REQUEST[<?= $i ?>]" value="N" />
                        <input type="checkbox" class="lm-auto-adm-checkbox" name="LM_AUTO_SUPPLIERS_STEP_CAN_REQUEST[<?= $i ?>]" value="Y" <?= ($step['request'] == 'Y') ? ('checked') : ('') ?> />
                    </td>
                </tr>
                <tr>
                    <td align="right" valign="top" width="50%" class="lm-auto-td-label">
                        <label for="LM_AUTO_SUPPLIERS_STEP_CAN_MAIL">
                            <?= GetMessage('LM_AUTO_SUPPLIERS_STEP_CAN_MAIL') ?>:
                        </label>
                    </td>
                    <td>
                        <input type="hidden" name="LM_AUTO_SUPPLIERS_STEP_CAN_MAIL[<?= $i ?>]" value="N" />
                        <input type="checkbox" class="lm-auto-adm-checkbox" name="LM_AUTO_SUPPLIERS_STEP_CAN_MAIL[<?= $i ?>]" value="Y" <?= ($step['mail'] == 'Y') ? ('checked') : ('') ?> />
                    </td>
                </tr>
                <? /* tr>
                    <td align="right" valign="top" width="50%" class="lm-auto-td-label">
                        <label for="LM_AUTO_SUPPLIERS_STEP_CAN_UPLOAD">
                            <?= GetMessage('LM_AUTO_SUPPLIERS_STEP_CAN_UPLOAD') ?>:
                        </label>
                    </td>
                    <td>
                        <input type="hidden" name="LM_AUTO_SUPPLIERS_STEP_CAN_UPLOAD[<?= $i ?>]" value="N" />
                        <input type="checkbox" class="lm-auto-adm-checkbox" name="LM_AUTO_SUPPLIERS_STEP_CAN_UPLOAD[<?= $i ?>]" value="Y" <?= ($step['upload'] == 'Y') ? ('checked') : ('') ?> />
                    </td>
                </tr */ ?>
                <tr>
                    <td></td>
                    <td>
                        <a href="javascript:void(0);" class="lm-auto-suppliers-remove-step">
                            <?= GetMessage('LM_AUTO_SUPPLIERS_REMOVE_STEP') ?>
                        </a>
                    </td>
                </tr>
                <tr><td colspan="2"><hr style="opacity: 0.3;" /></td></tr>
            </table>
        </td>
    </tr>
<? } ?>


<tr class="heading">
    <td valign="top" colspan="2">
        <input type="button" id="lm-auto-suppliers-add-step" value="<?= GetMessage('LM_AUTO_SUPPLIERS_ADD_STEP') ?>" />
    </td>
</tr>
