<?php

namespace Bdf\Collection\Util\Functor\Consumer;

/**
 * Consumer for call a method of the input element
 *
 * <code>
 * $entities->forEach(new Call('update', [['enabled']]);
 * </code>
 */
final class Call implements ConsumerInterface
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $arguments;


    /**
     * Call constructor.
     *
     * @param string $method
     * @param array $arguments
     */
    public function __construct(string $method, array $arguments = [])
    {
        $this->method = $method;
        $this->arguments = $arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($element, $key = null): void
    {
        $element->{$this->method}(...$this->arguments);
    }
}
