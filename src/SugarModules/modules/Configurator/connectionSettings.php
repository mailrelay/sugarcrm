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
    if (!isset($_POST['autosync_users'])) {
        $_POST['autosync_users'] = '0';
    }
    if (!isset($_POST['autosync_leads'])) {
        $_POST['autosync_leads'] = '0';
    }
    if (!isset($_POST['autosync_accounts'])) {
        $_POST['autosync_accounts'] = '0';
    }
    if (!isset($_POST['autosync_contacts'])) {
        $_POST['autosync_contacts'] = '0';
    }

    $mailrelaySettings = array();
    $mailrelaySettings['host'] = $_POST['host'];
    $mailrelaySettings['apikey'] = $_POST['apikey'];
    $mailrelaySettings['autosync_users'] = $_POST['autosync_users'];
    $mailrelaySettings['autosync_leads'] = $_POST['autosync_leads'];
    $mailrelaySettings['autosync_accounts'] = $_POST['autosync_accounts'];
    $mailrelaySettings['autosync_contacts'] = $_POST['autosync_contacts'];

    try {
        $mailrelayInstance->setApplicationInfo('SugarCRM', $sugar_version, MAILRELAY_PLUGIN_VERSION);
        $mailrelayInstance->setHost($_POST['host']);
        $mailrelayInstance->setApiKey($_POST['apikey']);
        $groups = $mailrelayInstance->getGroups();
        if ($_POST['autosync_users'] == '0' && $_POST['autosync_leads'] == '0' && $_POST['autosync_accounts'] == '0' && $_POST['autosync_contacts'] == '0') {
            $mailrelaySettings['groups_users'] = array();
            $mailrelaySettings['groups_leads'] = array();
            $mailrelaySettings['groups_accounts'] = array();
            $mailrelaySettings['groups_contacts'] = array();
            $admin->saveSetting('mailrelay', 'settings', serialize($mailrelaySettings));
            $mailrelayMessages[] = translate('LBL_MAILRELAY_SETTINGS_SAVED', 'Configurator');
        } else {
            if (($_POST['autosync_users'] == '1' && empty($_POST['groups_users'])) ||
                ($_POST['autosync_leads'] == '1' && empty($_POST['groups_leads'])) ||
                ($_POST['autosync_accounts'] == '1' && empty($_POST['groups_accounts'])) ||
                ($_POST['autosync_contacts'] == '1' && empty($_POST['groups_contacts']))) {
                if ($_POST['autosync_users'] == '1' && empty($_POST['groups_users'])) {
                    $mailrelayErrors[] = translate('LBL_MAILRELAY_GROUPS_USERS_REQUIRED', 'Configurator');
                }
                if ($_POST['autosync_leads'] == '1' && empty($_POST['groups_leads'])) {
                    $mailrelayErrors[] = translate('LBL_MAILRELAY_GROUPS_LEADS_REQUIRED', 'Configurator');
                }
                if ($_POST['autosync_accounts'] == '1' && empty($_POST['groups_accounts'])) {
                    $mailrelayErrors[] = translate('LBL_MAILRELAY_GROUPS_ACCOUNTS_REQUIRED', 'Configurator');
                }
                if ($_POST['autosync_contacts'] == '1' && empty($_POST['groups_contacts'])) {
                    $mailrelayErrors[] = translate('LBL_MAILRELAY_GROUPS_CONTACTS_REQUIRED', 'Configurator');
                }
            } else {
                if (!empty($_POST['groups_users'])) {
                    $mailrelaySettings['groups_users'] = array_map('intval', $_POST['groups_users']);
                } else {
                    $mailrelaySettings['groups_users'] = array();
                }
                if (!empty($_POST['groups_leads'])) {
                    $mailrelaySettings['groups_leads'] = array_map('intval', $_POST['groups_leads']);
                } else {
                    $mailrelaySettings['groups_leads'] = array();
                }
                if (!empty($_POST['groups_accounts'])) {
                    $mailrelaySettings['groups_accounts'] = array_map('intval', $_POST['groups_accounts']);
                } else {
                    $mailrelaySettings['groups_accounts'] = array();
                }
                if (!empty($_POST['groups_contacts'])) {
                    $mailrelaySettings['groups_contacts'] = array_map('intval', $_POST['groups_contacts']);
                } else {
                    $mailrelaySettings['groups_contacts'] = array();
                }
                $admin->saveSetting('mailrelay', 'settings', serialize($mailrelaySettings));
                $mailrelayMessages[] = translate('LBL_MAILRELAY_SETTINGS_SAVED', 'Configurator');
            }
        }
    } catch (Exception $exception) {
        $mailrelaySettings = array('host' => $_POST['host'],
                                   'apikey' => $_POST['apikey'],
                                   'autosync_users' => $_POST['autosync_users'],
                                   'autosync_leads' => $_POST['autosync_leads'],
                                   'autosync_accounts' => $_POST['autosync_accounts'],
                                   'autosync_contacts' => $_POST['autosync_contacts'],
                                   'groups_users' => $_POST['groups_users'],
                                   'groups_leads' => $_POST['groups_leads'],
                                   'groups_accounts' => $_POST['groups_accounts'],
                                   'groups_contacts' => $_POST['groups_contacts']);
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
                                   'autosync_users' => '0',
                                   'autosync_leads' => '0',
                                   'autosync_accounts' => '0',
                                   'autosync_contacts' => '0',
                                   'groups_users' => array(),
                                   'groups_leads' => array(),
                                   'groups_accounts' => array(),
                                   'groups_contacts' => array());
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

echo get_module_title(translate('LBL_MAILRELAY_SETTINGS', 'Configurator'), translate('LBL_MAILRELAY_SETTINGS', 'Configurator') . ': ', false);
echo $ss->fetch('custom/modules/Configurator/connectionSettings.tpl');
