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
 * display keywords entry for a module item - hook for ('item','display','GUI')
 *
 * @param $args['objectid'] ID of the object
 * @param $args['extrainfo'] extra information
 * @return mixed Array with information for the template that is called.
 * @throws BAD_PARAM, NO_PERMISSION
 */
function keywords_user_displayhook($args)
{
    extract($args);

    if (empty($extrainfo))
        $extrainfo = array();

    if (!isset($objectid) || !is_numeric($objectid)) {
        $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
        $vars = array('objectid', 'user', 'displayhook', 'keywords');
        throw new BadParameterException($vars, $msg);
    }


    // When called via hooks, the module name may be empty. Get it from current module.
    if (empty($extrainfo['module'])) {
        $modname = xarModGetName();
    } else {
        $modname = $extrainfo['module'];
    }

    $modid = xarMod::getRegId($modname);
    if (empty($modid)) {
        $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
        $vars = array('module', 'admin', 'updatehook', 'keywords');
        throw new BadParameterException($vars, $msg);
    }

    if (!empty($extrainfo['itemtype']) && is_numeric($extrainfo['itemtype'])) {
        $itemtype = $extrainfo['itemtype'];
    } else {
        $itemtype = 0;
    }

    if (!empty($extrainfo['itemid'])) {
        $itemid = $extrainfo['itemid'];
    } else {
        $itemid = $objectid;
    }

    // @todo: replace this with access prop
    if (!xarSecurityCheck('ReadKeywords',0)) return '';

    // get settings currently in force for this module/itemtype
    $settings = xarMod::apiFunc('keywords', 'hooks', 'getsettings',
        array(
            'module' => $modname,
            'itemtype' => $itemtype,
        ));

    // get the index_id for this module/itemtype/item
    $index_id = xarMod::apiFunc('keywords', 'index', 'getid',
        array(
            'module' => $modname,
            'itemtype' => $itemtype,
            'itemid' => $itemid,
        ));

    // get the keywords associated with this item
    $keywords = xarMod::apiFunc('keywords', 'words', 'getwords',
        array(
            'index_id' => $index_id,
        ));


    // config may have changed since the keywords were added
    if (!empty($settings['restrict_words'])) {
        $restricted_list = xarMod::apiFunc('keywords', 'words', 'getwords',
            array(
                'index_id' => $settings['index_id'],
            ));
        // show only keywords that are also in the restricted list
        $keywords = array_intersect($keywords, $restricted_list);
    }

    // @fixme: this is unreliable, hooks are not exclusive to current main module during a request
    $keys = implode(',',$keywords);
    xarVarSetCached('Blocks.keywords','keys',$keys);

    $data = $settings;
    $data['keywords'] = $keywords;

    $data['showlabel'] = isset($extrainfo['showlabel']) ? $extrainfo['showlabel'] : true;

    $tpltype = isset($extrainfo['tpltype']) ? $extrainfo['tpltype'] : 'user';

    return xarTpl::module('keywords', $tpltype, 'displayhook', $data);
}
?>