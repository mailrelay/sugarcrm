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
    try {
        $users = array();

        global $db;
        $dataset = $db->query('SELECT `id`, `first_name`, `last_name` FROM `users` WHERE `deleted` = 0');
        while ($row = $db->fetchByAssoc($dataset)) {
            $user = BeanFactory::getBean('Users', $row['id']);
            $email = $user->emailAddress->getPrimaryAddress($user);
            if ($email != '') {
                $users[$email] = trim($row['first_name'] . ' ' . $row['last_name']);
            }
        }

        $mailrelaySettings = unserialize(unhtmlentities($admin->settings['mailrelay_settings']));
        $mailrelaySettings['groups'] = array_map('intval', $_POST['groups']);
        $mailrelayInstance->setApplicationInfo('SugarCRM', $sugar_version, MAILRELAY_PLUGIN_VERSION);
        $mailrelayInstance->setHost($mailrelaySettings['host']);
        $mailrelayInstance->setApiKey($mailrelaySettings['apikey']);
        $results = $mailrelayInstance->syncUsersToGroups($users, $mailrelaySettings['groups']);
        $mailrelayMessages[] = translate('LBL_MAILRELAY_ADDED', 'Configurator') . ': ' . $results['added'];
        $mailrelayMessages[] = translate('LBL_MAILRELAY_UPDATED', 'Configurator') . ': ' . $results['updated'];
        $mailrelayMessages[] = translate('LBL_MAILRELAY_FAILED', 'Configurator') . ': ' . $results['failed'];
        $groups = $mailrelayInstance->getGroups();
    } catch (Exception $exception) {
        $mailrelayErrors[] = $exception->getMessage();
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
        }
    } else {
        $mailrelayErrors[] = translate('LBL_MAILRELAY_SETTINGS_REQUIRED', 'Configurator');
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

echo get_module_title(translate('LBL_MAILRELAY_SYNC', 'Configurator'), translate('LBL_MAILRELAY_SYNC', 'Configurator') . ': ', true);
echo $ss->fetch('custom/modules/Configurator/syncUsers.tpl');
