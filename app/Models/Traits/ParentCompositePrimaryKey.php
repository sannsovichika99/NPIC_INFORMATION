<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ParentCompositePrimaryKey
{
    protected function setKeysForSaveQuery($query)
    {
        return $query->where('id_student', $this->getAttribute('id_student'))
            ->where('role', $this->getAttribute('role'));
    }
}
