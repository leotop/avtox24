<?php

/**
 * Класс вывода данных в формате xls.
 */
class LinemediaAutoExcel
{
    const TYPE_INT      = 'int';
    const TYPE_DECIMAL  = 'decimal';
    const TYPE_TEXT     = 'text';
    const TYPE_DATE     = 'date';

    protected $headers  = array();
    protected $notes    = array();
    protected $rows     = array();
    protected $types    = array();


    /**
     * Добавление заголовков.
     */
    public function addHeader($headers)
    {
        $this->headers[] = (array) $headers;
    }


    /**
     * Добавление сообщений.
     */
    public function addNote($note)
    {
        $this->notes[] = (string) $note;
    }


    /**
     * Добавление типов столбцов.
     */
    public function addColumnTypes($types)
    {
        $this->types = (array) $types;
    }


    /**
     * Добавление строки.
     */
    public function addRow($row)
    {
        $this->rows[] = (array) $row;
    }


    /**
     * Формирование html для сохранения.
     */
    function getResult()
    {
		$option = COption::GetOptionString('linemedia.autosuppliers', 'LM_AUTO_SUPPLIERS_XLS_TYPE', 'ssconvert');
		if ($option == 'html') {
			$out = '
	    <html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">
	    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset='.LANG_CHARSET.'">
	    <meta name=ProgId content=Excel.Sheet>
	    <meta name=Generator content="Microsoft Excel 12">
	    <style>
            td.int {mso-number-format:"0";}
            td.decimal {mso-number-format:"0.00";}
            td.text {mso-number-format:"@";}
            td.date {mso-number-format:"dd.mm.yyyy";}

		    .number0 {mso-number-format:0;}
		    .number2 {mso-number-format:Fixed;}

		    <!--table
						{mso-displayed-decimal-separator:\"\,\";
						mso-displayed-thousand-separator:\" \";}
					.xl6321542
						{padding-top:1px;
						padding-right:1px;
						padding-left:1px;
						mso-ignore:padding;
						color:black;
						font-size:10.0pt;
						font-weight:700;
						font-style:normal;
						text-decoration:none;
						font-family:Sans;
						mso-generic-font-family:auto;
						mso-font-charset:0;
						mso-number-format:General;
						text-align:general;
						vertical-align:bottom;
						mso-background-source:auto;
						mso-pattern:auto;
						white-space:nowrap;}
			.xl6421542
					{padding-top:1px;
					padding-right:1px;
					padding-left:1px;
					mso-ignore:padding;
					color:black;
					font-size:9.0pt;
					font-weight:700;
					font-style:normal;
					text-decoration:none;
					font-family:Sans;
					mso-generic-font-family:auto;
					mso-font-charset:0;
					mso-number-format:General;
					text-align:left;
					vertical-align:middle;
					border:.5pt solid windowtext;
					background:#BFBFBF;
					mso-pattern:black none;
					white-space:nowrap;}
			.xl6521542
					{padding-top:1px;
					padding-right:1px;
					padding-left:1px;
					mso-ignore:padding;
					color:black;
					font-size:9.0pt;
					font-weight:700;
					font-style:normal;
					text-decoration:none;
					font-family:Sans;
					mso-generic-font-family:auto;
					mso-font-charset:0;
					mso-number-format:General;
					text-align:center;
					vertical-align:middle;
					border:.5pt solid windowtext;
					background:#BFBFBF;
					mso-pattern:black none;
					white-space:nowrap;}
			.xl6621542
					{padding-top:1px;
					padding-right:1px;
					padding-left:1px;
					mso-ignore:padding;
					color:black;
					font-size:9.0pt;
					font-weight:700;
					font-style:normal;
					text-decoration:none;
					font-family:Sans;
					mso-generic-font-family:auto;
					mso-font-charset:0;
					mso-number-format:General;
					text-align:center;
					vertical-align:middle;
					border:.5pt solid windowtext;
					background:#BFBFBF;
					mso-pattern:black none;
					white-space:normal;}
			.xl6721542
					{padding-top:1px;
					padding-right:1px;
					padding-left:1px;
					mso-ignore:padding;
					color:black;
					font-size:9.0pt;
					font-weight:400;
					font-style:normal;
					text-decoration:none;
					font-family:Sans;
					mso-generic-font-family:auto;
					mso-font-charset:0;
					mso-number-format:General;
					text-align:left;
					vertical-align:middle;
					border:.5pt solid windowtext;
					mso-background-source:auto;
					mso-pattern:auto;
					white-space:nowrap;}
			.xl6821542
					{padding-top:1px;
					padding-right:1px;
					padding-left:1px;
					mso-ignore:padding;
					color:black;
					font-size:9.0pt;
					font-weight:700;
					font-style:normal;
					text-decoration:none;
					font-family:Sans;
					mso-generic-font-family:auto;
					mso-font-charset:204;
					mso-number-format:General;
					text-align:center;
					vertical-align:middle;
					border:.5pt solid windowtext;
					mso-background-source:auto;
					mso-pattern:auto;
					white-space:nowrap;}
		-->
	    </style>
	    </head>
	    <body>';
			$out .= "<table border=0 cellpadding=0 cellspacing=0 width=663 style='border-collapse:collapse;table-layout:fixed;width:497pt'>";
			$out .= "<col class=xl6553521542 width=71 style='mso-width-source:userset;mso-width-alt: 2596;width:53pt'>
					 <col class=xl6553521542 width=99 style='mso-width-source:userset;mso-width-alt: 3620;width:74pt'>
					 <col class=xl6553521542 width=251 style='mso-width-source:userset;mso-width-alt: 9179;width:188pt'>
					 <col class=xl6553521542 width=81 style='mso-width-source:userset;mso-width-alt: 2962;width:61pt'>
					 <col class=xl6553521542 width=66 style='mso-width-source:userset;mso-width-alt: 2413;width:50pt'>
					 <col class=xl6553521542 width=95 style='mso-width-source:userset;mso-width-alt: 3474;width:71pt'>
					 <tr height=17 style='height:12.75pt'>
						  <td height=17 class=xl6553521542 width=71 style='height:12.75pt;width:53pt'></td>
						  <td class=xl6553521542 width=99 style='width:74pt'></td>
						  <td class=xl6553521542 width=251 style='width:188pt'></td>
						  <td class=xl6553521542 width=81 style='width:61pt'></td>
						  <td class=xl6553521542 width=66 style='width:50pt'></td>
						  <td class=xl6553521542 width=95 style='width:71pt'></td>
					 </tr>";

			/*
			 * Сообщения.
			 */
			foreach ($this->notes as $note) {
				$out .= '<tr><td height=17 class=xl6321542 style=\'height:12.75pt\' colspan="'.count(reset($this->headers)).'">' . $note . '</td></tr>';
			}
			$out .= '<tr height=17 style=\'height:12.75pt\'>
						 <td height=17 class=xl6321542 style=\'height:12.75pt\'></td>
						 <td class=xl6553521542></td>
						 <td class=xl6553521542></td>
						 <td class=xl6553521542></td>
						 <td class=xl6553521542></td>
						 <td class=xl6553521542></td>
 					</tr>';
			/*
			 * Заголовки.
			 */
			foreach ($this->headers as $row) {
				$out .= "<tr height=32 style='height:24.0pt'>";
				foreach ($row as $ki=>$item) {
					if ($ki == 0 ) {$out .= '<td height=32 class=xl6421542 style=\'height:24.0pt\'>'.$item.'</td>';}
					elseif ($ki == 4) {$out .= '<td class=xl6621542 width=66 style=\'border-left:none;width:50pt\'>'.$item.'</td>';}
					elseif ($ki == 5) {$out .= '<td class=xl6621542 width=95 style=\'border-left:none;width:71pt\'>'.$item.'</td>';}
					else {$out .= '<td class=xl6421542 style=\'border-left:none\'>'.$item.'</td>';}
				}
				$out .= "</tr>";
			}

			/*
			 * Строки.
			 */
			foreach ($this->rows as $row) {
				$out .= '<tr height=17 style=\'height:12.75pt\'>';
				foreach ($row as $i => $item) {
					/*
					$type = '';
					if ($this->types[$i] && in_array($this->types[$i], self::getTypes())) {
						$type = $this->types[$i];
					}
					*/
					if ($i == 0 ) {$out .= '<td height=17 class=xl6721542 style=\'height:12.75pt;border-top:none\'>'.$item.'</td>';}
					elseif ($i == 1 || $i == 2) {$out .= '<td class=xl6721542 style=\'border-top:none;border-left:none\'>'.$item.'</td>';}
					else {$out .= '<td class=xl6821542 style=\'border-top:none;border-left:none\'>'.$item.'</td>';}
				}
				$out .= '</tr>';
			}
			$out .= '
		  <![if supportMisalignedColumns]>
		 <tr height=0 style=\'display:none\'>
			  <td width=71 style=\'width:53pt\'></td>
			  <td width=99 style=\'width:74pt\'></td>
			  <td width=251 style=\'width:188pt\'></td>
			  <td width=81 style=\'width:61pt\'></td>
			  <td width=66 style=\'width:50pt\'></td>
			  <td width=95 style=\'width:71pt\'></td>
		 </tr>
 		<![endif]>';

			$out .= "</table>";
			$out .= '</body></html>';

		}

		if ($option == 'ssconvert') {
			$out = '
				<html>
				<head>
				<title></title>
				<meta http-equiv="Content-Type" content="text/html; charset='.LANG_CHARSET.'">
				<style>
					td.int {mso-number-format:"0";}
					td.decimal {mso-number-format:"0.00";}
					td.text {mso-number-format:"@";}
					td.date {mso-number-format:"dd.mm.yyyy";}

					.number0 {mso-number-format:0;}
					.number2 {mso-number-format:Fixed;}
				</style>
				</head>
				<body>';

			$out .= "<table border=\"1\">";

			/*
			 * Сообщения.
			 */
			foreach ($this->notes as $note) {
				$out .= '<tr><th colspan="'.count(reset($this->headers)).'">' . $note . '</th></tr>';
			}

			/*
			 * Заголовки.
			 */
			foreach ($this->headers as $row) {
				$out .= "<tr>";
				foreach ($row as $item) {
					$out .= '<th>' . $item . '</th>';
				}
				$out .= "</tr>";
			}

			/*
			 * Строки.
			 */
			foreach ($this->rows as $row) {
				$out .= '<tr>';
				foreach ($row as $i => $item) {
					$type = '';
					if ($this->types[$i] && in_array($this->types[$i], self::getTypes())) {
						$type = $this->types[$i];
					}
					$out .= '<td class="'.$type.'">'.$item.'</td>';
				}
				$out .= '</tr>';
			}

			$out .= "</table>";
			$out .= '</body></html>';
		}

	    return $out;
    }


    public static function getTypes()
    {
        $types = array(
            self::TYPE_INT,
            self::TYPE_DECIMAL,
            self::TYPE_TEXT,
            self::TYPE_DATE
        );
        return $types;
    }

}
