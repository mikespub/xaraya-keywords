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
 * Create an entry for a module item - hook for ('item','new','GUI')
 *
 * @param int $args['objectid'] ID of the object
 * @param array $args['extrainfo'] extra information
 * @return string hook output in HTML
 * @throws BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function keywords_admin_newhook($args)
{
    extract($args);

    if (empty($extrainfo))
        $extrainfo = array();

    // When called via hooks, the module name may be empty. Get it from current module.
    if (empty($extrainfo['module'])) {
        $modname = xarModGetName();
    } else {
        $modname = $extrainfo['module'];
    }

    $modid = xarMod::getRegId($modname);
    if (empty($modid)) {
        $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
        $vars = array('module', 'admin', 'newhook', 'keywords');
        throw new BadParameterException($vars, $msg);
    }

    if (!empty($extrainfo['itemtype']) && is_numeric($extrainfo['itemtype'])) {
        $itemtype = $extrainfo['itemtype'];
    } else {
        $itemtype = 0;
    }

    if (!empty($extrainfo['itemid']) && is_numeric($extrainfo['itemid'])) {
        $itemid = $extrainfo['itemid'];
    } else {
        $itemid = $objectid;
    }
    // new item won't have an id yet
    if (empty($itemid))
        $itemid = 0;

    // @todo: replace this with access prop
    if (!xarSecurityCheck('AddKeywords',0,'Item', "$modid:$itemtype:All")) return '';

    // get settings currently in force for this module/itemtype
    $settings = xarMod::apiFunc('keywords', 'hooks', 'getsettings',
        array(
            'module' => $modname,
            'itemtype' => $itemtype,
        ));

    // see if keywords were passed to hook call
    if (!empty($extrainfo['keywords'])) {
        $keywords = $extrainfo['keywords'];
    } else {
        // could be an item preview, try fetch from form input
        if (!xarVarFetch('keywords', 'isset',
            $keywords, null, XARVAR_DONT_SET)) return;
    }

    // we may have been given a string list
    if (!empty($keywords) && !is_array($keywords)) {
        $keywords = xarModAPIFunc('keywords','admin','separekeywords',
            array(
                'keywords' => $keywords,
            ));
    }

    // it's ok if there are no keywords
    if (empty($keywords))
        $keywords = array();

    // Retrieve the list of allowed delimiters
    $delimiters = xarModVars::get('keywords','delimiters');
    $delimiter = !empty($delimiters) ? $delimiters[0] : ',';
    
    $data = $settings;
    if (empty($settings['restrict_words'])) {
        // no restrictions, display expects a string
        // Use first delimiter to join words
        $data['keywords'] = !empty($keywords) ? implode($delimiter, $keywords) : '';
    } else {
        // get restricted list based on current settings
        $data['restricted_list'] = xarMod::apiFunc('keywords', 'words', 'getwords',
            array(
                'index_id' => $settings['index_id'],
            ));
        // return only keywords that are also in the restricted list
        $data['keywords'] = array_intersect($keywords, $data['restricted_list']);
        // see if managers are allowed to add to restricted list
        if (!empty($data['allow_manager_add'])) {
            // see if current user is a manager
            $data['is_manager'] = xarSecurityCheck('ManageKeywords',0,'Item', "$modid:$itemtype:$itemid");
            if (!empty($data['is_manager'])) {
                // see if keywords were passed to hook call
                if (!empty($extrainfo['restricted_extra'])) {
                    $toadd = $extrainfo['restricted_extra'];
                } else {
                    // could be an item preview, try fetch from form input
                    if (!xarVarFetch('restricted_extra', 'isset',
                        $toadd, array(), XARVAR_NOT_REQUIRED)) return;
                }
                // we may have been given a string list
                if (!empty($toadd) && !is_array($toadd)) {
                    $toadd = xarModAPIFunc('keywords','admin','separekeywords',
                        array(
                            'keywords' => $toadd,
                        ));
                }
                // display expects a string 
                $data['restricted_extra'] = !empty($toadd) ? implode($delimiter, array_unique($toadd)) : '';
            }
        }

    }
    $data['delimiters'] = $delimiters;

    return xarTpl::module('keywords', 'admin', 'newhook', $data);

}
?>