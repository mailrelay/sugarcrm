<?php

$admin_option_defs = array(
	'Administration' => array(
		'connectionSettings' => array(
			'Administration',
			'LBL_MAILRELAY_SETTINGS',
			'LBL_MAILRELAY_SETTINGS_DESC',
			'index.php?module=Configurator&action=connectionSettings',
		),
		'syncUsers' => array(
			'Users',
			'LBL_MAILRELAY_SYNC',
			'LBL_MAILRELAY_SYNC_DESC',
			'index.php?module=Configurator&action=syncUsers',
		),
	),
);

$admin_group_header[] = array('LBL_MAILRELAY', '', false, $admin_option_defs, 'LBL_MAILRELAY_DESC');
