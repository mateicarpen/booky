<?php

namespace App\Http\Controllers\Api;

use App\Bookmark;
use App\Http\Controllers\Controller;
use App\Services\Bookmarks;
use App\Services\HttpHelper;
use GuzzleHttp\Client;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookmarksController extends Controller
{
    /** @var Bookmarks */
    private $bookmarks;

    public function __construct(AuthManager $authManager, Client $httpClient)
    {
        $currentUser = $authManager->guard('api')->user();

        $this->bookmarks = new Bookmarks(new HttpHelper($httpClient), $currentUser);
    }

    /**
     * Returns a tree structure of the folders belonging to the current user
     */
    public function folderTree(): array
    {
        return $this->bookmarks->getSubtreeForFolder();
    }

    /**
     * Returns the details of the root folder.
     */
    public function index(): array
    {
        return [
            'folders'     => $this->bookmarks->getAllRootFolders(),
            'bookmarks'   => $this->bookmarks->getAllRootBookmarks(),
            'parent'      => null,
            'breadcrumbs' => []
        ];
    }

    /**
     * Returns the details of the specified folder.
     */
    public function show(int $id): array
    {
        $parent = $this->bookmarks->getById($id);

        if (!$parent->isFolder()) {
            throw new NotFoundHttpException;
        }

        return [
            'parent'      => $parent,
            'breadcrumbs' => $this->bookmarks->getParentsList($parent),
            'folders'     => $this->bookmarks->getFoldersByParent($id),
            'bookmarks'   => $this->bookmarks->getBookmarksByParent($id),
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): Bookmark
    {
        $typeId = $request->get('type_id');
        $url = $request->get('url');
        $name = $request->get('name');
        $parentId = $request->get('parent_id') ?: null;

        if ($parentId && !$this->bookmarks->hasAccessToFolder($parentId)) {
            throw new NotFoundHttpException('There is no such folder');
        }

        return $this->bookmarks->create($typeId, $url, $name, $parentId);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): Bookmark
    {
        if (!$this->bookmarks->hasAccessTo($id)) {
            throw new NotFoundHttpException('There is no such bookmark');
        }

        $name = $request->get('name');
        $url = $request->get('url');

        return $this->bookmarks->update($id, $url, $name);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): void
    {
        $this->bookmarks->delete($id);
    }

    public function bulkDelete(Request $request): void
    {
        $ids = $request->get('ids');

        foreach ($ids as $id) {
            $this->bookmarks->delete($id);
        }
    }

    public function bulkMove(Request $request): void
    {
        $parentId = $request->get('parentId') ?: null;
        $ids = $request->get('ids');

        if ($parentId && !$this->bookmarks->hasAccessToFolder($parentId)) {
            throw new NotFoundHttpException('There is no such folder');
        }

        foreach ($ids as $id) {
            $this->bookmarks->move($id, $parentId);
        }
    }

    /**
     * Returns the search results for the specified keyword
     */
    public function search(string $keyword): Collection
    {
        if (empty($keyword)) {
            return [];
        }

        return $this->bookmarks->search($keyword);
    }
}