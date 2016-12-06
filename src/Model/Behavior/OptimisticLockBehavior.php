<?php

namespace OptimisticLock\Model\Behavior;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use OptimisticLock\Model\Behavior\Exception\OptimisticLockException;

/**
 * 楽観ロックを行うためのビヘイビアです.
 *
 * レコード更新時に楽観ロックを行いたいTableクラスで読み込んでください。
 * 対象のテーブルにはintegerのversionカラムが必要です。
 *
 * Class OptimisticLockBehavior
 */
class OptimisticLockBehavior extends Behavior
{
    protected $_defaultConfig = [
        // Don't change!! Now, support 'version' only.
        'version_field' => 'version',
    ];

    /**
     * versionが一致しているかをチェックします.
     *
     * DBの最新レコードのversionとフォームのversionが一致しない場合、OptimisticLockExceptionをスローします.
     *
     * @param Event           $event
     * @param EntityInterface $entity
     * @param $options
     * @param $operation
     */
    public function beforeRules(Event $event, EntityInterface $entity, $options, $operation)
    {
        if (isset($entity->{$this->_table->primaryKey()})) {
            $latestEntity = $this->_table->get($entity->{$this->_table->primaryKey()});
            if (!empty($latestEntity)) {
                $config = $this->config();
                $currentVersion = $entity->get($config['version_field']);
                $latestVersion = $latestEntity->get($config['version_field']);
                if ($currentVersion != $latestVersion) {
                    $event->stopPropagation();
                    throw new OptimisticLockException();
                }
            }
        }
    }

    /**
     * 更新前にversionをインクリメントして保存します.
     *
     * versionの値が未設定の場合は1を設定します。
     *
     * @param Event           $event
     * @param EntityInterface $entity
     * @param $options
     */
    public function beforeSave(Event $event, EntityInterface $entity, $options)
    {
        $config = $this->config();
        if ($entity->has($config['version_field'])) {
            if ($entity->dirty($config['version_field'])) {
                return;
            }
            $currentVersion = $entity->get($config['version_field']);
            $entity->set($config['version_field'], $currentVersion + 1);
        } else {
            $entity->set($config['version_field'], 1);
        }
    }
}
