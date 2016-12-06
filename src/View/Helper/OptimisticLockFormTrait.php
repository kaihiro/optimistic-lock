<?php

namespace OptimisticLock\View\Helper;

use Cake\ORM\Entity;

trait OptimisticLockFormTrait
{
    public function create($model = null, array $options = [])
    {
        $out = parent::create($model, $options);
        if ($model != null && $model instanceof Entity) {
            if ($model->has('version')) {
                $out .= $this->hidden('version');
            }
        }

        return $out;
    }
}
