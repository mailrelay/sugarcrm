<?php

$manifest = array(
    'name' => 'Mailrelay',
    'description' => 'Easily sync your SugarCRM users with Mailrelay.',
    'version' => '1.0',
    'author' => 'CPC',
    'readme' => 'README',
    'acceptable_sugar_flavors' => array('CE', 'PRO', 'ENT'),
    'acceptable_sugar_versions' => array(
        'exact_matches' => array(),
        'regex_matches' => array('6\.5\.\d+$'),
    ),
    'icon' => '',
    'is_uninstallable' => true,
    'published_date' => '2014-02-10',
    'type' => 'module'
);

$installdefs['id'] = 'Mailrelay_' . $manifest['version'];

$installdefs['administration'] = array(
    array(
        'from' => '<basepath>/SugarModules/modules/Administration/mailrelay_options.php',
    ),
);

$installdefs['copy'] = array(
    array(
        'from' => '<basepath>/class.mailrelay.php',
        'to' => 'custom/include/class.mailrelay.php',
    ),
    array(
        'from' => '<basepath>/class.mailrelayHooks.php',
        'to' => 'custom/include/class.mailrelayHooks.php',
    ),
    array(
        'from' => '<basepath>/SugarModules/modules/Configurator/connectionSettings.php',
        'to' => 'custom/modules/Configurator/connectionSettings.php',
    ),
    array(
        'from' => '<basepath>/SugarModules/modules/Configurator/connectionSettings.tpl',
        'to' => 'custom/modules/Configurator/connectionSettings.tpl',
    )
);

$installdefs['language'] = array(
    array(
        'from' => '<basepath>/SugarModules/modules/Administration/Language/en_us.lang.php',
        'to_module' => 'Administration',
        'language' => 'en_us',
    ),
    array(
        'from' => '<basepath>/SugarModules/modules/Configurator/Language/en_us.lang.php',
        'to_module' => 'Configurator',
        'language' => 'en_us',
    )
);

$installdefs['logic_hooks'] = array(
    array(
        'module' => 'Users',
        'order' => 1,
        'hook' => 'after_save',
        'description' => 'Mailrelay after_save users hook',
        'file' => 'custom/include/class.mailrelayHooks.php',
        'class' => 'MailrelayHooks',
        'function' => 'afterSaveUser',
    ),
);

$installdefs['post_uninstall'] = array(
    '<basepath>/scripts/post_uninstall.php',
);