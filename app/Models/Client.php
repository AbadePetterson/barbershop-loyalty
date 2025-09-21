<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'cuts_count', 'free_cuts_earned'];

    public function addCut(): bool
    {
        $this->increment('cuts_count');

        if ($this->cuts_count % 10 == 0) {
            $this->increment('free_cuts_earned');
            return true;
        }

        return false;
    }

    public function useFreecut()
    {
        if ($this->free_cuts_earned > 0) {
            $this->decrement('free_cuts_earned');
            return true;
        }
        return false;
    }

    public function getProgressAttribute()
    {
        return $this->cuts_count % 10;
    }

    public function getCutsToFreeAttribute()
    {
        return 10 - $this->progress;
    }
}
