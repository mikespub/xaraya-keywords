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
 * Prepare data for form. May be called from form itself with updated
 * configuration parameters.
 *
 * @author mikespub
 * @access public
 * @param int $restricted 1 for pregiven keyword list, 0 for free input
 * @param int $useitemtype 1 for itemtype specific keyword lists
 * @return true on success or void on failure
 * @throws no exceptions
 * @todo nothing
 */
function keywords_admin_modifyconfig()
{
    if (!xarSecurityCheck('AdminKeywords')) return;

    if (!xarVarFetch('module_id', 'id',
        $module_id, null, XARVAR_DONT_SET)) return;
    if (!xarVarFetch('itemtype', 'int:0:',
        $itemtype, null, XARVAR_DONT_SET)) return;
    if (!xarVarFetch('phase', 'pre:trim:lower:enum:update',
        $phase, 'form', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('return_url', 'pre:trim:str:1:',
        $return_url, '', XARVAR_NOT_REQUIRED)) return;

    $data = array();

    if (empty($module_id)) {
        $modname = 'keywords';
        $itemtype = null;
    } else {
        $modname = xarMod::getName($module_id);
    }

    if ($modname == 'keywords') {
        $data['module_settings'] = xarMod::apiFunc('base','admin','getmodulesettings',
            array('module' => 'keywords'));
        $data['module_settings']->setFieldList('use_module_alias, module_alias_name, enable_short_urls, use_module_icons');
        $data['module_settings']->getItem();
    }

    if ($phase == 'update') {
        if (!xarSecConfirmAuthKey())
            return xarTpl::module('privileges', 'user', 'errors', array('layout' => 'bad_author'));
        if ($modname == 'keywords') {
            $isvalid = $data['module_settings']->checkInput();
            if ($isvalid) {
                $itemid = $data['module_settings']->updateItem();
                if (!xarVarFetch('delimiters', 'pre:trim:str:1:',
                    $delimiters, xarModVars::get('keywords', 'delimiters', ','), XARVAR_NOT_REQUIRED)) return;
                if (!xarVarFetch('stats_per_page', 'int:0:',
                    $stats_per_page, xarModVars::get('keywords', 'stats_per_page',100), XARVAR_NOT_REQUIRED)) return;
                if (!xarVarFetch('items_per_page', 'int:0:',
                    $items_per_page, xarModVars::get('keywords', 'items_per_page',20), XARVAR_NOT_REQUIRED)) return;
                if (!xarVarFetch('user_layout', 'pre:trim:lower:enum:list:cloud',
                    $user_layout, xarModVars::get('keywords', 'user_layout','list'), XARVAR_NOT_REQUIRED)) return;
                xarModVars::set('keywords', 'delimiters', $delimiters);
                xarModVars::set('keywords', 'stats_per_page', $stats_per_page);
                xarModVars::set('keywords', 'items_per_page', $items_per_page);
                xarModVars::set('keywords', 'user_layout', $user_layout);
                //if ($user_layout == 'list') {
                    if (!xarVarFetch('cols_per_page', 'int:0:',
                        $cols_per_page, xarModVars::get('keywords', 'cols_per_page', 2), XARVAR_NOT_REQUIRED)) return;
                    if (!xarVarFetch('words_per_page', 'int:0:',
                        $words_per_page, xarModVars::get('keywords', 'words_per_page', 50), XARVAR_NOT_REQUIRED)) return;
                    xarModVars::set('keywords', 'cols_per_page', $cols_per_page);
                    xarModVars::set('keywords', 'words_per_page', $words_per_page);
                //} else {
                    // the cloudy stuff
                    if (!xarVarFetch('cloud_font_min', 'int:1:',
                        $cloud_font_min, xarModVars::get('keywords', 'cloud_font_min', 1), XARVAR_NOT_REQUIRED)) return;
                    if (!xarVarFetch('cloud_font_max', 'int:1:',
                        $cloud_font_max, xarModVars::get('keywords', 'cloud_font_max', 1), XARVAR_NOT_REQUIRED)) return;
                    if (!xarVarFetch('cloud_font_unit', 'pre:trim:lower:enum:em:pt:px:%',
                        $cloud_font_unit, xarModVars::get('keywords', 'cloud_font_unit', 'em'), XARVAR_NOT_REQUIRED)) return;
                    xarModVars::set('keywords', 'cloud_font_min', $cloud_font_min);
                    xarModVars::set('keywords', 'cloud_font_max', $cloud_font_max);
                    xarModVars::set('keywords', 'cloud_font_unit', $cloud_font_unit);
                //}
            }
        }
        if (!xarMod::apiFunc('keywords', 'hooks', 'moduleupdateconfig',
            array(
                'objectid' => $modname,
                'extrainfo' => array('module' => $modname, 'itemtype' => $itemtype),
            ))) return;
        if (empty($return_url))
            $return_url = xarModURL('keywords', 'admin', 'modifyconfig',
                array(
                    'module_id' => $module_id,
                    'itemtype' => $itemtype,
                ));
        xarController::redirect($return_url);
    }

    // form phase
    $data['module_id'] = $module_id;
    $data['modname'] = $modname;
    $data['itemtype'] = $itemtype;

    if ($modname == 'keywords') {
        $data['delimiters'] = xarModVars::get('keywords', 'delimiters');
        $data['stats_per_page'] = xarModVars::get('keywords', 'stats_per_page', 100);
        $data['items_per_page'] = xarModVars::get('keywords', 'items_per_page', 20);
        $data['user_layout'] = xarModVars::get('keywords', 'user_layout', 'list');

        if ($data['user_layout'] == 'list') {
            $data['cols_per_page'] = xarModVars::get('keywords', 'cols_per_page', 2);
            $data['words_per_page'] = xarModVars::get('keywords', 'words_per_page',50);
        } else {
            $data['cloud_font_min'] = xarModVars::get('keywords', 'cloud_font_min', 1);
            $data['cloud_font_max'] = xarModVars::get('keywords', 'cloud_font_max', 3);
            $data['cloud_font_unit'] = xarModVars::get('keywords', 'cloud_font_unit', 'em');
            $data['font_units'] = array(
                array('id' => 'em', 'name' => 'em'),
                array('id' => 'pt', 'name' => 'pt'),
                array('id' => 'px', 'name' => 'px'),
                array('id' => '%', 'name' => '%'),
            );
        }

        $data['user_layouts'] = array(
            array('id' => 'list', 'name' => xarML('List')),
            array('id' => 'cloud', 'name' => xarML('Cloud')),
        );
    }

    $data['subjects'] = xarMod::apiFunc('keywords', 'hooks', 'getsubjects');
    $data['hook_config'] = xarMod::guiFunc('keywords', 'hooks', 'modulemodifyconfig',
        array(
            'objectid' => $modname,
            'extrainfo' => array('module' => $modname, 'itemtype' => $itemtype)
        ));

    return $data;
}
?>