<?php
/**
 * Linemedia Autoportal
 * Main module
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/**
 * Получает доступы для элементов Linemedia
 * Class LinemediaAutoRightsElements
 */
class LinemediaAutoRightsEntity {

    public static $ENTITY_TYPE_PRICE = 'price'; // шаблон импорта прайслиста

    private static $TABLE = 'b_lm_rights';
    private static $ALL_USERS_GROUP = 2; // группа все пользователи, в т.ч. неавторизованные

    private static $show_debug_default = false;

    /*
     * Массив соответствий типов объектов и биндинга доступов linemedia.auto
     * см. /bitrix/modules/linemedia.auto/constants.php
     */
    private static $ENTITY_TO_BINDING = array(
        'price' => LM_AUTO_ACCESS_BINDING_PRICES_IMPORT,
    );

    private static $ENTITY_TO_MODULE = array(
        'price' => 'linemedia.auto',
    );

    private $entity_type = null;
    private $user_id = null;
    private $is_admin = false;
    private $user_groups = array();
    private $right_level = '';

    /**
     * Инициализация экземпляра класса
     * @param $entity_type - тип объектов, к которым нужно проверить доступ
     */
    public function __construct($entity_type, $user_id = null) {

        global $USER;

        $this->entity_type = $entity_type;

        if(intval($user_id) > 0) {
            $this->user_id = $user_id;
            $this->user_groups = CUser::GetUserGroup($user_id);
        } else if(is_a($USER, 'CUser') && $USER->IsAuthorized()) {
            $this->user_id = $USER->GetID();
            $this->user_groups = $USER->GetUserGroupArray();
        }
        if(in_array(1, $this->user_groups)) $this->is_admin = true;

        if(!$this->user_id) $this->user_groups = array(self::$ALL_USERS_GROUP);
    }

    /**
     * Проверка доступа к элементу. Возвращает букву доступа.
     * @param $entity_id - ИД объекта, к которому проверяется доступ
     * @return string
     */
    public function getRight($entity_id) {

        $right_letter = LM_AUTO_MAIN_ACCESS_DENIED;
        if($this->is_admin) {
            return LM_AUTO_MAIN_ACCESS_FULL;
        }

        $right_letter = $this->getDefaultRights();

        $right_list = $this->getRightsList($entity_id);

        if(count($right_list) > 0) {

            $right = $this->calculateRights($right_list, $this->user_id, $this->user_groups);
            if($right) {
                $right_letter = $right;
                LinemediaAutoDebug::add('LM_RIGHTS FOR ' . $this->entity_type . ' ID = ' . $entity_id . ' : ' . $right, false, LM_AUTO_DEBUG_WARNING);
            }
        }

        return $right_letter;
    }

    /**
     * Получение доступов по умолчанию
     * @return string
     */
    public function getDefaultRights() {

        global $APPLICATION;

        $right_letter = LM_AUTO_MAIN_ACCESS_DENIED;

        if(array_key_exists($this->entity_type, self::$ENTITY_TO_BINDING)) {
            $right_letter = LinemediaAutoGroup::getMaxPermissionId('linemedia.auto', $this->user_groups, array('BINDING' => self::$ENTITY_TO_BINDING[$this->entity_type]));
        } else if(array_key_exists($this->entity_type, self::$ENTITY_TO_MODULE)) {
            $right_letter = $APPLICATION->GetGroupRight(self::$ENTITY_TO_MODULE[$this->entity_type]);
        }

        $this->right_level = 'default';

        if(!self::$show_debug_default) {
            // показывается только один раз
            LinemediaAutoDebug::add('LM_RIGHTS FOR ' . $this->entity_type . ' (default) : ' . $right_letter, false, LM_AUTO_DEBUG_WARNING);
            self::$show_debug_default = true;
        }


        return $right_letter;
    }

    /**
     * Возвращает список возможных доступов для установленного типа объектов
     * @return array
     */
    public function getTaskList() {

        global $DB;

        $result = array();

        if(array_key_exists($this->entity_type, self::$ENTITY_TO_BINDING)) {

            $sql = "SELECT * FROM b_task WHERE BINDING = '".self::$ENTITY_TO_BINDING[$this->entity_type]."' ORDER BY LETTER ASC";

            $rs = $DB->Query($sql);

            while($row = $rs->Fetch()) {
                $result[$row['LETTER']] = $row;
            }
        }

        return $result;
    }

