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
        // Init variables
        global $db;

        // Get configuration
        $mailrelaySettings = unserialize(unhtmlentities($admin->settings['mailrelay_settings']));

        // Setup Mailrelay class
        $mailrelayInstance->setApplicationInfo('SugarCRM', $sugar_version, MAILRELAY_PLUGIN_VERSION);
        $mailrelayInstance->setHost($mailrelaySettings['host']);
        $mailrelayInstance->setApiKey($mailrelaySettings['apikey']);

        // Sync Users
        if (!empty($_POST['groups_users'])) {
            $users = array();
            $dataset = $db->query('SELECT `id`, `first_name`, `last_name` FROM `users` WHERE `deleted` = 0');
            while ($row = $db->fetchByAssoc($dataset)) {
                $user = BeanFactory::getBean('Users', $row['id']);
                $email = $user->emailAddress->getPrimaryAddress($user);
                if ($email != '') {
                    $users[$email] = trim($row['first_name'] . ' ' . $row['last_name']);
                }
            }

            $mailrelaySettings['groups_users'] = array_map('intval', $_POST['groups_users']);

            $results = $mailrelayInstance->syncUsersToGroups($users, $mailrelaySettings['groups_users']);
            $message = translate('LBL_MAILRELAY_USERS_ADDED', 'Configurator') . ': ' . $results['added'] . ', ';
            $message .= translate('LBL_MAILRELAY_USERS_UPDATED', 'Configurator') . ': ' . $results['updated'] . ', ';
            $message .= translate('LBL_MAILRELAY_USERS_FAILED', 'Configurator') . ': ' . $results['failed'];
            $mailrelayMessages[] = $message;
        }

        // Sync Leads
        if (!empty($_POST['groups_leads'])) {
            $leads = array();
            $dataset = $db->query('SELECT `id` FROM `leads` WHERE `deleted` = 0');
            while ($row = $db->fetchByAssoc($dataset)) {
                $lead = BeanFactory::getBean('Leads', $row['id']);
                $email = $lead->emailAddress->getPrimaryAddress($lead);
                if ($email != '') {
                    $leads[$email] = $lead->full_name;
                }
            }

            $mailrelaySettings['groups_leads'] = array_map('intval', $_POST['groups_leads']);

            $results = $mailrelayInstance->syncUsersToGroups($leads, $mailrelaySettings['groups_leads']);
            $message = translate('LBL_MAILRELAY_LEADS_ADDED', 'Configurator') . ': ' . $results['added'] . ', ';
            $message .= translate('LBL_MAILRELAY_LEADS_UPDATED', 'Configurator') . ': ' . $results['updated'] . ', ';
            $message .= translate('LBL_MAILRELAY_LEADS_FAILED', 'Configurator') . ': ' . $results['failed'];
            $mailrelayMessages[] = $message;
        }

        // Sync Accounts
        if (!empty($_POST['groups_accounts'])) {
            $accounts = array();
            $dataset = $db->query('SELECT `id`, `name` FROM `accounts` WHERE `deleted` = 0');
            while ($row = $db->fetchByAssoc($dataset)) {
                $account = BeanFactory::getBean('Accounts', $row['id']);
                $email = $account->emailAddress->getPrimaryAddress($account);
                if ($email != '') {
                    $accounts[$email] = $account->name;
                }
            }

            $mailrelaySettings['groups_accounts'] = array_map('intval', $_POST['groups_accounts']);

            $results = $mailrelayInstance->syncUsersToGroups($accounts, $mailrelaySettings['groups_accounts']);
            $message = translate('LBL_MAILRELAY_ACCOUNTS_ADDED', 'Configurator') . ': ' . $results['added'] . ', ';
            $message .= translate('LBL_MAILRELAY_ACCOUNTS_UPDATED', 'Configurator') . ': ' . $results['updated'] . ', ';
            $message .= translate('LBL_MAILRELAY_ACCOUNTS_FAILED', 'Configurator') . ': ' . $results['failed'];
            $mailrelayMessages[] = $message;
        }

        // Sync Contacts
        if (!empty($_POST['groups_contacts'])) {
            $contacts = array();
            $dataset = $db->query('SELECT `id`, `first_name`, `last_name` FROM `contacts` WHERE `deleted` = 0');
            while ($row = $db->fetchByAssoc($dataset)) {
                $contact = BeanFactory::getBean('Contacts', $row['id']);
                $email = $contact->emailAddress->getPrimaryAddress($contact);
                if ($email != '') {
                    $contacts[$email] = trim($row['first_name'] . ' ' . $row['last_name']);
                }
            }

            $mailrelaySettings['groups_contacts'] = array_map('intval', $_POST['groups_contacts']);

            $results = $mailrelayInstance->syncUsersToGroups($contacts, $mailrelaySettings['groups_contacts']);
            $message = translate('LBL_MAILRELAY_CONTACTS_ADDED', 'Configurator') . ': ' . $results['added'] . ', ';
            $message .= translate('LBL_MAILRELAY_CONTACTS_UPDATED', 'Configurator') . ': ' . $results['updated'] . ', ';
            $message .= translate('LBL_MAILRELAY_CONTACTS_FAILED', 'Configurator') . ': ' . $results['failed'];
            $mailrelayMessages[] = $message;
        }

        // Get groups to populate the listboxes
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

echo get_module_title(translate('LBL_MAILRELAY_SYNC', 'Configurator'), translate('LBL_MAILRELAY_SYNC', 'Configurator') . ': ', false);
echo $ss->fetch('custom/modules/Configurator/syncUsers.tpl');
