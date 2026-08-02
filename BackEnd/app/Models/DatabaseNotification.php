<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification as FrameworkDatabaseNotification;

class DatabaseNotification extends FrameworkDatabaseNotification
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hongvan_notifications';
}
