<?php

function unhtmlentities($string) {
    $trans_tbl = get_html_translation_table(HTML_ENTITIES);
    foreach ($trans_tbl as $k => $v) {
        $ttr[$v] = utf8_encode($k);
    }
    return strtr($string, $ttr);
}

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

if (!is_admin($current_user)) {
    sugar_die('Admin Only Section.');
}

define('MAILRELAY_PLUGIN_VERSION', '1.0');

$groups = array();
$mailrelaySettings = array();
$mailrelayMessages = array();
$mailrelayErrors = array();

require_once 'modules/Administration/Administration.php';
$admin = new Administration();
$admin->retrieveSettings();

require_once 'custom/include/class.mailrelay.php';
$mailrelayInstance = new Mailrelay();

if (!empty($_POST)) {
    if (!isset($_POST['autosync'])) {
        $_POST['autosync'] = '0';
    }

    $mailrelaySettings = array();
    $mailrelaySettings['host'] = $_POST['host'];
    $mailrelaySettings['apikey'] = $_POST['apikey'];
    $mailrelaySettings['autosync'] = $_POST['autosync'];

    try {
        $mailrelayInstance->setApplicationInfo('SugarCRM', $sugar_version, MAILRELAY_PLUGIN_VERSION);
        $mailrelayInstance->setHost($_POST['host']);
        $mailrelayInstance->setApiKey($_POST['apikey']);
        $groups = $mailrelayInstance->getGroups();
        if ($_POST['autosync'] == '0') {
            $mailrelaySettings['groups'] = array();
            $admin->saveSetting('mailrelay', 'settings', serialize($mailrelaySettings));
            $mailrelayMessages[] = translate('LBL_MAILRELAY_SETTINGS_SAVED', 'Configurator');
        } else {
            if (empty($_POST['groups'])) {
                $mailrelayErrors[] = translate('LBL_MAILRELAY_GROUPS_REQUIRED', 'Configurator');
            } else {
                $mailrelaySettings['groups'] = array_map('intval', $_POST['groups']);
                $admin->saveSetting('mailrelay', 'settings', serialize($mailrelaySettings));
                $mailrelayMessages[] = translate('LBL_MAILRELAY_SETTINGS_SAVED', 'Configurator');
            }
        }
    } catch (Exception $exception) {
        $mailrelaySettings = array('host' => $_POST['host'],
                                   'apikey' => $_POST['apikey'],
                                   'autosync' => $_POST['autosync'],
                                   'groups' => $_POST['groups']);
        $mailrelayErrors[] = $exception->getMessage();
        $mailrelayErrors[] = translate('LBL_MAILRELAY_SETTINGS_NOT_SAVED', 'Configurator');
    }
} else {
    if (isset($admin->settings['mailrelay_settings']) && $admin->settings['mailrelay_settings'] != '') {
        $mailrelaySettings = unserialize(unhtmlentities($admin->settings['mailrelay_settings']));
        try {
            $mailrelayInstance->setApplicationInfo('SugarCRM', $sugar_version, MAILRELAY_PLUGIN_VERSION);
            $mailrelayInstance->setHost($mailrelaySettings['host']);
            $mailrelayInstance->setApiKey($mailrelaySettings['apikey']);
            $groups = $mailrelayInstance->getGroups();
        } catch (Exception $exception) {
            $mailrelayErrors[] = $exception->getMessage();
            $mailrelayErrors[] = translate('LBL_MAILRELAY_SETTINGS_NOT_SAVED', 'Configurator');
        }
    } else {
        $mailrelaySettings = array('host' => '',
                                   'apikey' => '',
                                   'autosync' => '0',
                                   'groups' => array());
    }
}

require_once 'include/Sugar_Smarty.php';

$ss = new Sugar_Smarty();
$ss->assign('MOD', $GLOBALS['mod_strings']);
$ss->assign('APP', $GLOBALS['app_strings']);
$ss->assign('settings', $mailrelaySettings);
$ss->assign('groups', $groups);
$ss->assign('messages', $mailrelayMessages);
$ss->assign('errors', $mailrelayErrors);

echo get_module_title(translate('LBL_MAILRELAY_SETTINGS', 'Configurator'), translate('LBL_MAILRELAY_SETTINGS', 'Configurator') . ': ', true);
echo $ss->fetch('custom/modules/Configurator/connectionSettings.tpl');
