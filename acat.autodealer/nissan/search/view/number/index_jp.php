<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<link href="../../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<link href="../../media/css/nissan.css" media="all" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../../media/js/jquery-1.11.1.min.js"></script>

<div id="searchNisNumber jp"><?php
    include WWW_ROOT."helpers/breads.php"; /// Подключаем "хлебные крошки"
    include_once WWW_ROOT."helpers/search.php"; /// Подключаем форму поиска
    if(!empty($errors) || !empty($msg)){
        if(!is_array($msg)) echo "<h2>".$msg."</h2>";
        else foreach($msg as $k=>$message){
            echo "<h2>".$message."</h2>";
        }
        foreach ($errors AS $sError) { ?>
        <span class="red"><?= $sError ?></span></br>
    <?php }
    }else{
        echo "<div id='searchNisNumber' class='wd80 pl50'>";
            foreach( $aTree AS $modifs ){ ?>
            <? $request = $url.$number.'&market='.A2D::get($_GET,'market').'&model='.$oNIS->replaceSS($modifs->model); ?>
                <div id="plusBranch<?=$modifs->modelname?>" class="plusBranch anime" onclick="clickOnMainDiv('<?=$request?>',this,'itemsBranch<?=$oNIS->replaceSS($modifs->model)?>','<?='headBranch'.$oNIS->replaceSS($modifs->model)?>',1)">+</div>
                <div class="itemsBranchL">
                    <div class="headBranchLVL1 anime" onclick="clickOnMainDiv('<?=$request?>',this,'itemsBranch<?=$oNIS->replaceSS($modifs->model)?>','<?='headBranch'.$oNIS->replaceSS($modifs->model)?>',1)" id="headBranch<?=$oNIS->replaceSS($modifs->model)?>">
                        <?=$modifs->modelname.' '.$oNIS->replaceSS($modifs->model)?>
                    </div>
                    <div id="itemsBranch<?=$oNIS->replaceSS($modifs->model)?>" class="" style="display:none"></div>
                </div>
            <? } ?>
            <script>
                /// Если был "ответ" - значит Аякс не нужен, просто сворачиваем, если небыло - грузим
                function clickOnMainDiv(url,div,detail,name,level){ //console.log($('#'+name).hasClass('response'));
                    if( !$('#'+name).hasClass('response') ){
                        bttnClick(url,$('#'+detail),detail,name,level);
                    }else{
                        $('#'+name).removeClass('response');
                        $('#'+detail).slideUp(700);
                    }
                }

                /// Многоуровневая Аякс "магия" по отрисовыванию вложенных разделов/запчастей
                function bttnClick(url,bttn,detail,name,level){
                    $.ajax({
                        type: "GET",
                        url: url,
                        dataType: "json"
                    }).done(function( r ){
                        var prevDiv = $('#'+name),msg = '';
                        $(bttn).show();
                        if( !r ){
                            bttn.remove();
                            msg = ""+
                                "<div class='noResponse'>"+
                                "   <span class='red'>Результатов не найдено</span>"+
                                "</div>"+
                                "";
                            response.html( msg );
                            prevDiv.addClass('response');
                        }else{

                            $.each( r, function( k, v ){
                                if(level == 1) {
                                    var nextUrl = '"' + url + '&modif=' + v.modif + '"';
                                    var divID = '"headBranchLVL_' + v.model1+'_'+ v.modif+'_'+ v.figure+'_'+ v.subfigure.replace("*","")+k + '"';
                                    var childID = '"market_' + v.model1+'_'+ v.modif+'_'+ v.figure+'_'+ v.subfigure.replace("*","")+k + '"';
                                    msg +=
                                        "<div class='headBranchLVL2 anime pl40' onclick='clickOnMainDiv(" + nextUrl + ", this, " + childID + "," + divID + ",2)" +
                                        "' id=" + divID + " >" +
                                        v.groupname + " ( <u>" + v.date + "</u>, " + v.shortsAll.replace(v.groupname+',',"") + " )" + //k
                                        "</div>" +
                                        "<div id="+ childID +" class='text-left ml60 pb20' style='display:none'></div>"
                                    ;
                                }
                                if (level == 2){ var sp = '/'; //msg = '';
                                    var divID = '"headBranchLVL_' + v[0].model1+'_'+ v[0].modif+'_'+v[0].groupsim+'_'+ v.figure+'_'+ v.subfigure+'_'+ v.secno +'"';
                                    var childID = '"market_'+ v[0].model1+'_'+ v[0].modif+'_'+v[0].groupsim+'_'+ v.figure+'_'+ v.subfigure+'_'+ v.secno +'"';
                                    msg +=
                                        "<div class='headBranchLVL3 anime pl20' onclick='slideLastLevel(this,"+childID+")' id=" + divID + " >"+ k + "</div>" +
                                        "<div id="+ childID +" class='text-left ml60 pb20' style='display:none'>"
                                    ;
                                    $.each( v, function( k2, v2 ) {
                                        var lastUrl = '/nissan/illustration.php?market=jp&model=' + v2.model1 + '&modif=' + v2.modif + '&group=' + v2.groupsim + '&figure=' + v2.figure /*+ sp + v2.subfig + sp + v2.secno */ + '&part=' + v2.partcode;
                                        msg +=
                                            "<div><a href='" + lastUrl + "' class='text-left' target='_blank'><span class='itemDesc'>" +
                                            v2.partcode + " " + v2.partname + " ( " + v2.fullsubgrname + " )" +
                                            "</span></a></div>"
                                        ;
                                    });
                                    msg += "</div>";
                                }
                            });
                            $(bttn).html(msg).hide();$(bttn).slideDown(700);
                            prevDiv.addClass('response');
                        }
                    } ).error(function(e){
                        /// Посмотреть ошибки console.log(e)
                    } ).fail(function(e){
                        /// Посмотреть ошибки console.log(e)
                    });

                }

                /// Функция для развертываия последнего уровня, он уже прогружен раньше через AJAX
                function slideLastLevel(current,child){
                    if( !$(current).hasClass('response') ){
                        $(current).addClass('response');
                        $('#'+child).slideDown(700);
                    }else{
                        $('#'+child).slideUp(700);
                        $(current).removeClass('response');
                    }
                }

            </script>
        <? } ?>
    <?="</div>" ?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>