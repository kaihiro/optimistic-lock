<?php

namespace OptimisticLock\Model;

use InvalidArgumentException;
use OptimisticLock\Exception\OptimisticLockException;

/**
 * Class OptimisticLockTableTrait
 *
 * @package OptimisticLock\Model
 */
trait OptimisticLockTableTrait
{
    /**
     * Auxiliary function to handle the insert of an entity's data in the table
     *
     * @param \Cake\Datasource\EntityInterface $entity the subject entity from were $data was extracted
     * @param array $data The actual data that needs to be saved
     * @return \Cake\Datasource\EntityInterface|bool
     * @throws \RuntimeException if not all the primary keys where supplied or could
     * be generated when the table has composite primary keys. Or when the table has no primary key.
     */
    protected function _insert($entity, $data)
    {
        if ($this->hasField('version')) {
            $entity->set('version', 1);
            $data['version'] = 1;
        }
        return parent::_insert($entity, $data);
    }

    /**
     * Auxiliary function to handle the update of an entity's data in the table
     *
     * @param \Cake\Datasource\EntityInterface $entity the subject entity from were $data was extracted
     * @param array $data The actual data that needs to be saved
     * @return \Cake\Datasource\EntityInterface|bool
     * @throws \InvalidArgumentException When primary key data is missing.
     */
    protected function _update($entity, $data)
    {
        $primaryColumns = (array)$this->primaryKey();
        $primaryKey = $entity->extract($primaryColumns);

        $data = array_diff_key($data, $primaryKey);
        if (empty($data)) {
            return $entity;
        }

        if (!$entity->has($primaryColumns)) {
            $message = 'All primary key value(s) are needed for updating, ';
            $message .= get_class($entity) . ' is missing ' . implode(', ', $primaryColumns);
            throw new InvalidArgumentException($message);
        }

        // for optimistic lock
        $conditions = $primaryKey;
        if ($this->hasField('version')) {
            if ($entity->has('version')) {
                $version = $entity->get('version');

                $entity->set('version', $version + 1);
                $data['version'] = $version + 1;

                $conditions['version'] = $version;

            } else {
                $entity->set('version', 1);
                $data['version'] = 1;
            }
        }

        $query = $this->query();
        $statement = $query->update()
            ->set($data)
            ->where($conditions)
            ->execute();

        $success = false;
        if ($statement->errorCode() === '00000') {
            // for optimistic lock
            if ($statement->count() === 0) {
                throw new OptimisticLockException();
            }
            $success = $entity;
        }
        $statement->closeCursor();

        return $success;
    }
}
