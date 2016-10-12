<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<link href="../media/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<link href="../media/css/nissan.css" media="all" rel="stylesheet" type="text/css">

<div id="NISVin" class="AutoDealer">

    <?php include WWW_ROOT."helpers/breads.php"; /// Подключаем "хлебные крошки"

    if(!empty($aErrors)  || !empty($msg) ){
        if(!is_array($msg)) echo "<h2>".$msg."</h2>";
        else foreach($msg as $k=>$message){
            echo "<h2>".$message."</h2>";
        }
        foreach( $errors AS $sError ){?>
            <span class="red">Ошибка:<?=$sError?></span></br>
        <?php }
    }else{ ?>
        <table border="1" class="dataTable">
            <thead>
            <tr>
                <th>Рынок</th>
                <th>Модель</th>
                <th>Серия</th>
                <th>Производство</th>
                <?foreach($aFields as $value){
                    echo '<th>'.$value.'</th>';
                }?>
                <th>Другое</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if(!empty($aRezult))
                foreach( $aRezult AS $_v ){
                    $nextUrl  = DS.$mark.DS.UTF8::strtolower($_v->market).DS.$_v->modelCode.DS.$_v->compl;
                    ?>
                    <tr onclick="window.location.href ='<?=$nextUrl?>'" class="alMid">
                        <td><a href="<?=$nextUrl?>"><?=str_replace(" ", "<br/>", $_v->marketRU)?></a></td>
                        <td><?=$_v->modelName?></td>
                        <td><?=$_v->modelCode?></td>
                        <td><?=$_v->prod?></td>
                        <?foreach($_v as $key =>$value){
                            //ключи отличаются в разных моделях, не везде двигатель или трансмиссия схожи для сравнения
                            if(!in_array($key,['market','marketRU', 'modelName', 'modelCode', 'compl', 'dir', 'prod', 'other'])){
                                echo '<td>'.$_v->$key.'</td>';
                            }
                        }
                        if(!empty($_v->other) && is_array($_v->other))
                            echo '<td>'.implode(' ',$_v->other).'</td>';
                        elseif(!empty($_v->other) && !is_array($_v->other)) echo '<td>'.$_v->other.'</td>';
                        else echo '<td></td>'; ?>
                    </tr>
                <?php }?>
            </tbody>
        </table>

        <?if(!empty($aList)){?>
            <div class="explanation">
                <span class="cBlue">Расшифровка сокращений</span>
                <div class="expTable">
                    <?php foreach( $aList AS $n=>$s ){
                        if($s){?>
                            <div class="eTableHead"><?=$n?></div>
                            <div class="eTableBody">
                                <?php foreach( $s AS $k=>$a ){?>
                                    <span class="sign"><?=$k?></span> = <span class="desc"><?=$a?></span><br/>
                                <?php }?>
                            </div>
                        <?php }
                    }?>
                </div>
            </div>
        <?}
    } ?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>