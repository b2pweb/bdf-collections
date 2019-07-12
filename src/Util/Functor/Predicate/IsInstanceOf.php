<?php

namespace Bdf\Collection\Util\Functor\Predicate;

/**
 * Check if the element is an instance of the given class name
 *
 * <code>
 * $stream
 *     ->filter(new IsInstanceOf(User::class)
 *     ->forEach(function (User $user) { ... })
 * ;
 * </code>
 */
final class IsInstanceOf implements PredicateInterface
{
    /**
     * @var string
     */
    private $className;


    /**
     * IsInstanceOf constructor.
     *
     * @param string $className The class name
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($element, $key = null): bool
    {
        return $element instanceof $this->className;
    }
}
