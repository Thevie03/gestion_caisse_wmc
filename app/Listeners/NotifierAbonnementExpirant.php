<?php

namespace App\Listeners;

use App\Events\AbonnementExpirant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifierAbonnementExpirant
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\AbonnementExpirant  $event
     * @return void
     */
    public function handle(AbonnementExpirant $event)
    {
        //
    }
}
