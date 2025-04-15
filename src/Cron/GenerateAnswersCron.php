<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Cron;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Contao\CoreBundle\Framework\ContaoFramework;
use Doctrine\DBAL\Connection;
use Doublespark\FaqGeneratorBundle\Generator\AnswerGenerator;
use Doublespark\FaqGeneratorBundle\Generator\Question;
use Doublespark\FaqGeneratorBundle\Generator\QuestionSet;
use Doublespark\FaqGeneratorBundle\Model\FaqQuestionModel;
use Doublespark\FaqGeneratorBundle\Options\GenerationStatusOptions;
use Psr\Log\LoggerInterface;

#[AsCronJob('minutely')]
class GenerateAnswersCron {

    public function __construct(
        private ContaoFramework $framework,
        private Connection $connection,
        private AnswerGenerator $answerGenerator,
        private LoggerInterface $contaoCronLogger,
        private LoggerInterface $contaoErrorLogger
    ){}

    public function __invoke(): void
    {
        // 10mins
        set_time_limit(600);

        $this->framework->initialize();

        /**
         * @var FaqQuestionModel $faqQuestionModel
         */
        $faqQuestionModel = $this->framework->getAdapter(FaqQuestionModel::class);

        // Get one requested phrase
        $objQuestion = $faqQuestionModel->findOneBy(['status=?','published=?', 'pid=0'], [GenerationStatusOptions::REQUESTED, true]);

        if($objQuestion)
        {
            $this->contaoCronLogger->info("Generating content for [$objQuestion->id] $objQuestion->phrase...");

            // Mark it as working to prevent other cron jobs picking it up
            $objQuestion->status = GenerationStatusOptions::WORKING;
            $objQuestion->save();

            $arrQuestions = $objQuestion->getChildren();

            $arrQuestionsFlat = [];

            if($arrQuestions)
            {
                foreach($arrQuestions as $question)
                {
                    $this->flattenChildren($question, $arrQuestionsFlat);
                }
            }

            try {

                $questionSet = new QuestionSet();

                foreach ($arrQuestionsFlat as $question)
                {
                    $questionSet->addQuestion(new Question((int)$question->id, $question->question));

                    // Generate in batches of 15
                    if($questionSet->count() === 15)
                    {
                        $this->processQuestionSet($questionSet);
                    }
                }

                // Process any remaining questions
                if($questionSet->count() > 0)
                {
                    $this->processQuestionSet($questionSet);
                }

                $objQuestion->status = GenerationStatusOptions::COMPLETE;
                $objQuestion->save();

                $this->contaoCronLogger->info("Finished generating content for [$objQuestion->id] $objQuestion->phrase");

            } catch(\Exception $e) {

                $this->contaoErrorLogger->error('[ID: '.$objQuestion->id.'] FAQ answer generation failed: '.$e->getMessage());

                $objQuestion->status = GenerationStatusOptions::FAILED;
                $objQuestion->save();

            }
        }
    }

    protected function processQuestionSet(QuestionSet $questionSet): void
    {
        $questionSet = $this->answerGenerator->generate($questionSet);

        foreach ($questionSet as $question)
        {
            $this->connection->update(
                'tl_ds_faq_question',
                ['answer' => "<p>".$question->getAnswer()."<p>"],
                ['id' => $question->getId()]
            );
        }

        $questionSet->clear();
    }

    protected function flattenChildren(FaqQuestionModel $objQuestion, &$arrQuestions): void
    {
        $arrQuestions[$objQuestion->id] = $objQuestion;

        if(isset($objQuestion->children) && is_array($objQuestion->children))
        {
            foreach ($objQuestion->children as $childQuestion)
            {
                $this->flattenChildren($childQuestion, $arrQuestions);
            }
        }
    }

}