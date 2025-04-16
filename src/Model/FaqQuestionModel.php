<?php

namespace Doublespark\FaqGeneratorBundle\Model;

use Contao\Model;

class FaqQuestionModel extends Model {

    protected static $strTable = 'tl_ds_faq_question';

    public function getChildren(bool $publishedOnly=true, int|null $pid=null): array
    {
        if(is_null($pid))
        {
            $pid = $this->id;
        }

        if($publishedOnly)
        {
            $result = static::findBy(['pid=?', 'published=1'], [$pid]);
        }
        else
        {
            $result = static::findBy(['pid=?'], [$pid]);
        }

        $children = [];

        if($result)
        {
            foreach($result as $question)
            {
                $children[] = $question;

                $question->children = $this->getChildren($publishedOnly,$question->id);
            }
        }

        // Set on local model
        $this->children = $children;

        return $children;
    }

}