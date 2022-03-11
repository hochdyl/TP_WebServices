<?php

namespace App\Service;

use Exception;

class EntityUpdaterService
{
    /**
     * Update an entity from keys passed to the data array.
     *
     * @param object $entity
     * @param array $data Associated array like "property" => "value"
     * @return object The updated entity
     * @throws Exception
     */
    public function update(object $entity, array $data): object
    {
        foreach ($data as $key => $value) {
            $method = 'set'.ucfirst($key);
            if (method_exists($entity, $method)) {
                try {
                    $entity->$method($value);
                } catch (Exception) {
                    throw new Exception('Cannot update "' . $key . '" with "' . $value . '"');
                }
            } else {
                throw new Exception('Cannot update "' . $key . '", unknown property.');
            }
        }
        return $entity;
    }
}