<?php

namespace CakeDC\Users\Social;

class MapUser
{
    /**
     * Map social user user data
     *
     * @param \CakeDC\Users\Social\Service\ServiceInterface $service social service
     * @param array $data user social data
     *
     * @return mixed
     */
    public function __invoke($service, $data)
    {
        $mapper = $service->getConfig('mapper');
        if (is_string($mapper)) {
            $mapper = $this->buildMapper($mapper);
        }

        $user = $mapper($data);
        $user['provider'] = $service->getProviderName();

        return $user;
    }

    /**
     * Build the mapper object
     *
     * @param string $className of mapper
     *
     * @return callable
     */
    protected function buildMapper($className)
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(__("Provider mapper class {0} does not exist", $className));
        }

        return new $className();
    }
}