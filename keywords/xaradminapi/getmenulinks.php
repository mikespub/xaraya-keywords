<?php

/**
 * utility function pass individual menu items to the main menu
 *
 * @author mikespub
 * @returns array
 * @return array containing the menulinks for the main menu items.
 */
function keywords_adminapi_getmenulinks()
{
    $menulinks = array();
    // Security Check
    if (xarSecurityCheck('AdminKeywords')) {
        $menulinks[] = Array('url'   => xarModURL('keywords',
                                                  'admin',
                                                  'view'),
                              'title' => xarML('Overview of the keyword assignments'),
                              'label' => xarML('View Keywords'));
        $menulinks[] = Array('url'   => xarModURL('keywords',
                                                  'admin',
                                                  'modifyconfig'),
                              'title' => xarML('Modify the keywords configuration'),
                              'label' => xarML('Modify Config'));
    }

    return $menulinks;
}
?>
