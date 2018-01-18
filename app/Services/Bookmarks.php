<?php

namespace App\Services;

use App\Bookmark;
use App\BookmarkType;
use App\User;
use Illuminate\Database\Eloquent\Collection;

class Bookmarks
{
    /** @var User */
    private $currentUser;

    public function __construct($currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function getById($id): Bookmark
    {
        return $this->currentUser->bookmarks()->findOrFail($id);
    }

    /**
     * Returns an array containing all the parents of this folder (starting from children of the root).
     *
     * @param Bookmark $bookmark
     * @return Bookmark[]
     */
    public function getParentsList($bookmark): array
    {
        $parents = [];

        while ($bookmark->parent_id !== null) {
            $parent = $bookmark->parent;

            $parents[] = $parent;
            $bookmark = $parent;
        }

        return $parents;
    }

    /**
     * @return Collection of Bookmark
     */
    public function getAllRootFolders(): Collection
    {
        return $this->currentUser->bookmarks()
            ->whereNull('parent_id')
            ->where('type_id', BookmarkType::FOLDER) // TODO: scope? Same below
            ->get();
    }

    /**
     * @return Collection of Bookmark
     */
    public function getAllRootBookmarks(): Collection
    {
        return $this->currentUser->bookmarks()
            ->whereNull('parent_id')
            ->where('type_id', BookmarkType::BOOKMARK)
            ->get();
    }

    /**
     * @param int $parentId
     * @return Collection of Bookmark
     */
    public function getFoldersByParent($parentId): Collection
    {
        return $this->currentUser->bookmarks()
            ->where('parent_id', $parentId)
            ->where('type_id', BookmarkType::FOLDER)
            ->get();
    }

    /**
     * @param int $parentId
     * @return Collection of Bookmark
     */
    public function getBookmarksByParent($parentId): Collection
    {
        return $this->currentUser->bookmarks()
            ->where('parent_id', $parentId)
            ->where('type_id', BookmarkType::BOOKMARK)
            ->get();
    }

    /**
     * @param int $id
     * @return bool
     */
    public function hasAccessToFolder($id): bool
    {
         return $this->currentUser->bookmarks()
            ->where('id', $id)
            ->where('type_id', BookmarkType::FOLDER)
            ->exists();
    }

    /**
     * @param int $id
     * @return bool
     */
    public function hasAccessTo($id): bool
    {
        return $this->currentUser->bookmarks()
            ->where('id', $id)
            ->exists();
    }

    /**
     * @param int|null $parentId
     * @param int $typeId
     * @param string $name
     * @param string $url
     * @return Bookmark
     */
    public function create($parentId, $typeId, $name, $url = null): Bookmark
    {
        $bookmark = new Bookmark();
        $bookmark->name = $name;
        $bookmark->parent_id = $parentId;
        $bookmark->type_id = $typeId;

        if (!$bookmark->isFolder()) {
            $bookmark->url = $url;
        }

        $this->currentUser->bookmarks()->save($bookmark);

        return $bookmark;
    }

    /**
     * @param $id
     * @param $name
     * @param $url
     * @return Bookmark
     */
    public function update($id, $name, $url = null): Bookmark
    {
        $bookmark = $this->currentUser->bookmarks()->findOrFail($id);
        $bookmark->name = $name;

        if (!$bookmark->isFolder()) {
            $bookmark->url = $url;
        }

        $bookmark->save();

        return $bookmark;
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function delete($id)
    {
        $bookmark = $this->currentUser->bookmarks()->findOrFail($id);
        $bookmark->delete();
    }

    /**
     * @param int $id
     * @param int $parentId
     */
    public function move($id, $parentId)
    {
        $bookmark = $this->currentUser->bookmarks()->findOrFail($id);
        $bookmark->parent_id = $parentId;
        $bookmark->save();
    }

    /**
     * Returns the bookmarks containing the keyword in the name field, ordered
     * by type (folders first).
     *
     * @param string $keyword
     * @return Bookmark[]
     */
    public function search($keyword): array
    {
        return $this->currentUser->bookmarks()
            ->where('name', 'like', "%{$keyword}%")
            ->orderBy('type_id', 'DESC')
            ->get();
    }

    /**
     * @param int|null $id
     * @return array
     */
    public function getSubtreeForFolder($id = null): array
    {
        $subtree = [];

        if (empty($id)) {
            $folders = $this->getAllRootFolders();
        } else {
            $folders = $this->getFoldersByParent($id);
        }

        foreach ($folders as $folder) {
            $subtree[] = [
                'folderId' => $folder->id,
                'text' => $folder->name,
                'nodes' => $this->getSubtreeForFolder($folder->id) ?: null,
                'bookmarkCount' => $this->getBookmarkCountByParent($folder->id),
            ];
        }

        // TODO: maybe calculate this as part of the previous function
        foreach ($subtree as &$tree) {
            $this->calculateRecursiveBookmarkCount($tree);
        }

        return $subtree;
    }

    /**
     * @param int $parentId
     * @return int
     */
    private function getBookmarkCountByParent($parentId): int
    {
        return $this->currentUser->bookmarks()
            ->where('type_id', BookmarkType::BOOKMARK)
            ->where('parent_id', $parentId)
            ->count();
    }

    /**
     * @param array $subtree This array gets modified by this function
     * @return int
     */
    private function calculateRecursiveBookmarkCount(&$subtree): int
    {
        if (!is_null($subtree['nodes'])) {
            foreach ($subtree['nodes'] as $node) {
                $subtree['bookmarkCount'] += $this->calculateRecursiveBookmarkCount($node);
            }
        }

        return $subtree['bookmarkCount'];
    }
}