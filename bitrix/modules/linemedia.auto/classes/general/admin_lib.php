<?php

class CLMAdminResult extends CAdminResult {
	function NavStart($nPageSize=0, $bShowAll=true, $iNumPage=false)
	{
		if($this->bFromLimited)
			return;

		if(is_array($nPageSize))
			$this->InitNavStartVars($nPageSize);
		else
			$this->InitNavStartVars(intval($nPageSize), $bShowAll, $iNumPage);

		if($this->bFromArray)
		{
			//$this->NavRecordCount = count($this->arResult);
			if($this->NavRecordCount < 1)
				return;

			if($this->NavShowAll)
				$this->NavPageSize = $this->NavRecordCount;

			$this->NavPageCount = floor($this->NavRecordCount/$this->NavPageSize);
			if($this->NavRecordCount % $this->NavPageSize > 0)
				$this->NavPageCount++;

			$this->NavPageNomer =
				($this->PAGEN < 1 || $this->PAGEN > $this->NavPageCount
				?
					(CPageOption::GetOptionString("main", "nav_page_in_session", "Y")!="Y"
						|| $_SESSION[$this->SESS_PAGEN] < 1
						|| $_SESSION[$this->SESS_PAGEN] > $this->NavPageCount
					?
						1
					:
						$_SESSION[$this->SESS_PAGEN]
					)
				:
					$this->PAGEN
				);

			$NavFirstRecordShow = $this->NavPageSize*($this->NavPageNomer-1);
			$NavLastRecordShow = $this->NavPageSize*$this->NavPageNomer;

			$this->arResult = array_slice($this->arResult, $NavFirstRecordShow, $NavLastRecordShow - $NavFirstRecordShow);
		}
		else
		{
			$this->DBNavStart();
		}
	}
}