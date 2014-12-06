<?php


namespace Frizinak\Corked\Decoder;

use Frizinak\Corked\Decoder\Exception\DecodingFailedException;
use Frizinak\Corked\Exception\InvalidArgumentException;

class JsonDecoder implements DecoderInterface
{

    /**
     *
     * @inheritdoc
     *
     * @param bool $assoc if applicable: true: array, false: stdClasses. @see json_decode().
     * @param int  $depth Maximum decoding depth. @see json_decode().
     *
     * @return mixed
     */
    public function decode($encoded, $assoc = true, $depth = 512)
    {
        if (!is_string($encoded)) {
            throw new InvalidArgumentException('Can only decode json strings');
        }
        $decoded = @json_decode($encoded, $assoc, $depth);
        if (($error = json_last_error()) !== JSON_ERROR_NONE) {
            throw new DecodingFailedException($this->jsonErrorMessage($error), $error);
        }
        return $decoded;
    }

    protected function jsonErrorMessage($error)
    {
        switch ($error) {
            case JSON_ERROR_DEPTH:
                return 'The maximum stack depth has been exceeded.';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Invalid or malformed JSON.';
            case JSON_ERROR_CTRL_CHAR:
                return 'Control character error, possibly incorrectly encoded.';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error.';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded.';
            case JSON_ERROR_RECURSION:
                return 'One or more recursive references in the value to be encoded.';
            case JSON_ERROR_INF_OR_NAN:
                return 'One or more NAN or INF values in the value to be encoded.';
            case JSON_ERROR_UNSUPPORTED_TYPE:
                return 'A value of a type that cannot be encoded was given.';
        }

        return 'Unknown JSON error.';
    }
}
