<?php


namespace Frizinak\Corked\DependencyResolver;

use Frizinak\Corked\Cork\Cork;
use Frizinak\Corked\Decoder\DecoderInterface;
use Frizinak\Corked\Definition\FromDefinition;
use Frizinak\Corked\DependencyResolver\Exception\DependencyResolvingFailedException;
use Frizinak\Corked\DependencyResolver\Exception\InvalidDataException;

/**
 * Searches the filesystem for paths matching the docker qualifier containing a decodable file.
 */
class FileResolver extends AbstractFileResolver
{

    /** @var DecoderInterface[] */
    protected $decoders = array();

    /**
     * Add a new decoder to the set of decoders.
     * Decoders are used based on the filenames they should be able to decode.
     *
     * @param string           $filename A filename that if found will be decoded using $decoder.
     * @param DecoderInterface $decoder
     */
    public function addDecoder($filename, DecoderInterface $decoder)
    {
        $this->decoders[$filename] = $decoder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Cork $cork)
    {
        /** @var FromDefinition $from */
        $from = $cork->getDefinition('from');
        if (!$from->getValue()) {
            return null;
        }

        if (!($namespace = $from->getNamespace())) {
            throw new DependencyResolvingFailedException($cork, 'Can not resolve when an empty namespace is given');
        }

        $name = $from->getName();
        $tag = $from->getTag();
        foreach ($this->lookupPaths as $path) {
            if ($data = $this->resolveInPath($path, $namespace, $name, $tag)) {
                $data['name'] = $from->getFullQualifier();
                return $data;
            };
        }

        throw new DependencyResolvingFailedException($cork, sprintf('%s/%s:%s could not be resolved', $namespace, $name, $tag));
    }

    protected function resolveInPath($path, $namespace, $name, $tag)
    {
        $basePath = $path . DIRECTORY_SEPARATOR . $namespace . DIRECTORY_SEPARATOR . $name;
        foreach ($this->decoders as $filename => $decoder) {
            $paths = array(
                // oracle/mysql/5.6.21/corked.json
                $basePath . DIRECTORY_SEPARATOR . $tag . DIRECTORY_SEPARATOR . $filename,
                // oracle/mysql_5.6.21/corked.json
                $basePath . '_' . $tag . DIRECTORY_SEPARATOR . $filename,
                // oracle/mysql/5.6.21_corked.json
                $basePath . DIRECTORY_SEPARATOR . $tag . '_' . $filename,
                // oracle/mysql_5.6.21_corked.json
                $basePath . '_' . $tag . '_' . $filename,
                // oracle/mysql/corked.json
                $basePath . DIRECTORY_SEPARATOR . $filename,
                // oracle/mysql_corked.json
                $basePath . '_' . $filename,
            );

            foreach ($paths as $filepath) {
                if (file_exists($filepath)) {
                    $data = $decoder->decode(file_get_contents($filepath));
                    if (!is_array($data)) {
                        throw new InvalidDataException();
                    }

                    $data['path'] = dirname($filepath);
                    return $data;
                }
            }
        }
    }
}
