<?php

require_once dirname(__FILE__) . '/../template.php';

class GuayaquilOperationSearchForm extends GuayaquilTemplate
{
    public $catalog;

    /**
     * @param string $catalog Catalog code
     * @param \SimpleXMLElement $operation Operation element from CatalogInfo
     * @param array $data previous entered data
     * @return string html data
     */

    function Draw($catalog, $operation, $data = '')
    {
        $this->catalog = $catalog;

        if (!is_array($data)) {
            $data = array();
        }
        $html = $this->DrawCheckScript($operation);
        $html .= $this->DrawForm($operation, $data);

        return $html;
    }

    function DrawCheckScript($operation)
    {
        $html = '<script type="text/javascript">
        function checkCustomForm' . $operation['name'] . '(form, submit_btn) {
            var testResult = true;

            jQuery(form).find(":input").each(function() {
                if (jQuery(this).data("regexp")) {
                    var regexp=jQuery(this).data("regexp");
                    var value = jQuery(this).val();
                    var expr = new RegExp(regexp, \'i\');
                    if (expr.test(value))
                    {
                        jQuery(this).attr(\'class\',\'g_input\');
                    } else {
                        jQuery(this).attr(\'class\',\'g_input_error\');
                        testResult = false;
                    }
                }
            });

            if (testResult) {
                jQuery(submit_btn).attr(\'disabled\', \'1\');
            }

            return testResult;
        }
        </script> ';

        return $html;
    }

    function DrawForm($operation, $data)
    {
        $link = $this->FormatLink('vehicles', NULL, $this->catalog);
        $actionUrl =
            (parse_url($link, PHP_URL_SCHEME) ? parse_url($link, PHP_URL_SCHEME) . '://' : '') .
            (parse_url($link, PHP_URL_HOST) ? parse_url($link, PHP_URL_HOST) : '') .
            (parse_url($link, PHP_URL_PORT) ? ':' . parse_url($link, PHP_URL_PORT) : '') .
            parse_url($link, PHP_URL_PATH);

        $html = '
        <div class="formExampleText">'.CommonExtender::LocalizeString('inputmodel').' INFINITI FX35/FX45</div>
        <form name="findByCustom" method="GET" action="' . $actionUrl . '" onSubmit="return checkCustomForm' . $operation['name'] . '(this);">
            ';

        foreach ($operation->field as $field) {
            $name = (string)$field['name'];
            $html .= '<input name="data[' . $name . ']" data-regexp="' . $field['pattern'] . '" value="' . @$data[$name] . '" type="text">';
        }

        $html .= '<input type = "submit" value = "' . $this->GetLocalizedString('Search') . '"/>';

        $query = explode('&', html_entity_decode(parse_url($link, PHP_URL_QUERY)));

        foreach ($query as $item) {
            $x = explode('=', $item);
            $html .= '<input type="hidden" name="' . $x[0] . '" value="' . $x[1] . '"/>';
        }

        $html .= '<input type="hidden" name="operation" value="'.$operation['name'].'"/>';
        $html .= '</form>';

        return $html;
    }
}
