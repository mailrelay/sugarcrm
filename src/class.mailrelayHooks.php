<?php

define('MAILRELAY_PLUGIN_VERSION', '1.0');

class MailrelayHooks {

    public function afterSaveUser($user, $event, $arguments) {
        $email = $user->email1;
        if ($email != '') {
            require_once 'modules/Administration/Administration.php';
            $admin = new Administration();
            $admin->retrieveSettings();
            if (isset($admin->settings['mailrelay_settings']) && $admin->settings['mailrelay_settings'] != '') {
                $mailrelaySettings = unserialize($this->unhtmlentities($admin->settings['mailrelay_settings']));
                if ($mailrelaySettings['autosync_users'] == '1') {
                    global $sugar_version;

                    require_once 'custom/include/class.mailrelay.php';
                    $mailrelayInstance = new Mailrelay();
                    $mailrelayInstance->setApplicationInfo('SugarCRM', $sugar_version, MAILRELAY_PLUGIN_VERSION);
                    $mailrelayInstance->setHost($mailrelaySettings['host']);
                    $mailrelayInstance->setApiKey($mailrelaySettings['apikey']);
                    try {
                        $mailrelayInstance->syncUserToGroups($email, trim($user->first_name . ' ' . $user->last_name), $mailrelaySettings['groups_users']);
                    } catch (Exception $exception) {
                        error_log('Error to sync "' . $email . '", ' . $exception->getMessage());
                    }
                }
            }
        }
    }

    public function afterSaveLead($lead, $event, $arguments) {
        $email = $lead->email1;
        if ($email != '') {
            require_once 'modules/Administration/Administration.php';
            $admin = new Administration();
            $admin->retrieveSettings();
            if (isset($admin->settings['mailrelay_settings']) && $admin->settings['mailrelay_settings'] != '') {
                $mailrelaySettings = unserialize($this->unhtmlentities($admin->settings['mailrelay_settings']));
                if ($mailrelaySettings['autosync_leads'] == '1') {
                    global $sugar_version;

                    require_once 'custom/include/class.mailrelay.php';
                    $mailrelayInstance = new Mailrelay();
                    $mailrelayInstance->setApplicationInfo('SugarCRM', $sugar_version, MAILRELAY_PLUGIN_VERSION);
                    $mailrelayInstance->setHost($mailrelaySettings['host']);
                    $mailrelayInstance->setApiKey($mailrelaySettings['apikey']);
                    try {
                        $mailrelayInstance->syncUserToGroups($email, $lead->full_name, $mailrelaySettings['groups_leads']);
                    } catch (Exception $exception) {
                        error_log('Error to sync "' . $email . '", ' . $exception->getMessage());
                    }
                }
            }
        }
    }

    public function afterSaveAccount($account, $event, $arguments) {
        $email = $account->email1;
        if ($email != '') {
            require_once 'modules/Administration/Administration.php';
            $admin = new Administration();
            $admin->retrieveSettings();
            if (isset($admin->settings['mailrelay_settings']) && $admin->settings['mailrelay_settings'] != '') {
                $mailrelaySettings = unserialize($this->unhtmlentities($admin->settings['mailrelay_settings']));
                if ($mailrelaySettings['autosync_accounts'] == '1') {
                    global $sugar_version;

                    require_once 'custom/include/class.mailrelay.php';
                    $mailrelayInstance = new Mailrelay();
                    $mailrelayInstance->setApplicationInfo('SugarCRM', $sugar_version, MAILRELAY_PLUGIN_VERSION);
                    $mailrelayInstance->setHost($mailrelaySettings['host']);
                    $mailrelayInstance->setApiKey($mailrelaySettings['apikey']);
                    try {
                        $mailrelayInstance->syncUserToGroups($email, $account->name, $mailrelaySettings['groups_accounts']);
                    } catch (Exception $exception) {
                        error_log('Error to sync "' . $email . '", ' . $exception->getMessage());
                    }
                }
            }
        }
    }

    public function afterSaveContact($contact, $event, $arguments) {
        $email = $contact->email1;
        if ($email != '') {
            require_once 'modules/Administration/Administration.php';
            $admin = new Administration();
            $admin->retrieveSettings();
            if (isset($admin->settings['mailrelay_settings']) && $admin->settings['mailrelay_settings'] != '') {
                $mailrelaySettings = unserialize($this->unhtmlentities($admin->settings['mailrelay_settings']));
                if ($mailrelaySettings['autosync_contacts'] == '1') {
                    global $sugar_version;

                    require_once 'custom/include/class.mailrelay.php';
                    $mailrelayInstance = new Mailrelay();
                    $mailrelayInstance->setApplicationInfo('SugarCRM', $sugar_version, MAILRELAY_PLUGIN_VERSION);
                    $mailrelayInstance->setHost($mailrelaySettings['host']);
                    $mailrelayInstance->setApiKey($mailrelaySettings['apikey']);
                    try {
                        $mailrelayInstance->syncUserToGroups($email, trim($contact->first_name . ' ' . $contact->last_name), $mailrelaySettings['groups_contacts']);
                    } catch (Exception $exception) {
                        error_log('Error to sync "' . $email . '", ' . $exception->getMessage());
                    }
                }
            }
        }
    }

    private function unhtmlentities($string) {
        $trans_tbl = get_html_translation_table(HTML_ENTITIES);
        foreach ($trans_tbl as $k => $v) {
            $ttr[$v] = utf8_encode($k);
        }
        return strtr($string, $ttr);
    }

}
