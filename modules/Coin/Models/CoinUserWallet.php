<?php

namespace Modules\Coin\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Casts\Upper;
use Modules\Core\Models\Traits\DynamicRelationship;
use Modules\Core\Models\Traits\HasFail;
use Modules\Core\Models\Traits\HasTableName;
use Modules\User\Models\ProjectUser;

class CoinUserWallet extends Model
{
    use HasFail,
        HasTableName,
        DynamicRelationship;

    public $table = 'coin_user_wallet';

    protected $fillable = [
        'user_id',
        'chain',
        'address',
        'tokenio_version'
    ];

    protected $casts = [
        'chain' => Upper::class
    ];

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin', 'chain');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function projectUser()
    {
        return $this->hasOne(ProjectUser::class, 'user_id', 'user_id');
    }
}
