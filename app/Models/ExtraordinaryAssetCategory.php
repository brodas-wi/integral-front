<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtraordinaryAssetCategory extends Model
{
    protected $fillable = ['name', 'slug'];

    public function extraordinaryAssets()
    {
        return $this->hasMany(ExtraordinaryAsset::class);
    }
}
