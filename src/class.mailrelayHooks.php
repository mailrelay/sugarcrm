<?php

define('MAILRELAY_PLUGIN_VERSION', '1.0');

class MailrelayHooks {

    public function afterSaveUser($bean, $event, $arguments) {
        if ($bean->email1 != '') {
            require_once 'modules/Administration/Administration.php';
            $admin = new Administration();
            $admin->retrieveSettings();
            if (isset($admin->settings['mailrelay_settings']) && $admin->settings['mailrelay_settings'] != '') {
                $mailrelaySettings = unserialize($this->unhtmlentities($admin->settings['mailrelay_settings']));
                if ($mailrelaySettings['autosync'] == '1') {
                    global $sugar_version;

                    require_once 'custom/include/class.mailrelay.php';
                    $mailrelayInstance = new Mailrelay();
                    $mailrelayInstance->setApplicationInfo('SugarCRM', $sugar_version, MAILRELAY_PLUGIN_VERSION);
                    $mailrelayInstance->setHost($mailrelaySettings['host']);
                    $mailrelayInstance->setApiKey($mailrelaySettings['apikey']);
                    try {
                        $mailrelayInstance->syncUserToGroups($bean->email1, trim($bean->first_name . ' ' . $bean->last_name), $mailrelaySettings['groups']);
                    } catch (Exception $exception) {
                        error_log('Error to sync "' . $bean->email1 . '", ' . $exception->getMessage());
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
