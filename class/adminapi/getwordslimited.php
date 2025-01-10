<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\AdminApi;

use Xaraya\Modules\MethodClass;
use xarDB;
use xarModVars;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords adminapi getwordslimited function
 */
class GetwordslimitedMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get entries for a module item
     * @param int $args ['modid'] module id
     * @param int $args ['itemtype'] itemtype
     * @return array|string|void of keywords
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!isset($moduleid) || !is_numeric($moduleid)) {
            $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)', 'module id', 'user', 'getwordslimited', 'keywords');
            throw new BadParameterException(null, $msg);
        }

        $dbconn = xarDB::getConn();
        $xartable = & xarDB::getTables();
        $keywordstable = $xartable['keywords_restr'];
        $bindvars = [];

        // Get restricted keywords for this module item
        $query = "SELECT id,
                         keyword
                  FROM $keywordstable
                  WHERE module_id = ?";

        $bindvars[] = $moduleid;

        if (isset($itemtype)) {
            $query .= " AND itemtype = ?";
            $bindvars[] = $itemtype;
        }
        $query .= " ORDER BY keyword ASC";
        $result = & $dbconn->Execute($query, $bindvars);

        if (!$result) {
            return;
        }
        $keywords = [];
        $keywords = '';
        if ($result->EOF) {
            $result->Close();
            return $keywords;
        }
        while (!$result->EOF) {
            [$id,
                $word] = $result->fields;
            $keywords[$id] = $word;
            $result->MoveNext();
        }
        $result->Close();

        $delimiters = xarModVars::get('keywords', 'delimiters');
        $delimiter = substr($delimiters, 0, 1) . " ";
        $keywords = implode($delimiter, $keywords);

        return $keywords;
    }
}