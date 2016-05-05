<?php
/**
 * Linemedia Autoportal
 * Main module
 * Convert prices agent
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

/**
 * ����� ��� ������ � �����������, ������������ � ������, �������� �� 1�
 * ����� ���������� ����� ���� ��� #ORDER_ID#.ext ��� � #ORDER_ID#-#BASKET_ID#.ext
 * Class LinemediaAutoOrderDocuments
 */
class LinemediaAutoOrderDocuments {

    /**
     * ����� upload � ������� ��������� ���������
     * @var string
     */
    public static $LM_AUTO_UPLOAD_DOC_FOLDER = '/upload/documents/';

    /**
     * ��������� ������� ����������
     * @var array
     */
    protected $files = array(
        'order' => array(),
        'basket' => array(),
    );

    /**
     * ����������� ���
     * @var array
     */
    static $cache;

    /**
     * ������ ���� ������ ���������� ����������� �� ����� ������
     * @var string
     */
    private static $ORDER_NAME_TEMPLATE = '#ORDER_ID#';
    /**
     * ������ ���� ������ ���������� ����������� � ��������� �������� ������
     * @var string
     */
    private static $BASKET_NAME_TEMPLATE = '#ORDER_ID#-#BASKET_ID#';

    /**
     * �����������, ������������ ������� ����������� � ������ ������
     * @param $order_id
     */
    public function __construct($order_id) {

        // ���� ���������� �� �������� ������
        $document_types = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_ORDER_DOCUMENT_TYPES'));

        if(intval($order_id) > 0 && is_array($document_types) && count($document_types) > 0) {

            if(is_array(self::$cache) && array_key_exists($order_id, self::$cache)) {

                $this->files = self::$cache[$order_id];

            } else {

                $order = new LinemediaAutoOrder($order_id);
                $baskets = $order->getBaskets();

                // �������� ���� ������
                $file_names = array();
                // ����� �� ����� ������
                $name = str_replace('#ORDER_ID#', $order_id, self::$ORDER_NAME_TEMPLATE);
                $file_names[$name] = array(
                    'basket_id' => false,
                );
                // ����� � ��������
                foreach($baskets as $basket) {
                    $name = str_replace(array('#ORDER_ID#', '#BASKET_ID#'), array($order_id, $basket['ID']), self::$BASKET_NAME_TEMPLATE);
                    $file_names[$name] = array(
                        'basket_id' => $basket['ID'],
                    );
                }

                foreach($document_types as $type) {

                    $folder = $type['folder'];
                    $folder_path = $_SERVER['DOCUMENT_ROOT'] . self::$LM_AUTO_UPLOAD_DOC_FOLDER . $folder . '/';

                    if(file_exists($folder_path)) {

                        $file_list = scandir($folder_path);

                        foreach($file_list as $file) {

                            $path_parts = pathinfo($file);
                            if(array_key_exists($path_parts['filename'], $file_names)) {

                                $ext = strtolower($path_parts['extension']);
                                if(!($basket_id = $file_names[$path_parts['filename']]['basket_id'])) {
                                    $this->files['order'][] = array(
                                        'type_name' => $type['name'],
                                        'file_name' => $file,
                                        'extension' => $ext,
                                        'folder' => $folder,
                                    );
                                } else {
                                    $this->files['basket'][$basket_id][] = array(
                                        'type_name' => $type['name'],
                                        'file_name' => $file,
                                        'extension' => $ext,
                                        'folder' => $folder,
                                    );
                                }
                            }
                        }
                    }
                } // foreach($document_types as $type)
                if(!is_array(self::$cache)) self::$cache = array();
                self::$cache[$order_id] = $this->files;
            } // if(is_array(self::$cache) && array_key_exists($order_id, self::$cache))

        } // if(is_array($document_folders) && count($document_folders) > 0)
    }

    /**
     * ��������� ������ ������ ������
     * @return array
     */
    public function getFiles() {
        return $this->files;
    }

    /**
     * ���������� �������� ������
     * @param $link_template - BASE_FOLDER . "/print.php?folder=#FILE_FOLDER#&file=#FILE_NAME#"
     * @return array
     */
    public function getFileLinks($link_template) {

        $links = array();

        foreach($this->files as $type => $file_list) {

            foreach($file_list as $file) {

                $link = $link_template;
                $link = str_replace('#FILE_FOLDER#', $file['folder'], $link);
                $link = str_replace('#FILE_NAME#', $file['file_name'], $link);

                $links[$type][] = array(
                    'TYPE_NAME' => $file['type_name'],
                    'ORIG_LINK' => $link,
                    'SHORT_LINK' => false,
                    'FILE_NAME' => $file['file_name'],
                );
            }
        }

        /* ������� �������� ������, ������� ��� ���� */
        $res = CBXShortUri::GetList(Array(), Array());
        while($fields = $res->Fetch()) {

            foreach($links as $type => $link_list) {

                foreach($link_list as $key => $link) {

                    if($link['ORIG_LINK'] == $fields['URI']) {
                        $links[$type][$key]['SHORT_LINK'] = '/' . $fields['SHORT_URI'];
                    }
                }
            }


        }

        /* �������� ����� ������ */
        foreach($links as $type => $link_list) {

            foreach($link_list as $key => $link) {

                if(!$link['SHORT_LINK']) {

                    $short_uri = CBXShortUri::GenerateShortUri(); //���������� ����� ������
                    $fields = Array(
                        "URI" => $link['ORIG_LINK'],
                        "SHORT_URI" => $short_uri,
                        "STATUS" => "301",
                    );
                    $id = CBXShortUri::Add($fields); //��������� ������

                    $links[$type][$key]['SHORT_LINK'] = '/' . $short_uri;
                }
            }
        }


        return $links;
    }

    /**
     * ��������� ������� ����� ��� ��������, ������� ��� ����������
     * @param $folder_name
     * @return bool
     */
    public static function checkUploadFolder($folder_name) {

        if(!empty($folder_name)) {

            $folder_name = self::safeFolderName($folder_name);

            $path = $_SERVER['DOCUMENT_ROOT'] . self::$LM_AUTO_UPLOAD_DOC_FOLDER . $folder_name . '/';

            if(!file_exists($path)) {
                return mkdir($path, 0777, true);
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * ��������� ������������ ������� � ����� �����
     * @param $folder_name
     * @return string
     */
    public static function safeFolderName($folder_name) {
        return rawurlencode(str_replace('.', '', trim($folder_name)));
    }
}