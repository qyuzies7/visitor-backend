<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptionList extends Model
{
    protected $fillable = ['group_key','value','label','sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function scopeGroup($q, string $groupKey)
    {
        return $q->where('group_key', $groupKey)->orderBy('sort_order');
    }

    public function scopeForGroups($q, array $groups)
    {
        return $q->whereIn('group_key', $groups)->orderBy('group_key')->orderBy('sort_order');
    }
}
