<?php

namespace OptimisticLock\Model\Behavior;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use OptimisticLock\Exception\OptimisticLockException;

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
                $postVersion = $entity->get('version');
                $this->postVersion = $postVersion;
                $latestVersion = $latestEntity->get('version');
                if ($postVersion != $latestVersion) {
                    $event->stopPropagation();
                    throw new OptimisticLockException();
                }
            }
        }
    }
}
