<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasCompositePrimaryKey
{

    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForQuery($keyName));
        }

        return $query;
    }

    protected function getKeyForQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }
}