    /**
     * @param $entity_id - ИД объекта, к которому устанавливается доступ
     * @param $rights - массив доступов - GROUP_CODE => ИД таска по таблице b_task; GROUP_CODE это либо группа G123 либо юзер U123
     */
    public function setRights($entity_id, $rights) {

        global $DB;

        if(intval($entity_id) > 0) {

            $this->deleteRights($entity_id);

            foreach($rights as $group_code => $right) {
                $sql = "INSERT INTO " . self::$TABLE . " (GROUP_CODE, ENTITY_TYPE, ENTITY_ID, TASK_ID) VALUES ('".$group_code."', '".$this->entity_type."', '".$entity_id."', '".$right."')";
                $DB->Query($sql);
            }
        }
    }

    /**
     * Выводит содержимое вкладки доступы в админ. интерфейсе
     */
    public function showRightsTab($entity_id) {

        $right_list = $this->getRightsList($entity_id);
        $default_letter = $this->getDefaultRights();
        $task_list = $this->getTaskList();
        $groups = $this->getGroupList();

        $is_default_access = false;
        foreach($right_list as $right) {
            if($right['GROUP_CODE'] == 'G0') { // access for all
                $is_default_access = true;
            }
        }

        if(!$is_default_access) {
            ?>
            <tr>
                <td>
                    <b><?=GetMessage('LM_AUTO_RIGHTS_FOR_ALL')?>:</b>
                </td>
                <td>
                    <span><?=$task_list[$default_letter]['NAME']?></span>
                </td>
                <td></td>
            </tr>
            <?
        }

        foreach($right_list as $right) {
            $group = intval(str_replace('G', '', $right['GROUP_CODE']));
            $name = $groups[$group]['NAME'];
            if($group == 0) $name = GetMessage('LM_AUTO_RIGHTS_FOR_ALL');
            ?>
            <tr>
                <td>
                    <label><?=$name?>
                        <? if($group > 0) { ?><a href="/bitrix/admin/group_edit.php?lang=ru&ID=<?=$group?>" title="<?=$groups[$group]['DESCRIPTION']?>">[<?=$group?>]</a><? } ?>
                    </label>
                    <input type="hidden" name="group[]" value="<?=$group?>" />
                </td>
                <td>
                    <select name="right[]">
                        <? foreach($task_list as $letter => $task) { ?>
                            <? if($right['LETTER'] == $letter) { ?>
                                <option value="<?=$task['ID']?>" selected="selected"><?=$task['NAME']?></option>
                            <? } else { ?>
                                <option value="<?=$task['ID']?>"><?=$task['NAME']?></option>
                            <? } ?>
                        <? } ?>
                    </select>
                </td>
                <td>
                    <a class="del_row" href="javascript:void(0)"><img src="/bitrix/themes/.default/images/actions/delete_button.gif" border="0" width="20" height="20"></a>
                </td>
            </tr>
            <?
        }
        ?>
        <tr class="rights_row">
            <td>
                <select name="group[]">
                    <option value="0"><?=GetMessage('LM_AUTO_RIGHTS_FOR_ALL')?></option>
                    <? foreach($groups as $group_id => $group) { ?>
                        <option value="<?=$group_id?>"><?=$group['NAME']?></option>
                    <? } ?>
                </select>
            </td>
            <td>
                <select name="right[]">
                    <option value="0"><?=GetMessage('LM_AUTO_RIGHTS_AS_DEFAULT')?></option>
                    <? foreach($task_list as $task) { ?>
                        <option value="<?=$task['ID']?>"><?=$task['NAME']?></option>
                    <? } ?>
                </select>
            </td>
            <td>
                <a class="del_row" style="display:none;" href="javascript:void(0)"><img src="/bitrix/themes/.default/images/actions/delete_button.gif" border="0" width="20" height="20"></a>
            </td>
        </tr>
        <tr class="btn_row">
            <td colspan="3" align="right">
                <a href="javascript:void(0)" onclick="addEntityRights();" hidefocus="true" class="adm-btn"><?= GetMessage('LM_AUTO_RIGHTS_ADD_RIGHT') ?></a>
            </td>
        </tr>
        <script>
            $(".del_row").on('click', function() {
                $(this).closest("tr").remove();
            });
            function addEntityRights() {
                var row = $("#rights_edit_table .rights_row:last").clone(true);
                row.find("select").val('0');
                row.insertAfter("#rights_edit_table .rights_row:last");
                $(".del_row").show();
                $("#rights_edit_table .rights_row:last .del_row").hide();
            }
        </script>
        <?
    }

