<?php 

/**
 * Linemedia Autoportal
 * Main module
 * search modificator interface
 * @author  Linemedia
 * @since   22/01/2012
 * @link    http://auto.linemedia.ru/
 */


/**
 * interface CapableToModifyingSearchInterface
 * allow to class implimenting its vary outcome of search by using LinemediaAutoSearchModificator
 */
interface CapableToModifyingSearchInterface
{

	/**
	 * apply modificator to search result
	 * @param array | \Iterator $searchOutcome
	 * @package array | \Iterator $traversal
	 * @return array
	 */
	public function applyModificatorToSearch($searchOutcome, $traversal = null);

	/**
	 * whether or ont logic conditions satisfy search outcome
	 * @param array | \Iterator $searchOutcome
	 * @return boolean
	 */
	public function isAlterationFeasible($searchOutcome);
	
	/**
	 * provide modificator config to debug
	 * @return array
	 */
	public function getDebugInfo();

	/**
	 * set debug info
	 * @param mixed $debug
	 */
	public function setDebugInfo($debug);
	
}
