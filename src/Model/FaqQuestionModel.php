<?php

namespace Doublespark\FaqGeneratorBundle\Model;

use Contao\Model;

class FaqQuestionModel extends Model {

    protected static $strTable = 'tl_ds_faq_question';

    public function getChildren(int|null $pid=null): array
    {
        if(is_null($pid))
        {
            $pid = $this->id;
        }

        $result = static::findBy(['pid=?', 'published=1'], [$pid]);

        $children = [];

        if($result)
        {
            foreach($result as $question)
            {
                $children[] = $question;

                $question->children = $this->getChildren($question->id);
            }
        }

        // Set on local model
        $this->children = $children;

        return $children;
    }

}