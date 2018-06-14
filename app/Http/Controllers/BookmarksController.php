<?php

namespace App\Http\Controllers;

use App\Services\ChromeImporter as Importer;
use App\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;

class BookmarksController extends Controller
{
    /** @var User  */
    private $currentUser;

    /** @var Importer */
    private $importer;

    public function __construct(AuthManager $authManager, Importer $importer)
    {
        $this->importer = $importer;

        // you can't access the current user until the middleware is running
        $this->middleware(function ($request, $next) use ($authManager) {
            $this->currentUser= $authManager->guard()->user();

            return $next($request);
        });
    }

    public function index()
    {
        return view('bookmarks.index', [
            'currentUser' => $this->currentUser,
        ]);
    }

    public function importForm()
    {
        return view('bookmarks.import', [
            'done' => false,
            'error' => false,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|max:10000|mimetypes:text/html',
        ]);

        $file = $request->file('file');

        $error = !$this->importer->import($file, $this->currentUser->id);

        return view('bookmarks.import', [
            'done' => true,
            'error' => $error,
        ]);
    }
}