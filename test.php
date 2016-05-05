<?
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
    $APPLICATION->SetTitle("тест1");
?>

<?
   /*
    $arSelect = Array('ID','IBLOCK_ID', "DETAIL_PICTURE");
    $arFilter = Array("IBLOCK_ID" => 3, 'SECTION_ID' => 129, "DETAIL_PICTURE" => false);
    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    
    while($aritem = $res->Fetch()){
            arshow($aritem["ID"]);
            $newElement = new CIBlockElement;
            $arLoadProductArray = Array(
                "IBLOCK_ID"      => 3,
                "IBLOCK_SECTION_ID" => 129, 
                "DETAIL_PICTURE" => CFile::MakeFileArray("upload/image_null.jpg"),
            );   
           $newElement->Update($aritem["ID"], $arLoadProductArray);     
    }  */
?>
<?
/*
 // парсинг csv файла
$resultArray = array();
    $root_path ="import/forward_price1.csv";
    $f = fopen($root_path, "r");
    while($str = fgets($f, 1024)){
    $resultArray[] = explode(";", $str);
    }
    fclose($f); 
    
    $arSelect = Array('ID','IBLOCK_ID', "PROPERTY_ARTNUMBER");
    $arFilter = Array("IBLOCK_ID" => 3, 'SECTION_ID' => 129);
    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    while($aritem = $res->Fetch()){    
        $articul[$aritem['PROPERTY_ARTNUMBER_VALUE']]["ARTNUMBER"] = $aritem['PROPERTY_ARTNUMBER_VALUE'];
        $articul[$aritem['PROPERTY_ARTNUMBER_VALUE']]['ID'] = $aritem['ID'];  
    }
  
foreach ($resultArray as $key => $arElement) { 
       
    if($key == 1 ){
        //Forming code for element

        //Downloading images if they don't exist
        
        $url = $arElement["image"];
        $path = $_SERVER["DOCUMENT_ROOT"].'/upload/images/'.$arElement[1].'.jpg';
        $arFile = CFile::MakeFileArray($path);
        if(!$path){
            $arFile = CFile::MakeFileArray("upload/image_null.jpg");
        }
        
        //Add new element in iblock
        $newElement = new CIBlockElement;

        $PROP = array();

        //Forming prop's for tire or disk
        $PROP["kod_1c"]=$arElement[0];
        $PROP["ARTNUMBER"]=$arElement[1];
        $PROP["OEM_TIPO_SIZE_PCD"]=$arElement[2];
        $PROP["GROUP"]=$arElement[3];
        $PROP["YEAR_R_DIAMETR"]=$arElement[4];
        $PROP["MANUFACTURER"]=$arElement[15];
        $PROP["NUM_PROIZVODITEL"]=$arElement[16];

        $arLoadProductArray = Array(
            "ACTIVE" => "Y",
            "NAME" => $arElement[5],
            "MODIFIED_BY"    => $USER->GetID(), 
            "IBLOCK_ID"      => 3,
            "IBLOCK_SECTION_ID" => 129,       
            "PROPERTY_VALUES"=> $PROP,
            "DETAIL_PICTURE" => $arFile,
        );
         
         if($articul[$arElement[1]]["ID"]){    
           $newElement->Update($articul[$arElement[1]]["ID"], $arLoadProductArray);
           $productId = $articul[$arElement[1]]["ID"];   
        }else{
           //Add new product in catalog
           $productId = $newElement->Add($arLoadProductArray); 
            
            $arCatFields = array(
                "ID" => $productId, 
                "VAT_ID" => 1,
                "QUANTITY" => $arElement[7]+$arElement[8]+$arElement[9]+$arElement[10],
                "VAT_INCLUDED" => "Y" 
            );
            CCatalogProduct::Add($arCatFields);  
         }
         
         
        //Add price
        $priceId = 1;
        $priceFields = Array(
            "PRODUCT_ID" => $productId,
            "CATALOG_GROUP_ID" => $priceId,
            "PRICE" => $arElement[6], 
            "CURRENCY" => "RUB",
        );
        $res = CPrice::GetList(
                array(),
                array(
                        "PRODUCT_ID" => $productId,
                        "CATALOG_GROUP_ID" => $priceId
                    )
            );

        if ($arr = $res->Fetch())
        {
            CPrice::Update($arr["ID"], $priceFields);
        }
        else
        {
            CPrice::Add($priceFields);
        }
    }                      
}          */
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>