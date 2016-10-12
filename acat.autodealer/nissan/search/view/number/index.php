<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?><link href="../../media/css/fw.css" media="all" rel="stylesheet" type="text/css">
<link href="../../media/css/style.css" media="all" rel="stylesheet" type="text/css">
<link href="../../media/css/nissan.css" media="all" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../../media/js/jquery-1.11.1.min.js"></script>

<div id="searchNisNumber"><?php
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
    foreach( $aTree AS $mark=>$modifs ){ ?>
        <div class="treeBranch">
            <div id="plusBranch<?=$oNIS->replaceSS($mark)?>" class="plusBranch anime" onclick="branchToggle('plusBranch<?=$oNIS->replaceSS($mark)?>','itemsBranch<?=$oNIS->replaceSS($mark)?>')">+</div>
            <div class="itemsBranchL">
                <div class="headBranchLVL1 anime" onclick="branchToggle('plusBranch<?=$oNIS->replaceSS($mark)?>','itemsBranch<?=$oNIS->replaceSS($mark)?>')"><?=$mark.' '.$modifs->name;$forUrl =$modifs->name; unset($modifs->name);?></div>
                <div id="itemsBranch<?=$oNIS->replaceSS($mark)?>" class="" style="display:none">
                    <?php foreach( $modifs AS $model=>$markets ){ $a = '0'; $params = ''; ?>
                        <div class="headBranchLVL2 anime pl40" onclick="$('#market<?=$oNIS->replaceSS($mark).$model?>').slideToggle(600)">
                            <?=$markets->$a->fullgrname.' ('.$markets->date.' , '.$markets->$a->shortsAll.')';unset($markets->date);//var_dump($markets->$a)?>
                        </div>
                        <div id="market<?=$oNIS->replaceSS($mark).$model?>" class="text-left ml60 pb20" style="display:none">
                            <?php  foreach( $markets AS $market=>$detail){ $b = ''.(($market*1)-1).'';
                                if($market > 0 && $markets->$market->secno == $markets->$b->secno && $markets->$market->partcode==$markets->$b->partcode) continue;
                                $url = DS.'nissan/illustration.php?market='.$about->market.'&model='.$forUrl.'&modif='.$model.'&group='.$detail->group.'&figure='.$detail->figure.'&part='.urlencode($detail->partcode);?>
                                <div id="detail<?=$mark.$model.$market?>" class="text-left ml60">
                                    <a href="<?=$url?>" target="_blank">
                                        <span class="itemDesc" class="anime"><?=$detail->partcode.' '.$detail->partname.' ( '.$detail->fullsubgrname.' )'?></span>
                                    </a><br>
                                </div>
                            <?php } ?>
                        </div>
                    <?php }?>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
    <?php }?>
        <script>
            function branchToggle(plusBranch,itemsBranch){
                console.log(plusBranch,itemsBranch);
                var $plusBranch  = $('#'+plusBranch),
                    $itemsBranch = $('#'+itemsBranch);
                if( $plusBranch.html()!='+' ){
                    $plusBranch.html('+');
                    $itemsBranch.slideUp(700);
                }
                else{
                    $plusBranch.html('&ndash;');
                    $itemsBranch.slideDown(700);
                }
            }
        </script>
    <? } ?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>