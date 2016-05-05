<?php
global $APPLICATION;
$APPLICATION->AddHeadString('<script src="https://www.google.com/jsapi" type="text/javascript"></script>'); // диаграммы
CUtil::InitJSCore(array('window', 'ajax','popup')); //для всплыв. диалога битрикса