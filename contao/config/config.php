<?php

use Doublespark\FaqGeneratorBundle\Controller\BackendGenerateQuestionsController;
use Doublespark\FaqGeneratorBundle\Controller\BackendGenerateAnswersController;
use Doublespark\FaqGeneratorBundle\Model\FaqQuestionModel;

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_ds_faq_question'] = FaqQuestionModel::class;

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['content']['ds_faq'] = [
    'tables' => ['tl_ds_faq_question'],
    'stylesheet' => ['bundles/faqgenerator/css/be-list.css'],
    'generateQuestions' => [BackendGenerateQuestionsController::class, 'generateQuestionsAction'],
    'generateAnswers' => [BackendGenerateAnswersController::class, 'generateAnswersAction'],
];