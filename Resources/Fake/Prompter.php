<?php

/*
 * This file is part of the doyo/code-coverage project.
 *
 * (c) Anthonius Munthi <https://itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fake;

use PhpSpec\Console\Prompter as PrompterInterface;

class Prompter implements PrompterInterface
{
    private $answers      = [];
    private $hasBeenAsked = false;
    private $question;
    private $unansweredQuestions = false;

    public function setAnswer($answer)
    {
        $this->answers[] = $answer;
    }

    public function askConfirmation(string $question, bool $default = true): bool
    {
        $this->hasBeenAsked = true;
        $this->question     = $question;

        $this->unansweredQuestions = \count($this->answers) > 1;

        return (bool) array_shift($this->answers);
    }

    public function hasBeenAsked($question = null)
    {
        if (!$question) {
            return $this->hasBeenAsked;
        }

        return $this->hasBeenAsked
            && $this->normalise($this->question) === $this->normalise($question);
    }

    public function hasUnansweredQuestions()
    {
        return $this->unansweredQuestions;
    }

    /**
     * @param mixed $question
     *
     * @return mixed
     */
    private function normalise($question)
    {
        return preg_replace('/\s+/', '', trim(strip_tags($question)));
    }
}
