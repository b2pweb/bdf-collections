<?php

namespace Bdf\Collection\Stream;

/**
 * Class FlatMapStream
 *
 * @template T
 *
 * @template ST
 * @template SK
 *
 * @implements StreamInterface<T, mixed>
 */
final class FlatMapStream implements StreamInterface
{
    use StreamTrait;

    /**
     * @var StreamInterface<ST, SK>
     */
    private $stream;

    /**
     * @var callable(ST, SK):(T|T[]|StreamInterface<T, mixed>)
     */
    private $transformer;

    /**
     * @var bool
     */
    private $preserveKeys;

    /**
     * @var StreamInterface<T, mixed>|null
     */
    private $currentStream;

    /**
     * @var T|null
     */
    private $currentValue;

    /**
     * @var int
     */
    private $index = 0;


    /**
     * FlatMapStream constructor.
     *
     * @param StreamInterface<ST, SK> $stream
     * @param callable(ST, SK):(StreamInterface<T, mixed>|T[]|T) $transformer
     * @param bool $preserveKeys
     */
    public function __construct(StreamInterface $stream, callable $transformer, bool $preserveKeys = false)
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
