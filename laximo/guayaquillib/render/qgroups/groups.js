function tree_toggle(event) {
    event = event || window.event
    var clickedElem = event.target || event.srcElement

    if (!hasClass(clickedElem, 'qgExpand')) {
        return // клик не там
    }

    // Node, на который кликнули
    var node = clickedElem.parentNode
    if (hasClass(node, 'qgExpandLeaf')) {
        return // клик на листе
    }

    // определить новый класс для узла
    var newClass = hasClass(node, 'qgExpandOpen') ? 'qgExpandClosed' : 'qgExpandOpen'
    // заменить текущий класс на newClass
    // регексп находит отдельно стоящий open|close и меняет на newClass
    var re =  /(^|\s)(qgExpandOpen|qgExpandClosed)(\s|$)/
    node.className = node.className.replace(re, '$1'+newClass+'$3')
}


function hasClass(elem, className) {
    return new RegExp("(^|\\s)"+className+"(\\s|$)").test(elem.className)
}

var QuickGroups = {};
QuickGroups.Search = function(value)
{
    var filtered_groups = jQuery('#qgFilteredGroups');
    var tree = jQuery('#qgTree');

    if (value.length < 3)
    {
        filtered_groups.css("display", "none");
        tree.css("display", "block");
    }
    else
    {
        filtered_groups.css("display", "block");
        tree.css("display", "none");
        filtered_groups.html('');

        QuickGroups.InnerSearch(value, '', tree, filtered_groups)
    }
}

QuickGroups.InnerSearch = function(value, current_path, item, filtered_groups)
{
    var items = item.children();
    items.each(function()
        {
            var el = jQuery(this);
            if (el.hasClass('qgContent'))
            {
                var text = el.text();
                var text2 = text.replace(new RegExp('(' + value + ')', 'i'), '<span class="qgSelected">$1</span>');
                if (text != text2)
                    jQuery('<div class="qgFilteredGroup"><div class="qgCurrentPath">'+ current_path + '</div><div class="qgFilteredName">'+ el.html().replace(text, text2)+'</div></div>').appendTo(filtered_groups);

                current_path = current_path + ' / ' + text;
            }
            QuickGroups.InnerSearch(value, current_path, el, filtered_groups)
    });
}

////////////////////////дополнительные функции////////////////////////////////


$(function(){     
    bindEvents();       
})     

//вешаем нужные обработчики событий
function bindEvents() {
    //привязываем клик к названию раздела
    $(document).on("click", ".qgContent",  function(){   
        buildBlocksFromTree(this);                       
        //если есть блок для раскрытия подразделов - делаем соответствующие изменения классов для родителя
        if ($(this).siblings(".qgExpand").length > 0 && $(this).find("a").length <= 0) {  
            $(this).parent("li").toggleClass("qgExpandClosed").toggleClass("qgExpandOpen");  
        }                
    })  
    
    $(document).on("click", "#qgTree a", function(e){
        e.preventDefault();
        var category_link = $(e).attr("href");
        loadDetailsInfo(category_link);
    })  

    //привязываем клик к иконке раскрытия списка
    $(document).on("click", ".qgExpand", function(){   
        buildBlocksFromTree(this);
    });

    //имитация клика по квадратам, соответствующим элементам дерева
    $(document).on("click", ".laximo-tree-element", function(){

        var itemIndex = $(this).data("item-index");
        var listItem = $("#qgTree li[data-item-index=" + itemIndex + "]");
        var listItemExpand = listItem.find(".qgContent");
        listItem.removeClass("qgExpandClosed").addClass("qgExpandOpen");   
        buildBlocksFromTree(listItemExpand);              
    })
}

//функция строит набор блоков в основном контейнере, которые дублируют подпункты в левом меню
function buildBlocksFromTree(e) {  
    if ($(e).siblings(".qgContainer").length > 0 ) {        
        $(".laximo-page-content-wrap").html("");  
        var category_title = $(e).parent("li").children(".qgContent").html(); 
        $(".laximo-content-title").html(category_title);
        if ($(e).siblings(".qgContainer").length > 0) {   
            $(e).siblings(".qgContainer").children("li").each(function(){
                var item_title = $(this).find(".qgContent");
                var new_html = item_title.html();
                var itemIndex = $(this).data("item-index");
                $(".laximo-page-content-wrap").append("<div class='laximo-tree-element' data-item-index='" + itemIndex + "'><span>" + new_html + "</span></div>");
            })
        } 
    } else if ($(e).find("a").length > 0) {
        var category_link = $(e).find("a").attr("href"); 
        $(".laximo-page-content-wrap").html("Загрузка...");   
        loadDetailsInfo(category_link);
        var category_title = $(e).find("a").html(); 
        $(".laximo-content-title").html(category_title);
        return false;
        //document.location.href = $(e).find("a").attr("href");
    } else {
        $(".laximo-page-content-wrap").html("");
        $(".laximo-content-title").html("Категории");
        $("#qgTree > .qgContainer > li").each(function(){
            var item_title = $(this).find(".qgContent");
            var new_html = item_title.html();
            var itemIndex = $(this).data("item-index");
            $(".laximo-page-content-wrap").append("<div class='laximo-tree-element' data-item-index='" + itemIndex + "'><span>" + new_html + "</span></div>");
        })
    }

}

//загрузка данных о деталях с соответствующей страницы
function loadDetailsInfo(path) {
    $(".laximo-page-content-wrap").load(path + " .laximo-page-content-wrap > *");
     history.replaceState(3, "", path);
}