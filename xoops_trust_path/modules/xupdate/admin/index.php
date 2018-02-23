<?php
/**
 * @file
 * @brief The page controller in the directory
 * @package xupdate
 * @version $Id$
**/

if (!defined('XCUBE_CORE_USER_PASS_LEN_FIXED') && get_class($this) !== 'Xupdate_Admin_UserPassColumnLenFixAction') {
    header('Location: ' . XOOPS_URL . '/modules/xupdate/admin/index.php?action=UserPassColumnLenFix&xoops_redirect='.rawurlencode($_SERVER['REQUEST_URI']));
    exit();
}

$root =& XCube_Root::getSingleton();
$root->mContext->mModule->setAdminMode(true);


$root->mController->executeHeader();
$root->mController->execute();


$xoopsLogger=&$root->mController->getLogger();
$xoopsLogger->stopTime();
$root->mController->executeView();
