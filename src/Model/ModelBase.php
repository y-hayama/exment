<?php

namespace Exceedone\Exment\Model;

use Illuminate\Database\Eloquent\Model;
use Exceedone\Exment\Enums\SystemTableName;

class ModelBase extends Model
{
    protected $guarded = ['id'];
    
    /**
     * Get CreatedUser. Only name.
     *
     * @return void
     */
    public function getCreatedUserAttribute()
    {
        return $this->getUser('created_user_id');
    }
    public function getUpdatedUserAttribute()
    {
        return $this->getUser('updated_user_id');
    }
    
    /**
     * Get CreatedUser. As custom value object
     *
     * @return void
     */
    public function getCreatedUserValueAttribute()
    {
        return $this->getUserValue('created_user_id');
    }
    public function getUpdatedUserValueAttribute()
    {
        return $this->getUserValue('updated_user_id');
    }
    
    /**
     * Get CreatedUser. As HTML
     *
     * @return void
     */
    public function getCreatedUserTagAttribute()
    {
        return $this->getUser('created_user_id', true);
    }
    public function getUpdatedUserTagAttribute()
    {
        return $this->getUser('updated_user_id', true);
    }

    
    /**
     * Get CreatedUser. Append avatar
     *
     * @return void
     */
    public function getCreatedUserAvatarAttribute()
    {
        return $this->getUser('created_user_id', true, true);
    }
    public function getUpdatedUserAvatarAttribute()
    {
        return $this->getUser('updated_user_id', true, true);
    }

    /**
     * Whether this model disable delete
     *
     * @return boolean
     */
    public function getDisabledDeleteAttribute()
    {
        return false;
    }

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    /**
    *
    * @return void
    */
    protected static function boot()
    {
        parent::boot();

        ///// add created_user_id, updated_user_id
        static::creating(function ($model) {
            static::setUser($model, ['created_user_id', 'updated_user_id']);
        });
        static::updating(function ($model) {
            static::setUser($model, ['updated_user_id']);
        });
        
        static::saved(function ($model) {
            static::callClearCache();
        });
        static::deleted(function ($model) {
            static::callClearCache();
        });
    }

    /**
     * Call clear cache if definition
     *
     * @return void
     */
    protected static function callClearCache()
    {
        $classname = get_called_class();
        if (\method_exists($classname, 'clearCacheTrait')) {
            $classname::clearCacheTrait();
        }
    }

    /**
     * set id to $columns
     */
    protected static function setUser($model, $columns = [])
    {
        $user = \Exment::user() ?? null;
        if (!isset($user)) {
            return;
        }
        $user_id = $user->getUserId();
        foreach ($columns as $column) {
            $model->{$column} = $user_id;
        }
    }

    /**
     * get eloquent using request settion.
     * now only support only id.
     */
    public static function getEloquentDefault($obj, $withs = [], $query_key = 'id')
    {
        return static::_getEloquent($obj, $withs, $query_key, 'firstRecord');
    }

    /**
     * get eloquent using request settion.
     * now only support only id.
     */
    public static function getEloquentCache($obj, $withs = [], $query_key = 'id')
    {
        return static::_getEloquent($obj, $withs, $query_key, 'firstRecordCache');
    }

    /**
     * get eloquent using request settion.
     * now only support only id.
     */
    protected static function _getEloquent($obj, $withs = [], $query_key = 'id', $fucnName = 'firstRecord')
    {
        if (is_nullorempty($obj)) {
            return null;
        }

        
        if (is_object($obj) && get_class($obj) == get_called_class()) {
            return $obj;
        }
        
        // get table
        $obj = static::{$fucnName}(function ($table) use ($query_key, $obj) {
            return array_get($table, $query_key) == $obj;
        });

        if (is_nullorempty($obj)) {
            return null;
        }

        if (count($withs) > 0) {
            $obj->load($withs);
        }

        return $obj;
    }

    /**
     * get user from id
     */
    protected function getUser($column, $link = false, $addAvatar = false)
    {
        return getUserName($this->{$column}, $link, $addAvatar);
    }

    /**
     * get user from id
     */
    protected function getUserValue($column)
    {
        return CustomTable::getEloquent(SystemTableName::USER)->getValueModel($this->{$column}, true);
    }
}
