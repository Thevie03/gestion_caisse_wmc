<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    /**
     * Affiche la page de présentation (landing page)
     * 
     * Si l'utilisateur est déjà authentifié, il sera redirigé automatiquement
     * vers le dashboard par le middleware RedirectIfAuthenticated
     */
    public function index(): View
    {
        return view('welcome');
    }
}




