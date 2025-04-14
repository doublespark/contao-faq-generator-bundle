<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\Message;
use Doctrine\DBAL\Connection;
use Doublespark\FaqGeneratorBundle\Generator\AnswerGenerator;
use Doublespark\FaqGeneratorBundle\Generator\Question;
use Doublespark\FaqGeneratorBundle\Generator\QuestionSet;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class BackendGenerateAnswersController {

    public function __construct(
        private ContaoFramework $framework,
        private RequestStack $requestStack,
        private Connection $connection,
        private AnswerGenerator $answerGenerator
    ){}

    public function generateAnswersAction(DataContainer $dc): Response
    {
        $this->framework->initialize();

        $pid = (int)$dc->id;

        $arrQuestions = [];

        // Fetch all published questions
        $this->getQuestionsByPid($pid, $arrQuestions);

        $questionSet = new QuestionSet();

        foreach ($arrQuestions as $question)
        {
            $questionSet->addQuestion(new Question((int)$question['id'], $question['question']));
        }

        $questionSet = $this->answerGenerator->generate($questionSet);

        $i=0;

        foreach ($questionSet as $question)
        {
            $this->connection->update(
                'tl_ds_faq_question',
                ['answer' => "<p>".$question->getAnswer()."<p>"],
                ['id' => $question->getId()]
            );

            $i++;
        }

        /**
         * @var Message $message
         */
        $message = $this->framework->getAdapter(Message::class);

        $message->addConfirmation("Updated the answers for $i questions.");

        $request = $this->requestStack->getCurrentRequest();

        return new RedirectResponse($this->getBackUrl($request));

    }

    private function getBackUrl(Request $request): string
    {
        return str_replace('&key='.$request->query->get('key'), '', $request->getRequestUri());
    }

    private function getQuestionsByPid(int $pid, &$arrQuestions): void
    {
        $result = $this->connection->prepare('SELECT * FROM tl_ds_faq_question WHERE pid=? AND published=1')->executeQuery(params: [
            0 => $pid,
        ]);

        $arrResults = $result->fetchAllAssociative();

        if(count($arrResults) < 1)
        {
            return;
        }

        foreach ($arrResults as $result)
        {
            $arrQuestions[] = $result;
            $this->getQuestionsByPid($result['id'], $arrQuestions);
        }
    }
}