<?php
/**
 * по умолчанию скрыта вторая вкладка
 * fromSV[tab1] OR fromSF[tab2]
 *
 * fromSV - From Search VIN
 * fromSN - From Search Number
 *
 * $root: bmw, toyota, etka
 * $searchTabs:
 *      Количество массивов второго уровня означает сколько будет вкладок
 *      Количество элементов в таком массиве означает сколько будет полей для поиска
 *      Каждый элемент - имя поля
 *      [
 *          [vin]
 *          [number]
 *      ]
 */
$a = $tName = FALSE; /// Нет ни одной активной вкладки
foreach( A2D::$searchTabs AS $t ){
    if(!empty($t['alias'])) $tab = "from".ucfirst($t['alias']);
    else $tab = "from";
    if( A2D::get($_GET,$tab) ){
        $a = TRUE; /// Выстрелило
        ${"tab".$t['id']}    ='tab_active';
        ${"tabDiv".$t['id']} ='tabkont_active';
        $tName = $t['tName'];
    }
    else{
        ${"tab".$t['id']}    ='tab';
        ${"tabDiv".$t['id']} ='tabkont';
    }
}
if( !$a ){ /// Если нет активных вкладок, включаем первую
    $tab1    ='tab_active';
    $tabDiv1 ='tabkont_active';
}

?>

<section id="searchForm">
    <div class="searchForm" id="A2DSearch">

        <?php if( count(A2D::$searchTabs)>1 ) foreach( A2D::$searchTabs AS $t ){$width = 100/count(A2D::$searchTabs);?>
            <div class="<?=${"tab".$t['id']}?>" id="tab<?=$t['id']?>" onclick="tabActive(<?=$t['id']?>)" style="width: <?=$width?>%;">
            <span><?=$t['tName']?></span>
        </div>
        <?php }?>
        <div class="clear"></div>

        <?php foreach( A2D::$searchTabs AS $i=>$t ){ $multi=A2D::get($t,'multi'); $hidden=A2D::get($t,'hidden',[]); ?>
            <div class="<?=${"tabDiv".$t['id']}?>" id="tabkont<?=$t['id']?>" >
            <form id="searchForm<?=($i+1)?>" name="searchForm<?=($i+1)?>" method="<?=A2D::$searchMethod?>" action="<?=A2D::$searchIFace?>/search/<?=($multi)?$t['action']:$t['alias']?>.php">
                <input type="hidden" name="mark" value="<?=$_GET['mark']?>">
                <input type="hidden" name="from<?=ucfirst($t['alias'])?>" value="1">

                <?php foreach( $hidden as $hName=>$hValue ){ //Добавление скрытых полей ?>
                                <input type="hidden" name="<?=$hName?>" value="<?=$hValue?>">
                                <?php }?>

                <?php if( $multi ){
                        foreach( $multi AS $mt ){
                            if(empty($mt['list'])){?>
                                <input type="text" name="<?=$mt['alias']?>" class="search<?=count($multi)?>failds" placeholder="Укажите <?=$mt['name']?>" value="<?=A2D::get($_GET,$mt['alias']);?>">
                                <?php if( $mt!=end($multi) ){?>&ensp;&mdash;&ensp;<?php }
                            }else{ $lists=$mt['list'];?>
                            <select name="<?=$mt['alias']?>" class="search2failds low" >
                                <?php foreach( $lists AS $key=>$market ){
                                    if( (empty(A2D::get($_GET,$mt['alias'])) && (($root=='nissan' && $key=='el' ) || ($root=='infiniti' && $key=='elinf' ) ))
                                        || ( A2D::get($_GET,$mt['alias'])==$key )
                                    ) $selected = "selected";
                                    else    $selected = '';
                                    ?>
                                    <option value="<?=$key?>" <?=$selected?>><?=$market?></option>
                                <?php }?>
                                </select><?
                            }
                        }
                    }else{?>
                    <input type="text" name="<?=$t['alias']?>" class="spare_parts" placeholder="Укажите <?=$t['name']?>" value="<?=A2D::get($_GET,$t['alias']);?>">
                <?php }?>

                <input type="submit" class="searchBttn anime uppercase" value="Найти">
                                <?php $where=A2D::get(A2D::$searchWhere,'tabs',[]); ?>
                <?php if( in_array($t['alias'],$where) && count(A2D::$searchWhere)>1 ){ $_wsc=( ($_c=A2D::get(A2D::$searchWhere,'hide')) )?" $_c":"";?>
                    <div class="whereSearch<?=$_wsc?>">
                        <div class="inlineFlex fl"><span id="everyWhere" class="a2dCheckBox mr5"></span> искать везде</div>
                        <div class="item fl ml55 mr20"><span id="onlyHere" class="a2dCheckBox active mr5"></span> <?=A2D::get(A2D::$searchWhere,'desc')?></div>
                        <?php if( ($lists=A2D::get(A2D::$searchWhere,'lists',[])) ) foreach( $lists AS $select ){?>
                                                <div class="item fl">
                            <select name="<?=$select['alias']?>" class="form-control input-sm">
                                <?php foreach( $select['options'] AS $option ){?>
                                <option value="<?=$option->code?>"<?=( A2D::get($_GET,$select['alias'])==$option->code )? " selected":"";?>><?=$option->ru?></option>
                                <?php }?>
                            </select>
                                                </div>
                        <?php }?>
                        <div class="clear"></div>
                        <input type="hidden" name="<?=A2D::get(A2D::$searchWhere,'name')?>" value="<?=A2D::get(A2D::$searchWhere,'value')?>" class="whereSearchInput">
                    </div>
                <?php }?>
            </form>
        </div>
        <?php }?>

    </div>
</section>

<script>
    function tabActive(num){
        $('#A2DSearch').find('.tabkont_active').removeClass().addClass('tabkont');
        $('#A2DSearch').find('.tab_active').removeClass().addClass('tab');
        $('#tab'+num).removeClass().addClass('tab_active');
        $('#tabkont'+num).removeClass().addClass('tabkont_active');
    }

    $(document).ready(function(){
        var $form,$where,$value,_action,
            where  = '.whereSearch',
            value  = '.whereSearchInput',
            action = '<?=A2D::get(A2D::$searchWhere,'gSearch')?>'
            ;
        $('.a2dCheckBox').click(function(){
            if( $(this).hasClass('active') ) return false;
            $form  = $(this).parents('form');
            $where = $form.find(where);

            action = action || $form.attr('action');

            $where.find('.a2dCheckBox').removeClass('active');
            $(this).addClass('active');

            if( this.id=="everyWhere" ){
                $value = $where.find(value).detach();
                _action = $form.attr('action');
                $form.attr('action',action);
            }
            if( this.id=="onlyHere" ){
                $value.appendTo($where);
                $form.attr('action',_action);
            }

        });
    });
</script>
