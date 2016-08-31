<?php

echo '<h3>' . CommonExtender::LocalizeString('searchbymodel').'</h3>';

include_once('guayaquillib/render/catalog/operationform.php');

if (!class_exists('OperationSearchExtender')) {
    class OperationSearchExtender extends CommonExtender
    {
        function FormatLink($type, $dataItem, $catalog, $renderer)
        {
            return 'vehicles.php?ft=execCustomOperation&c=' . $catalog;
        }
    }
}

$renderer = new GuayaquilOperationSearchForm(new OperationSearchExtender());
echo $renderer->Draw(array_key_exists('c', $_GET) ? $_GET['c'] : '', $operation, @$_GET['data']);
?>