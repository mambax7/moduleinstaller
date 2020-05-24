<?php

//use XoopsModules\Moduleinstaller;

include dirname(__DIR__) . '/preloads/autoloader.php';

$moduleDirName = basename(dirname(__DIR__));
$moduleDirNameUpper = mb_strtoupper($moduleDirName);
/** @var \XoopsModules\Moduleinstaller\Helper $helper */
$helper = \XoopsModules\Moduleinstaller\Helper::getInstance();
$helper->loadLanguage('common');
$helper->loadLanguage('feedback');

$pathIcon32 = \Xmf\Module\Admin::menuIconPath('');
if (is_object($helper->getModule())) {
    $pathModIcon32 = $helper->url($helper->getModule()->getInfo('modicons32'));
}

$adminmenu[] = [
    'title' => _MI_INSTALLER_MENU_00,
    'link'  => 'admin/index.php',
    'icon'  => $pathIcon32 . '/home.png',
];

$adminmenu[] = [
    'title' => _MI_INSTALLER_MENU_01,
    'link'  => 'admin/install.php',
    'icon'  => $pathIcon32 . '/add.png',
];

$adminmenu[] = [
    'title' => _MI_INSTALLER_MENU_03,
    'link'  => 'admin/update.php',
    'icon'  => $pathIcon32 . '/update.png',
];

$adminmenu[] = [
    'title' => _MI_INSTALLER_MENU_02,
    'link'  => 'admin/uninstall.php',
    'icon'  => $pathIcon32 . '/delete.png',
];

$adminmenu[] = [
    'title' => _MI_INSTALLER_ADMIN_ABOUT,
    'link'  => 'admin/about.php',
    'icon'  => $pathIcon32 . '/about.png',
];
