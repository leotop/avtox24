<?php
//header('Content-type: application/json');
//echo json_encode(array('errors' => $arResult['ERRORS']));
//exit();
echo 'error'; ?>
<tr onclick="document.location='<?=$arParams['QUERY_URL']?>'">
    <td>
        <?=$arParams['QUERY_TITLE']?>
    </td>
    <td>
        <a href="<?=$arParams['QUERY_URL']?>"><?=$arParams['QUERY']?></a>
    </td>
    <td>
        <?=$arParams['QUERY_COMMENT']?>
    </td>
    <td>
        <?=$arParams['QUERY_QUANTITY']?>
    </td>
</tr>