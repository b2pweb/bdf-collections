<?php

namespace Bdf\Collection\Stream;

use AppendIterator;

/**
 * Concatenate two or more Streams into one stream
 *
 * The streams will be iterated consecutively (The first iterator is the first iterated)
 *
 * @template T
 * @implements StreamInterface<T, mixed>
 *
 * @see StreamInterface::concat()
 */
final class ConcatStream extends AppendIterator implements StreamInterface
{
    use StreamTrait;

    /**
     * @var bool
     */
    private $preserveKeys;

    /**
     * @var int
     */
    private $key = 0;


    /**
     * ConcatStream constructor.
     *
     * @param array<StreamInterface<T, mixed>> $streams
     * @param bool $preserveKeys Preserve the base stream keys
     */
    public function __construct(array $streams, bool $preserveKeys = true)
    {
        parent::__construct();

        $this->preserveKeys = $preserveKeys;

        foreach ($streams as $stream) {
            $this->append($stream);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function concat(StreamInterface $stream, bool $preserveKeys = true): StreamInterface
    {
        // Do not preserve key for previous streams, but keep for new stream
        // => Create a new ContactStream for keeping new stream keys
        if (!$this->preserveKeys && $preserveKeys) {
            return new ConcatStream([$this, $stream], true);
        }

        // If keys are not preserved for the last stream
        // All the previous streams wil also loose their keys
        if (!$preserveKeys) {
            $this->preserveKeys = false;
        }

        $this->append($stream);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        if ($this->preserveKeys) {
            return parent::key();
        }

        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        parent::next();

        ++$this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        parent::rewind();

        $this->key = 0;
    }
}
