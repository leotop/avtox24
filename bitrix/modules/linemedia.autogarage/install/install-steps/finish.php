<?php

IncludeModuleLangFile(__FILE__);

/* 
 * �������������� ��������� ������.
 * 
 * ����������� ���������� �� ��������� �����, �.�. ����� ��� �������� ������� ������.
 * ��� ���� ������� �� ���������� �������� ���� ���������� ����� ����������� ����� �����.
 */
RegisterModule('linemedia.autogarage');

?>

<?= CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage("LM_AUTO_GARAGE_INSTALL_SUCCESS"), 'TYPE' => 'OK')) ?>
