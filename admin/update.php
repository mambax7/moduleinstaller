<?php

/**
 * See the enclosed file license.txt for licensing information.
 * If you did not receive this file, get it at https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @copyright   XOOPS Project (https://xoops.org)
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package     installer
 * @since       2.3.0
 * @author      Haruki Setoyama  <haruki@planewave.org>
 * @author      Kazumi Ono <webmaster@myweb.ne.jp>
 * @author      Skalpa Keo <skalpa@xoops.org>
 * @author      Taiwen Jiang <phppp@users.sourceforge.net>
 * @author      DuGris (aka L. JEN) <dugris@frxoops.org>
 **/
require_once __DIR__ . '/admin_header.php';
xoops_cp_header();
$xoopsOption['checkadmin'] = true;
$xoopsOption['hascommon']  = true;
require_once dirname(__DIR__) . '/include/common.inc.php';
require_once XOOPS_ROOT_PATH . '/modules/system/admin/modulesadmin/modulesadmin.php';
defined('XOOPS_INSTALL') || exit('XOOPS Installation wizard die');

if (!@require_once XOOPS_ROOT_PATH . "/language/{$wizard->language}/global.php") {
    require_once XOOPS_ROOT_PATH . '/language/english/global.php';
}
if (!@require_once XOOPS_ROOT_PATH . "/modules/system/language/{$wizard->language}/admin/modulesadmin.php") {
    require_once XOOPS_ROOT_PATH . '/modules/system/language/english/admin/modulesadmin.php';
}
require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
require_once XOOPS_ROOT_PATH . '/class/xoopslists.php';

//$xoTheme->addStylesheet( XOOPS_URL . "/modules/" . $xoopsModule->getVar("dirname") . "/assets/css/style.css" );

$pageHasForm = true;
$pageHasHelp = false;

/**
 * @param $dirname
 */

/*
function xoops_module_update($dirname) {

//http://localhost/257final/modules/system/admin.php?fct=modulesadmin&op=update&module=mypoints

$url = XOOPS_URL . "/modules/system/admin.php?fct=modulesadmin&op=update&module=".$dirname;
//$ch = curl_init();
//curl_setopt($ch, CURLOPT_URL,$url);
//curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
//curl_setopt($ch, CURLOPT_TIMEOUT, 10);
//$result = curl_exec($ch);
//curl_close($ch);


$crl = curl_init();
curl_setopt($crl, CURLOPT_URL, $url);
@curl_setopt($crl, CURLOPT_HEADER, 0);
@curl_setopt($crl, CURLOPT_NOBODY, 1);
@curl_setopt($crl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
$res = curl_exec($crl);
curl_close($crl);
return $res;

}

*/

