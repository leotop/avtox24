<?
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
    $APPLICATION->SetTitle("тест1");
?>
<?
$arSelect = Array('ID','IBLOCK_ID','IBLOCK_SECTION_ID','DETAIL_PICTURE', "PROPERTY_CML2_ARTICLE");
    $arFilter = Array("IBLOCK_ID" => 3);
    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    while($aritem = $res->Fetch()){
        if($aritem['PROPERTY_CML2_ARTICLE_VALUE'] and !$aritem['DETAIL_PICTURE']){
            $itemArticleProduct[] = $aritem;
           // arshow($aritem);
        }
    }
    $newElement = new CIBlockElement;
    arshow(count($itemArticleProduct));    //8474
    foreach($itemArticleProduct as $key => $articleProduct){
        if($key > 500 && $key < 1000){
          //  arshow($articleProduct);
            $arLoadProductArray = Array(
                "DETAIL_PICTURE" => CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/upload/accessories/".$articleProduct['PROPERTY_CML2_ARTICLE_VALUE'].".JPEG"),
            );
           arshow($arLoadProductArray);
           arshow($articleProduct);
           $newElement->Update($articleProduct["ID"], $arLoadProductArray);
           $productId = $articleProduct["ID"];
        }
    }
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>