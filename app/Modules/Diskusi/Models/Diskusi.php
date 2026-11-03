<?php

namespace App\Modules\Diskusi\Models;

use App\Modules\Pengguna\Models\User;
use Illuminate\Database\Eloquent\Model;

class Diskusi extends Model
{
    protected $table = 'diskusi';
    protected $fillable = ['ruang_id', 'user_id', 'parent_id', 'isi'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function balasan()
    {
        return $this->hasMany(Diskusi::class, 'parent_id')->oldest();
    }
}
