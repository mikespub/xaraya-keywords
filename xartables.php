<?php
/**
 * Keywords Module
 *
 * @package modules
 * @subpackage keywords module
 * @category Third Party Xaraya Module
 * @version 2.0.0
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://xaraya.com/index.php/release/187.html
 * @author mikespub
 */
/**
 * Return keywords table names to xaraya
 *
 * This function is called internally by the core whenever the module is
 * loaded.  It is loaded by xarMod__loadDbInfo().
 *
 * @access private
 * @return array
 */
function keywords_xartables()
{
    // Initialise table array
    $xarTables = array();
    // Get the name for the keywords item table.  This is not necessary
    // but helps in the following statements and keeps them readable

    $keywordsTable = xarDB::getPrefix() . '_keywords';
    // Set the table name
    $xarTables['keywords'] = $keywordsTable;

    $keywordsTable_restr = xarDB::getPrefix() . '_keywords_restr';
    // Set the table name
    $xarTables['keywords_restr'] = $keywordsTable_restr;
    $xarTables['keywords_index'] = xarDB::getPrefix() . '_keywords_index';


    // Return the table information
    return $xarTables;
}

?>
