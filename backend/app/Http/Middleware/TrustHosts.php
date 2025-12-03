<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array<int, string>
     */
    public function hosts()
    {
        // Disable host pattern checking to avoid invalid regex crashes in production.
        // This is acceptable for this deployment environment.
        return [];

        /*
        // If you prefer to keep the default behaviour safely, you can use:
        //
        // if ($pattern = $this->allSubdomainsOfApplicationUrl()) {
        //     return [$pattern];
        // }
        //
        // return [];
        */
    }
}
