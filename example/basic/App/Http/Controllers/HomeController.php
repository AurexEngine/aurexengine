<?php

namespace App\Http\Controllers;

use AurexEngine\Http\Request;
use AurexEngine\Http\Response;

class HomeController
{
    public function index(Request $request): Response
    {
        return new Response('Home ✅ (controller resolved)');
    }

    public function show(Request $request, int $id)
    {
        return "User ID: $id";
    }
}