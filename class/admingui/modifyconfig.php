<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\AdminGui;


use Xaraya\Modules\Keywords\AdminGui;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarVar;
use xarMod;
use xarSec;
use xarController;
use xarModVars;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords admin modifyconfig function
 * @extends MethodClass<AdminGui>
 */
class ModifyconfigMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Prepare data for form. May be called from form itself with updated
     * configuration parameters.
     * @author mikespub
     * @access public
     * @param int $restricted 1 for pregiven keyword list, 0 for free input
     * @param int $useitemtype 1 for itemtype specific keyword lists
     * @return bool|string|void on success or void on failure
     * @todo nothing
     */
    public function __invoke(array $args = [])
    {
        if (!xarSecurity::check('AdminKeywords')) {
            return;
        }

        if (!xarVar::fetch('module_id', 'id', $module_id, null, xarVar::DONT_SET)) {
            return;
        }
        if (!xarVar::fetch('itemtype', 'int:0:', $itemtype, null, xarVar::DONT_SET)) {
            return;
        }
        if (!xarVar::fetch('phase', 'pre:trim:lower:enum:update', $phase, 'form', xarVar::NOT_REQUIRED)) {
            return;
        }
        if (!xarVar::fetch('return_url', 'pre:trim:str:1:', $return_url, '', xarVar::NOT_REQUIRED)) {
            return;
        }

        $data = [];

        if (empty($module_id)) {
            $modname = 'keywords';
            $itemtype = null;
        } else {
            $modname = xarMod::getName($module_id);
        }

        if ($modname == 'keywords') {
            $data['module_settings'] = xarMod::apiFunc('base', 'admin', 'getmodulesettings', ['module' => 'keywords']);
            $data['module_settings']->setFieldList('items_per_page, use_module_alias, module_alias_name, enable_short_urls, use_module_icons, frontend_page, backend_page');
            $data['module_settings']->getItem();
        }

        if ($phase == 'update') {
            if (!xarSec::confirmAuthKey()) {
                return xarController::badRequest('bad_author', $this->getContext());
            }
            if ($modname == 'keywords') {
                $isvalid = $data['module_settings']->checkInput();
                if ($isvalid) {
                    $itemid = $data['module_settings']->updateItem();
                    if (!xarVar::fetch('delimiters', 'pre:trim:str:1:', $delimiters, xarModVars::get('keywords', 'delimiters', ','), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    if (!xarVar::fetch('stats_per_page', 'int:0:', $stats_per_page, xarModVars::get('keywords', 'stats_per_page', 100), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    if (!xarVar::fetch('items_per_page', 'int:0:', $items_per_page, xarModVars::get('keywords', 'items_per_page', 20), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    if (!xarVar::fetch('user_layout', 'pre:trim:lower:enum:list:cloud', $user_layout, xarModVars::get('keywords', 'user_layout', 'list'), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    xarModVars::set('keywords', 'delimiters', $delimiters);
                    xarModVars::set('keywords', 'stats_per_page', $stats_per_page);
                    xarModVars::set('keywords', 'items_per_page', $items_per_page);
                    xarModVars::set('keywords', 'user_layout', $user_layout);
                    //if ($user_layout == 'list') {
                    if (!xarVar::fetch('cols_per_page', 'int:0:', $cols_per_page, xarModVars::get('keywords', 'cols_per_page', 2), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    if (!xarVar::fetch('words_per_page', 'int:0:', $words_per_page, xarModVars::get('keywords', 'words_per_page', 50), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    xarModVars::set('keywords', 'cols_per_page', $cols_per_page);
                    xarModVars::set('keywords', 'words_per_page', $words_per_page);
                    //} else {
                    // the cloudy stuff
                    if (!xarVar::fetch('cloud_font_min', 'int:1:', $cloud_font_min, xarModVars::get('keywords', 'cloud_font_min', 1), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    if (!xarVar::fetch('cloud_font_max', 'int:1:', $cloud_font_max, xarModVars::get('keywords', 'cloud_font_max', 1), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    if (!xarVar::fetch('cloud_font_unit', 'pre:trim:lower:enum:em:pt:px:%', $cloud_font_unit, xarModVars::get('keywords', 'cloud_font_unit', 'em'), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    xarModVars::set('keywords', 'cloud_font_min', $cloud_font_min);
                    xarModVars::set('keywords', 'cloud_font_max', $cloud_font_max);
                    xarModVars::set('keywords', 'cloud_font_unit', $cloud_font_unit);
                    //}
                }
            }
            if (!xarMod::apiFunc(
                'keywords',
                'hooks',
                'moduleupdateconfig',
                [
                    'objectid' => $modname,
                    'extrainfo' => ['module' => $modname, 'itemtype' => $itemtype],
                ]
            )) {
                return;
            }
            if (empty($return_url)) {
                $return_url = xarController::URL(
                    'keywords',
                    'admin',
                    'modifyconfig',
                    [
                        'module_id' => $module_id,
                        'itemtype' => $itemtype,
                    ]
                );
            }
            xarController::redirect($return_url, null, $this->getContext());
        }

        // form phase
        $data['module_id'] = $module_id;
        $data['modname'] = $modname;
        $data['itemtype'] = $itemtype;

        if ($modname == 'keywords') {
            $data['delimiters'] = xarModVars::get('keywords', 'delimiters', ',');
            $data['stats_per_page'] = xarModVars::get('keywords', 'stats_per_page', 100);
            $data['items_per_page'] = xarModVars::get('keywords', 'items_per_page', 20);
            $data['user_layout'] = xarModVars::get('keywords', 'user_layout', 'list');

            if ($data['user_layout'] == 'list') {
                $data['cols_per_page'] = xarModVars::get('keywords', 'cols_per_page', 2);
                $data['words_per_page'] = xarModVars::get('keywords', 'words_per_page', 50);
            } else {
                $data['cloud_font_min'] = xarModVars::get('keywords', 'cloud_font_min', 1);
                $data['cloud_font_max'] = xarModVars::get('keywords', 'cloud_font_max', 3);
                $data['cloud_font_unit'] = xarModVars::get('keywords', 'cloud_font_unit', 'em');
                $data['font_units'] = [
                    ['id' => 'em', 'name' => 'em'],
                    ['id' => 'pt', 'name' => 'pt'],
                    ['id' => 'px', 'name' => 'px'],
                    ['id' => '%', 'name' => '%'],
                ];
            }

            $data['user_layouts'] = [
                ['id' => 'list', 'name' => xarML('List')],
                ['id' => 'cloud', 'name' => xarML('Cloud')],
            ];
        }

        $data['subjects'] = xarMod::apiFunc('keywords', 'hooks', 'getsubjects');
        $data['hook_config'] = xarMod::guiFunc(
            'keywords',
            'hooks',
            'modulemodifyconfig',
            [
                'objectid' => $modname,
                'extrainfo' => ['module' => $modname, 'itemtype' => $itemtype],
            ]
        );

        return $data;
    }
}
