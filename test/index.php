
<?php
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

    $rsUser = CUser::GetByID($USER->GetParam('USER_ID')); 
    $arUser = $rsUser->Fetch();
    arshow($arUser);
?>
       