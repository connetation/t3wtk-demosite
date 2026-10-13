<?php

/*
 * This file is part of the Connetation TYPO3 Web Toolkit - T3WTK.
 *
 * Copyright (c) Connetation Web Engineering GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'Connetation TYPO3 Web Toolkit: Demo Site',
    'description' => 'Demo site for the Connetation TYPO3 Web Toolkit (T3WTK). Automatically creates a complete page tree with site configuration, sample content and six color themes. Ideal for evaluation and as a reference.',
    'category' => 'distribution',
    'author' => 'Connetation Web Engineering GmbH',
    'author_email' => 'office@connetation.at',
    'state' => 'stable',
    'version' => '2.0.3',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.99.99',
            'form' => '14.0.0-14.99.99',
            'conn_t3wtk_base' => '3.1.4-0.0.0',
            'conn_t3wtk_elements' => '3.1.4-0.0.0',
        ],
        'suggests' => [
            'conn_t3wtk_sitepackage' => '3.0.0-0.0.0',
        ],
    ],
];
