<?php

use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_ds_faq_question'] = array
(
	// Config
	'config' => array
	(
		'dataContainer' => DC_Table::class,
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'pid,published' => 'index',
			)
		),
        'label' => 'Questions',
        'notCopyable' => true
	),

	// List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => DataContainer::MODE_TREE,
            'rootPaste'               => true,
            'showRootTrails'          => true,
            'icon'                    => 'pagemounts.svg',
            'panelLayout'             => 'filter;search',
            'defaultSearchField'      => 'question'
        ),
        'label' => array
        (
            'fields'                  => array('question'),
            'format'                  => '%s',
        ),
        'operations' => array(
            'generateQuestions' => array(
                'href' => 'key=generateQuestions',
                'icon' => '',
            ),
            'generateAnswers' => array(
                'href' => 'key=generateAnswers',
                'icon' => '',
            )
        )
    ),

	// Palettes
	'palettes' => array
	(
        '__selector__' => array('type'),
        'default' => '{config_legend},type;{publish_legend},published',
        'root' => '{config_legend},type;{detail_legend},phrase;{publish_legend},published',
		'question' => '{config_legend},type;{title_legend},question;{answer_legend},answer;{publish_legend},published'
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'pid' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default 0",
		),
		'sorting' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default 0"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default 0"
		),
        'type' => array
        (
            'filter'                  => true,
            'inputType'               => 'select',
            'eval'                    => array('submitOnChange'=>true, 'disabled'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(64) NOT NULL default 'question'"
        ),
        'phrase' => array
        (
            'search'                  => true,
            'flag'                    => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'basicEntities'=>true, 'maxlength'=>255, 'tl_class'=>'long'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
		'question' => array
		(
			'search'                  => true,
			'flag'                    => DataContainer::SORT_INITIAL_LETTER_ASC,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'basicEntities'=>true, 'maxlength'=>255, 'tl_class'=>'long'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'answer' => array
		(
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => array('mandatory'=>true, 'rte'=>'tinyMCE', 'helpwizard'=>true),
			'explanation'             => 'insertTags',
			'sql'                     => "text NULL"
		),
		'published' => array
		(
			'toggle'                  => true,
			'filter'                  => true,
			'flag'                    => DataContainer::SORT_INITIAL_LETTER_DESC,
			'inputType'               => 'checkbox',
			'eval'                    => array('doNotCopy'=>true),
			'sql'                     => array('type' => 'boolean', 'default' => false)
		)
	)
);
