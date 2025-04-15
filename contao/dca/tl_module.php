<?php

use Doublespark\FaqGeneratorBundle\Controller\FrontendModule\FaqListModuleController;

$GLOBALS['TL_DCA']['tl_module']['palettes'][FaqListModuleController::TYPE] = '{title_legend},name,headline,type;{config_legend},fg_faqSections,fg_faqListMode;{protected_legend:hide},protected;{expert_legend:hide},cssID';

$GLOBALS['TL_DCA']['tl_module']['fields']['fg_faqSections'] = [
    'inputType' => 'checkboxWizard',
    'eval'      => array('mandatory'=>false, 'multiple'=>true, 'tl_class'=>'w100'),
    'sql'       => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['fg_faqListMode'] = [
    'inputType' => 'select',
    'options' => [
        'nested' => 'Nested',
        'flat' => 'Flat'
    ],
    'eval' => array('mandatory'=>true, 'tl_class'=>'w100'),
    'sql' => "varchar(255) NOT NULL default ''"
];