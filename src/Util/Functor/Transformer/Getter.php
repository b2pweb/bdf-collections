<?php

namespace Bdf\Collection\Util\Functor\Transformer;

/**
 * Call a getter on the element
 *
 * (i) This transformer do not add any prefix on the getter.
 *     If the method name is getName(), you must instantiate with `new Getter('getName')`
 *
 * <code>
 * $persons->stream()
 *     ->map(new Getter('name')) // Call method name() on each element, and return the result
 *     ->toArray()
 * ;
 * </code>
 */
final class Getter implements TransformerInterface
{
    /**
     * @var string
     */
    private $name;


    /**
     * Getter constructor.
     *
     * @param string $name The method name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($element, $key = null)
    {
        return $element->{$this->name}();
    }
}
