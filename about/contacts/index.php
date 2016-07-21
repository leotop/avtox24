<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Avtox24");
/*$APPLICATION->SetTitle("Задайте вопрос");*/
?><p>
 <b>Телефоны отдела продаж:</b>
</p>
<p>
</p>
<h3> <b>8&nbsp;(495) 150-12-95</b>&nbsp;</h3>
<p>
</p>
<p>
</p>
<p>
</p>
<p>
</p>
<p>
	 Телефон для сообщений&nbsp;<br>
	 &nbsp;+7 (926) 415-00-10&nbsp;<br>
 <img width="96" alt="i.jpg" src="http://avtox24.ru/upload/medialibrary/6d0/6d0d142c6d23d8d6c7ee09b24e9e4308.jpg" height="32" title="i.jpg">
</p>
<p>
	<br><div class="mango-call-site" data-options='{"host": "lk.mango-office.ru/", "id": "MTAwMDI5MDk=", "errorMessage": "В данный момент наблюдаются технические проблемы и совершение звонка невозможно"}'>
	<button style=" border-radius: 24px;   background: #08b8e8; width: 175px;height: 36px;white-space: nowrap;border: none;font-family: 'SegoeUISemibold';color: #fff;font-size: 13px;cursor: pointer;">Звонок с сайта</button>
</div>
<script>!function(t){function e(t){options=JSON.parse(t.getAttribute("data-options")),t.querySelector("button, a").setAttribute("onClick","window.open('https://"+options.host+"widget/call-from-site-auto-dial/"+options.id+"', '_blank', 'width=238,height=400,resizable=no,toolbar=no,menubar=no,location=no,status=no'); return false;")}for(var o=document.getElementsByClassName(t),n=0;n<o.length;n++){var i=o[n];if("true"!=o[n].getAttribute("init")){options=JSON.parse(o[n].getAttribute("data-options"));var a=document.createElement("link");a.setAttribute("rel","stylesheet"),a.setAttribute("type","text/css"),a.setAttribute("href",window.location.protocol+"//"+options.host+"widget/widget-button.css"),a.readyState?a.onreadystatechange=function(){("complete"==this.readyState||"loaded"==this.readyState)&&e(i)}:(a.onload=e(i),a.onerror=function(){options=JSON.parse(i.getAttribute("data-options")),i.querySelector("."+t+" button, ."+t+" a").setAttribute("onClick","alert('"+options.errorMessage+"');")}),(i||document.documentElement).appendChild(a),i.setAttribute("init","true")}}}("mango-call-site");</script>
</p>
<p>
	<br>
</p>
 <b>Адрес: &nbsp;</b><b style="font-size: large;">&nbsp;</b><b>127238</b>&nbsp;<b>г. Москва, Ильменский пр-д., д.1. стр1. оф 1</b>
<p>
 <a href="mailto:info@avtox24.ru">info@</a><a href="mailto:info@avtox24.ru">avtox24.ru</a>
</p>
<p>
	 <script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=dh_w6LkreZTlkCG5jiHFW7PQqpfecPnf&width=-1&height=450"></script>
</p>
 <br>
<p>
</p>
<div style="page-break-after: always">
 <span style="display: none">&nbsp;</span>
</div>
 Уважаемые покупатели! <br>
 Прежде чем задать свой вопрос, обратите внимание на раздел <a href="../faq/">Помощь покупателю</a>. Возможно, там уже есть исчерпывающая информация по решению вашей проблемы.
<p>
</p>
 <?$APPLICATION->IncludeComponent(
	"bitrix:main.feedback",
	"lm-auto",
	Array(
		"COMPONENT_TEMPLATE" => "lm-auto",
		"EMAIL_TO" => "info@avtox24.ru",
		"EVENT_MESSAGE_ID" => array(0=>"7",),
		"OK_TEXT" => "Спасибо, ваше сообщение принято.",
		"REQUIRED_FIELDS" => array(),
		"USE_CAPTCHA" => "Y"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>