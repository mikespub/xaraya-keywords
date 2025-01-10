<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\UserApi;

use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarMod;
use xarDB;
use Query;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords userapi search function
 */
class SearchMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Perform the search
     * @return array with keys to keywords
     */
    public function __invoke(array $args = [])
    {
        if (!xarSecurity::check('ReadKeywords')) {
            return;
        }

        if (empty($args) || count($args) < 1) {
            return;
        }

        extract($args);
        if ($args['search'] == '') {
            return;
        }

        // If there is more than one keyword passed, separate them
        $words = xarMod::apiFunc('keywords', 'admin', 'separatekeywords', ['keywords' => $args['search']]);

        // Get item
        sys::import('xaraya.structures.query');
        $tables = & xarDB::getTables();
        $q = new Query('SELECT');
        $q->addtable($tables['keywords'], 'k');
        $q->addtable($tables['keywords_index'], 'i');
        $q->join('k.id', 'i.keyword_id');
        $q->addfield('k.keyword AS keyword');
        $q->addfield('i.module_id AS module_id');
        $q->addfield('i.itemtype AS itemtype');
        $q->addfield('i.itemid AS itemid');
        $q->addfield('COUNT(i.id) AS count');
        $a = [];
        foreach ($words as $word) {
            $a[] = $q->plike('keyword', "%" . $word . "%");
        }
        $q->qor($a);
        $q->setgroup('keyword');
        $q->addorder('keyword', 'ASC');
        $q->optimize = false;
        $q->run();
        $result = $q->output();

        return $result;
    }
}