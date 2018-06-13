<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Auth\AuthManager;

class BookmarksController extends Controller
{
    public function index(AuthManager $authManager)
    {
        $currentUser = $authManager->guard()->user();

        return view('bookmarks.index', [
            'currentUser' => $currentUser,
        ]);
    }
}