<?php

use Doublespark\FaqGeneratorBundle\Controller\BackendGenerateQuestionsController;

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['content']['ds_faq'] = [
    'tables' => ['tl_ds_faq_question'],
    'stylesheet' => ['bundles/faqgenerator/css/be-list.css'],
    'generateQuestions' => [BackendGenerateQuestionsController::class, 'generateQuestionsAction'],
];