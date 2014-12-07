<?php


namespace Frizinak\Corked\Decoder;

use Symfony\Component\Yaml\Yaml;

class YamlDecoder implements DecoderInterface
{

    /**
     * @inheritdoc
     */
    public function decode($encoded)
    {
        return Yaml::parse($encoded, true);
    }
}
