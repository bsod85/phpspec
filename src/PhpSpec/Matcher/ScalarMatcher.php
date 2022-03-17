<?php

/*
 * This file is part of PhpSpec, A php toolset to drive emergent
 * design by specification.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpSpec\Matcher;

use PhpSpec\Formatter\Presenter\Presenter;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Wrapper\DelayedCall;

final class ScalarMatcher implements Matcher
{
    private Presenter $presenter;

    
    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    /**
     * Checks if matcher supports provided subject and matcher name.
     */
    public function supports(string $name, mixed $subject, array $arguments): bool
    {
        $checkerName = $this->getCheckerName($name);

        return $checkerName && function_exists($checkerName);
    }

    /**
     * Evaluates positive match.
     *
     *
     * @throws FailureException
     */
    public function positiveMatch(string $name, mixed $subject, array $arguments) : ?DelayedCall
    {
        /** @var string $checker */
        $checker = $this->getCheckerName($name);

        if (!\call_user_func($checker, $subject)) {
            throw new FailureException(sprintf(
                '%s expected to return %s, but it did not.',
                $this->presenter->presentString(sprintf(
                    '%s(%s)',
                    $checker,
                    $this->presenter->presentValue($subject)
                )),
                $this->presenter->presentValue(true)
            ));
        }

        return null;
    }

    /**
     * Evaluates negative match.
     *
     *
     * @throws FailureException
     */
    public function negativeMatch(string $name, mixed $subject, array $arguments) : ?DelayedCall
    {
        /** @var string $checker */
        $checker = $this->getCheckerName($name);

        if (\call_user_func($checker, $subject)) {
            throw new FailureException(sprintf(
                '%s not expected to return %s, but it did.',
                $this->presenter->presentString(sprintf(
                    '%s(%s)',
                    $checker,
                    $this->presenter->presentValue($subject)
                )),
                $this->presenter->presentValue(true)
            ));
        }

        return null;
    }

    /**
     * Returns matcher priority.
     */
    public function getPriority(): int
    {
        return 50;
    }

    private function getCheckerName(string $name): string|false
    {
        if (0 !== strpos($name, 'be')) {
            return false;
        }

        $expected = lcfirst(substr($name, 2));
        if ($expected == 'boolean') {
            return 'is_bool';
        }
        if ($expected == 'real') {
            return 'is_float';
        }

        return 'is_'.$expected;
    }
}
