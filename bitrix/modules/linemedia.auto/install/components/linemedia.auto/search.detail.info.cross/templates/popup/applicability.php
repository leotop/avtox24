<?__IncludeLang(dirname(__FILE__) . '/lang/' . LANGUAGE_ID . '/' . basename(__FILE__));?>

<? foreach ($arResult['APPLICABILITY'] as $model) { ?>
    <a href="javascript:void(0)" class="applicability-model" rel="<?= md5($model['MODEL_NAME']) ?>">
        <?= $model['MODEL_NAME'] ?>
    </a>
<? } ?>
<div class="clear"></div>

<? foreach ($arResult['APPLICABILITY'] as $model) { ?>
    <div id="applicability-modification-<?= md5($model['MODEL_NAME']) ?>" class="applicability-modifications" style="display: none;">
        <table class="applicability-modifications-table">
            <thead>
                <tr>
                    <th><?= GetMessage('HEAD_TYPE') ?></th>
                    <th><?= GetMessage('HEAD_YEAR') ?></th>
                    <th><?= GetMessage('HEAD_HORSEPOWER') ?></th>
                    <th><?= GetMessage('HEAD_BODY') ?></th>
                    <th><?= GetMessage('HEAD_FUEL') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($model['MODIFICATIONS'] as $modification) {
                    $year_start = substr($modification['START'], 4, 2) . '.' . substr($modification['START'], 0, 4);
                    $year_end = substr($modification['END'], 4, 2) . '.' . substr($modification['END'], 0, 4);
                    ?>
                    <tr>
                        <td align="center">
                            <?= $modification['NAME'] ?>
                        </td>
                        <td align="right">
                            <?=$year_start?>-<?=$year_end?>
                        </td>
                        <td align="right">
                            <?= $modification['HP_FROM'] ?>
                        </td>
                        <td align="right">
                            <?= $modification['BODY_TYPE'] ?>
                        </td>
                        <td align="right">
                            <?= $modification['FUEL_TYPE'] ?>
                        </td>
                    </tr> 
                <? } ?>
            </tbody>
        </table>
    </div>
<? } ?>
