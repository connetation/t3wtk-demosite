<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Conn: t3kit Demo Site',
    'description' => 'Installs a complete demo page tree, site configuration and content built with conn/t3kit-base and conn/t3kit-elements',
    'category' => 'distribution',
    'author' => 'Connetation Web Engineering',
    'author_email' => 'i.sapina@connetation.at',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.99.99',
            'form' => '14.0.0-14.99.99',
            'conn_t3kit_base' => '2.0.0-0.0.0',
            'conn_t3kit_elements' => '2.0.0-0.0.0',
            'conn_t3kit_sitepackage' => '2.0.0-0.0.0',
        ],
    ],
];
