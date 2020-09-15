<?php

namespace Bdf\Collection\Stream;

/**
 * Class FlatMapStream
 */
final class FlatMapStream implements StreamInterface
{
    use StreamTrait;

    /**
     * @var StreamInterface
     */
    private $stream;

    /**
     * @var callable
     */
    private $transformer;

    /**
     * @var bool
     */
    private $preserveKeys;

    /**
     * @var StreamInterface|null
     */
    private $currentStream;

    /**
     * @var mixed
     */
    private $currentValue;

    /**
     * @var int
     */
    private $index = 0;


    /**
     * FlatMapStream constructor.
     *
     * @param StreamInterface $stream
     * @param callable $transformer
     * @param bool $preserveKeys
     */
    public function __construct(StreamInterface $stream, callable $transformer, $preserveKeys = false)
    {
        $this->stream = $stream;
        $this->transformer = $transformer;
        $this->preserveKeys = $preserveKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->currentValue;
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress PossiblyNullReference
     */
    public function next()
    {
        ++$this->index;
        $this->currentStream->next();

        if ($this->currentStream->valid()) {
            $this->currentValue = $this->currentStream->current();
            return;
        }

        $this->stream->next();
        $this->loadNextStream();
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress PossiblyNullReference
     */
    public function key()
    {
        return $this->preserveKeys ? $this->currentStream->key() : $this->index;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->currentStream !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->stream->rewind();

        $this->index = 0;
        $this->loadNextStream();
    }

    private function loadNextStream(): void
    {
        $this->currentStream = null;
        $this->currentValue = null;

        while ($this->stream->valid()) {
            $currentStream = Streams::wrap(($this->transformer)($this->stream->current(), $this->stream->key()));
            $currentStream->rewind();

            if ($currentStream->valid()) {
                $this->currentStream = $currentStream;
                $this->currentValue = $currentStream->current();
                return;
            }

            $this->stream->next();
        }
    }
}
