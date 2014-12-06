<?php


namespace Frizinak\Corked\Cork;

interface CorkFactoryInterface
{

    /**
     * Create an image level cork.
     *
     * @param array|object|\Traversable $data
     *
     * @return Cork
     */
    public function createCork($data = array());

    /**
     * Create a project level cork.
     *
     * @param array|object|\Traversable $data
     *
     * @return RootCork
     */
    public function createRoot($data = array());
}
