<?php

namespace App\Http\Controllers;

use App\Support\ListingFilterValidator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Valide les paramètres de filtrage des listes (recherche, dates, statuts…).
     */
    protected function validateListingFilters(Request $request, array $extra = []): void
    {
        ListingFilterValidator::validate($request, $extra);
    }
}
