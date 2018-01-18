<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Bookmarks;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookmarksController extends Controller
{
    /** @var Bookmarks */
    private $bookmarks;

    public function __construct(AuthManager $authManager)
    {
        $this->bookmarks = new Bookmarks($authManager->guard('api')->user());
    }

    /**
     * Returns a tree structure of the folders belonging to the current user
     *
     * @return array
     */
    public function folderTree()
    {
        return $this->bookmarks->getSubtreeForFolder();
    }

    /**
     * Returns the details of the root folder.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
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
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $name = $request->get('name');
        $parentId = $request->get('parent_id') ?: null;
        $typeId = $request->get('type_id');
        $url = $request->get('url');

        if ($parentId && !$this->bookmarks->hasAccessToFolder($parentId)) {
            throw new NotFoundHttpException('There is no such folder');
        }

        return $this->bookmarks->create($parentId, $typeId, $name, $url);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!$this->bookmarks->hasAccessTo($id)) {
            throw new NotFoundHttpException('There is no such bookmark');
        }

        $name = $request->get('name');
        $url = $request->get('url');

        return $this->bookmarks->update($id, $name, $url);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->bookmarks->delete($id);
    }

    /**
     * @param Request $request
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->get('ids');

        foreach ($ids as $id) {
            $this->bookmarks->delete($id);
        }
    }

    /**
     * @param Request $request
     */
    public function bulkMove(Request $request)
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
     *
     * @param string $keyword
     * @return array
     */
    public function search($keyword)
    {
        if (empty($keyword)) {
            return [];
        }

        return $this->bookmarks->search($keyword);
    }
}