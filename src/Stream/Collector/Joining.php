<?php

namespace Bdf\Collection\Stream\Collector;

/**
 * Concatenates all elements into a string, with a separator, a prefix and a suffix
 *
 * <code>
 * $stream = new ArrayStream([1, 2, 3]);
 * $stream->collect(new Joining()); // '123'
 * $stream->collect(new Joining(',')); // '1,2,3'
 * $stream->collect(new Joining(',', '[', ']')); // '[1,2,3]'
 * </code>
 */
final class Joining implements CollectorInterface
{
    /**
     * @var string
     */
    private $separator;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $suffix;

    /**
     * @var string
     */
    private $aggregation = null;


    /**
     * Joining constructor.
     *
     * @param string $separator The elements separator
     * @param string $prefix The prefix
     * @param string $suffix The suffix
     */
    public function __construct($separator = '', $prefix = '', $suffix = '')
    {
        $this->separator = $separator;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }

    /**
     * {@inheritdoc}
     */
    public function aggregate($element, $key = null)
    {
        if ($this->aggregation === null) {
            $this->aggregation = (string) $element;
        } else {
            $this->aggregation .= $this->separator.$element;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finalize()
    {
        return $this->prefix.$this->aggregation.$this->suffix;
    }
}
