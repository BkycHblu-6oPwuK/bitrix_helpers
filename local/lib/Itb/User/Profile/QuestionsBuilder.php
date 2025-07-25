<?php

namespace Itb\User\Profile;

use Itb\Repository\Iblock\QuestionRepository;

class QuestionsBuilder
{
    private QuestionRepository $repository;

    public function __construct(QuestionRepository $repository)
    {
        $this->repository = $repository;
    }
    
    /**
     * @return QuestionDTO[]
     */
    public function build() : array
    {
        $elements = $this->repository->getQuestions();
        $result = [];
        foreach($elements as $element){
            $dto = new QuestionDTO;
            $dto->question = $element['NAME'];
            $dto->answer = $element['PREVIEW_TEXT'];
            $result[] = $dto;
        }
        return $result;
    }
}