    public function saveFromForm($entity_id) {

        if(intval($entity_id) < 1) return false;

        $groups = $_REQUEST['group'];
        $rights = $_REQUEST['right'];
        $to_save = array();
        if(is_array($groups) && count($groups) > 0 && is_array($rights) && count($rights) > 0) {
            for($i=0; $i<count($groups); $i++) {
                if(intval($rights[$i]) > 0) {
                    $to_save['G' . $groups[$i]] = $rights[$i];
                }
            }
            return $this->setRights($entity_id, $to_save);
        }
        return false;
    }

    private function getGroupList() {

        $groups = array();
        $rs = CGroup::GetList($by="name", $order="asc", array());

        while($group = $rs->Fetch()) {

            $groups[$group['ID']] = $group;
        }

        return $groups;
    }

    private function deleteRights($entity_id) {

        global $DB;
        $sql = "DELETE FROM " . self::$TABLE . " WHERE ENTITY_TYPE = '".$this->entity_type."' AND ENTITY_ID = '".$entity_id."'";

        return $DB->Query($sql);
    }

    /**
     * Определение доступа для пользователя исходя из его членства в группах.
     * @param $rights - массив доступов, полученный из $this->getRight
     * @param $user_id - ИД пользователя
     * @param $user_groups - массив групп пользователя
     * @return string|false - буква доступа
     */
    private function calculateRights($rights, $user_id, $user_groups) {

        // проверка персонального доступа
        if(intval($this->user_id) > 0) {
            $personal_rights = array();
            foreach($rights as $right) {
                if($right['GROUP_CODE'] == 'U' . $this->user_id) {
                    $letter = $right['LETTER'];
                    $personal_rights[] = $letter;
                }
            }
            if(count($personal_rights) > 0) {
                rsort($personal_rights);
                $this->right_level = 'user';
                return current($personal_rights);
            }
        }


        if(count($this->user_groups) > 0) {
            $group_rights = array();
            foreach($rights as $right) {
                if(strpos($right['GROUP_CODE'], 'G') === 0) {
                    $group_id = intval(str_replace('G', '', $right['GROUP_CODE']));
                    $letter = $right['LETTER'];
                    if(in_array($group_id, $this->user_groups)) {
                        $group_rights[] = $letter;
                    }
                }
            }
            if(count($group_rights) > 0) {
                rsort($group_rights);
                $this->right_level = 'group';
                return current($group_rights);
            }
        }
        // for all
        foreach($rights as $right) {
            if(strpos($right['GROUP_CODE'], 'G') === 0) {
                $group_id = intval(str_replace('G', '', $right['GROUP_CODE']));
                $letter = $right['LETTER'];
                if($group_id == 0) {
                    return $letter; // access for all
                }
            }
        }

        return false;
    }

    /**
     * Возвращает список доступов установленных для элемента
     * @param $entity_id - ИД объекта, к которому проверяется доступ
     * @return array
     */
    private function getRightsList($entity_id) {

        global $DB;

        $result = array();

        $sql = "SELECT R.GROUP_CODE, T.LETTER FROM " . self::$TABLE . " R, b_task T WHERE R.TASK_ID = T.ID AND R.ENTITY_TYPE = '".$this->entity_type."' AND R.ENTITY_ID='".$entity_id."'";

        $rs = $DB->Query($sql);

        while($row = $rs->Fetch()) {
            $result[] = $row;
        }

        return $result;
    }

}