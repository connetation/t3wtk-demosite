<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Connetation TYPO3 Web Toolkit: Demo Site',
    'description' => 'Installs a complete demo page tree, site configuration and content built with connetation/t3wtk-base and connetation/t3wtk-elements',
    'category' => 'distribution',
    'author' => 'Connetation Web Engineering',
    'author_email' => 'i.sapina@connetation.at',
    'state' => 'stable',
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.99.99',
            'form' => '14.0.0-14.99.99',
            'conn_t3wtk_base' => '3.0.8-0.0.0',
            'conn_t3wtk_elements' => '3.0.8-0.0.0',
        ],
        'suggests' => [
            'conn_t3wtk_sitepackage' => '3.0.0-0.0.0',
        ],
    ],
];