if ('POST' === $_SERVER['REQUEST_METHOD']) {
    require_once XOOPS_ROOT_PATH . '/class/xoopsblock.php';
    require_once XOOPS_ROOT_PATH . '/kernel/module.php';
    require_once XOOPS_ROOT_PATH . '/include/cp_functions.php';
    require_once XOOPS_ROOT_PATH . '/include/version.php';
    //    require_once  dirname(__DIR__) . '/include/modulesadmin.php';

    $configHandler = xoops_getHandler('config');
    $xoopsConfig   = $configHandler->getConfigsByCat(XOOPS_CONF);

    $msgs = [];
    foreach ($_REQUEST['modules'] as $dirname => $updateModule) {
        if ($updateModule) {
            $msgs[] = xoops_module_update($dirname);
        }
    }

    $pageHasForm = false;

    if (count($msgs) > 0) {
        $content = "<div class='x2-note successMsg'>" . UPDATED_MODULES . "</div><ul class='log'>";
        foreach ($msgs as $msg) {
            $content .= "<dt>{$msg}</dt>";
        }
        $content .= '</ul>';
    } else {
        $content = "<div class='x2-note confirmMsg'>" . NO_INSTALLED_MODULES . '</div>';
    }

    // Flush cache files for cpanel GUIs
    xoops_load('cpanel', 'system');
    XoopsSystemCpanel::flush();

    //Set active modules in cache folder
    xoops_setActiveModules();
} else {
    if (!isset($GLOBALS['xoopsConfig']['language'])) {
        $GLOBALS['xoopsConfig']['language'] = $_COOKIE['xo_install_lang'];
    }

    // Get installed modules
    /** @var \XoopsModuleHandler $moduleHandler */
    $moduleHandler  = xoops_getHandler('module');
    $installed_mods = $moduleHandler->getObjects();
    $listed_mods    = [];
    foreach ($installed_mods as $module) {
        $listed_mods[] = $module->getVar('dirname');
    }

    require_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
    $dirlist  = \XoopsLists::getModulesList();
    $toinstal = 0;

    $javascript = '';
    $content    = "<ul class='log'><li>";
    $content    .= "<table class='module'>\n";
    //remove System module and itself from the list of modules that can be uninstalled
    //    $dirlist = array_diff($dirlist, array('system', 'moduleinstaller'));
    foreach ($dirlist as $file) {
        clearstatcache();
        if (in_array($file, $listed_mods)) {
            $value = 0;
            $style = '';
            if (isset($wizard->configs['modules']) && in_array($file, $wizard->configs['modules'])) {
                $value = 1;
                $style = " style='background-color:#E6EFC2;'";
            }

            $file   = trim($file);
            $module = $moduleHandler->create();
            if (!$module->loadInfo($file, false)) {
                continue;
            }

            $form     = new \XoopsThemeForm('', 'modules', 'index.php', 'post', true);
            $moduleYN = new \XoopsFormRadioYN('', 'modules[' . $module->getInfo('dirname') . ']', $value, _YES, _NO);
            $moduleYN->setExtra("onclick='selectModule(\"" . $file . "\", this)'");
            $form->addElement($moduleYN);

            $content .= "<tr id='" . $file . "'" . $style . ">\n";
            $content .= "    <td class='img' ><img src='" . XOOPS_URL . '/modules/' . $module->getInfo('dirname') . '/' . $module->getInfo('image') . "' alt='" . $module->getInfo('name') . "'></td>\n";

            $moduleHandlerInDB = xoops_getHandler('module');
            $moduleInDB        = $moduleHandler->getByDirname($module->getInfo('dirname'));
            // Save current version for use in the update function
            $prevVersion = round($moduleInDB->getVar('version') / 100, 2);

            $content = round($module->getInfo('version'), 2) != $prevVersion ? $content . "    <td ><span style='color: #FF0000; font-weight: bold;'>" : $content . '    <td><span>';
            $content .= '        ' . $module->getInfo('name') . '&nbsp;' . number_format(round($module->getInfo('version'), 2), 2) . '&nbsp;' . $module->getInfo('module_status') . '&nbsp;(folder: /' . $module->getInfo('dirname') . ')';
            $content .= '        <br>' . $module->getInfo('description');
            $content .= "    </span></td>\n";
            $content .= "    <td class='yesno'>";
            $content .= $moduleYN->render();
            $content .= "    </td></tr>\n";
            ++$toinstal;
        }
    }
    $content .= '</table>';
    $content .= "</li></ul><script type='text/javascript'>" . $javascript . '</script>';
    if (0 == $toinstal) {
        $pageHasForm = false;
        $content     = "<div class='x2-note confirmMsg'>" . NO_MODULES_FOUND . '</div>';
    }
}
$adminObject = \Xmf\Module\Admin::getInstance();
$adminObject->displayNavigation(basename(__FILE__));

$adminObject->addItemButton(_AM_INSTALLER_SELECT_ALL, 'javascript:selectAll();', 'button_ok');

$adminObject->addItemButton(_AM_INSTALLER_SELECT_NONE, 'javascript:unselectAll();', 'prune');

$adminObject->displayButton('left', '');

require_once dirname(__DIR__) . '/include/install_tpl.php';
require_once __DIR__ . '/admin_footer.php';
