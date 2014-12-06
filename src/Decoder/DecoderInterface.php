<?php


namespace Frizinak\Corked\Decoder;

interface DecoderInterface
{

    /**
     * Decodes an encode string
     *
     * @param string $encoded
     *
     * @return mixed decoded.
     */
    public function decode($encoded);
}
