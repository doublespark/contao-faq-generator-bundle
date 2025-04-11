<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addLegend('faq_generator_legend', 'chmod_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField('fg_alsoAskedApiKey', 'faq_generator_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('fg_alsoAskedApiEnv', 'faq_generator_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('fg_openAiApiKey', 'faq_generator_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_settings')
;

$GLOBALS['TL_DCA']['tl_settings']['fields']['fg_alsoAskedApiKey'] = [
    'inputType' => 'text',
    'eval'      => array('mandatory'=>false, 'tl_class'=>'w100')
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['fg_alsoAskedApiEnv'] = [
    'inputType' => 'select',
    'options' => [
        'sandbox' => 'Sandbox',
        'production' => 'Production'
    ],
    'eval' => array('mandatory'=>true, 'tl_class'=>'w100')
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['fg_openAiApiKey'] = [
    'inputType' => 'text',
    'eval'      => array('mandatory'=>false, 'tl_class'=>'w100')
];