<?php

/**
 * install step 3 (final)
 *
 * @author  Linemedia
 * @since   01/08/2012
 *
 * @link    http://auto.linemedia.ru/
 */

$MESS['LINEMEDIA_AUTO_MAIN_MODULE_NOT_INSTALLED']  = 'Для работы данного модуля необходимо иметь установленный модуль <a href="http://marketplace.1c-bitrix.ru/solutions/linemedia.auto/" target="_blank">Linemedia Автоэксперт</a>! Продолжение установки невозможно.'; 

$MESS['LM_AUTO_DOWNLOADER_PROTOCOLS_INFO'] = 'Модуль предоставляет возможность скачивать прайслисты с других сайтов разными способами.<br>Существуют разные варианты подключения к сайтам, например через <b>FTP</b> или просто по <b>URL</b>.<br><br>Ниже представлен список возможных типов подключения и соответствие настроек вашего сервера необходимым требованиям.<br><br>Чтобы использовать все возможности модуля, необходимо выполнить все требования, перечисленные ниже и добиться того, чтобы все строчки стали зелёными.';

$MESS['PROTOCOL_AVAILABLE'] = "Протокол доступен";
$MESS['PROTOCOL_UNAVAILABLE'] = "Протокол не может быть использован";


$MESS['LM_AUTO_DOWNLOADER_NO_CONVERTER'] = '<b>Внимание! Модуль не сможет автоматически конвертировать файлы XLS и XLSX!</b><br>Для корректной работы конвертера необходимо установить на сервер программу <b><a href="http://projects.gnome.org/gnumeric/doc/sect-files-ssconvert.shtml">ssconvert</a></b> из пакета <a href="http://projects.gnome.org/gnumeric/">gnumeric</a>.<br>На Debian системах это можно сделать с помощью команды <b>sudo apt-get install gnumeric</b>. Для других систем пакеты можно найти <a href="http://pkgs.org/download/gnumeric">здесь</a>.<br>
<br>
К примеру на <b>CentOS x64</b> установить программу можно выполнив следующие команды:<br>
<pre>sudo yum install GConf2 libglade2-devel.x86_64 goffice-devel.x86_64 -y
wget http://puias.math.ias.edu/data/puias/6/x86_64/os/Addons/gnumeric-1.10.10-1.puias6.1.x86_64.rpm
sudo rpm -i gnumeric-1.10.10-1.puias6.1.x86_64.rpm</pre><br>
<br><br>Вы можете использовать модуль без этой программы, но только с файлами в формате <b>CSV</b>!';


$MESS['LM_AUTO_DOWNLOADER_INSTALL'] = "Установить";

