<?php

namespace Philip0514\Ark\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

//Traits
use Philip0514\Ark\Traits\Helper;

class About extends Model
{
    use SoftDeletes, Helper;

    public function detachAll($rows1)
    {
        $rows1->ogimages()->detach();
    }

    public function ogimages()
    {
        return $this->morphToMany('Philip0514\Ark\Models\Media', 'media_relations');
    }
}
