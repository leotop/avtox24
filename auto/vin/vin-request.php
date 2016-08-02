<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("tags", "Подбор запчастей по VIN");
$APPLICATION->SetPageProperty("keywords_inner", "Автозапчасти по VIN, подбор по VIN");
$APPLICATION->SetPageProperty("keywords", "Автозапчасти по VIN");
$APPLICATION->SetTitle("VIN запрос");
?><p style="text-align: center;">
<script id="bx24_form_inline" data-skip-moving="true">
        (function(w,d,u,b){w['Bitrix24FormObject']=b;w[b] = w[b] || function(){arguments[0].ref=u;
                (w[b].forms=w[b].forms||[]).push(arguments[0])};
                if(w[b]['forms']) return;
                s=d.createElement('script');r=1*new Date();s.async=1;s.src=u+'?'+r;
                h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
        })(window,document,'https://mosavtomag.bitrix24.ru/bitrix/js/crm/form_loader.js','b24form');

        b24form({"id":"8","lang":"ru","sec":"wlg1om","type":"inline"});
</script>
</p><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>