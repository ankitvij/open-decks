<?php

namespace App\Policies;

use App\Models\Night;
use App\Models\User;

class NightPolicy
{
    /** Only the host who created a night can see its guest list and manage it. */
    public function manage(User $user, Night $night): bool
    {
        return $night->host_id === $user->id;
    }
}
