<?php

namespace App\Services;

use App\Bookmark;
use App\BookmarkType;
use App\User;
use Illuminate\Database\Eloquent\Collection;

class Bookmarks
{
    /** @var HttpHelper */
    private $httpHelper;

    /** @var User */
    private $currentUser;

    public function __construct(HttpHelper $httpHelper, User $currentUser = null)
    {
        $this->httpHelper = $httpHelper;
        $this->currentUser = $currentUser;
    }

    public function getById(int $id): Bookmark
    {
        return $this->currentUser->bookmarks()->findOrFail($id);
    }

    /**
     * Returns an array containing all the parents of this folder (starting from children of the root).
     *
     * @return Bookmark[]
     */
    public function getParentsList(Bookmark $folder): array
    {
        $parents = [];

        while ($folder->parent_id !== null) {
            $parent = $folder->parent;
            $parents[] = $parent;
            $folder = $parent;
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
            ->where('type_id', BookmarkType::FOLDER)
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
     * @return Collection of Bookmark
     */
    public function getFoldersByParent(int $parentId): Collection
    {
        return $this->currentUser->bookmarks()
            ->where('parent_id', $parentId)
            ->where('type_id', BookmarkType::FOLDER)
            ->get();
    }

    /**
     * @return Collection of Bookmark
     */
    public function getBookmarksByParent(int $parentId): Collection
    {
        return $this->currentUser->bookmarks()
            ->where('parent_id', $parentId)
            ->where('type_id', BookmarkType::BOOKMARK)
            ->get();
    }

    /**
     * @return bool
     */
    public function hasAccessToFolder(int $id): bool
    {
         return $this->currentUser->bookmarks()
            ->where('id', $id)
            ->where('type_id', BookmarkType::FOLDER)
            ->exists();
    }

    /**
     * @return bool
     */
    public function hasAccessTo(int $id): bool
    {
        return $this->currentUser->bookmarks()
            ->where('id', $id)
            ->exists();
    }

    /**
     * @return Bookmark
     */
    public function create(int $typeId, string $url = null, string $name = null, int $parentId = null): Bookmark
    {
        $bookmark = new Bookmark();
        $bookmark->name = $name;
        $bookmark->parent_id = $parentId;
        $bookmark->type_id = $typeId;

        if ($bookmark->isBookmark()) {
            $bookmark->url = $url;
            $pageBody = $this->httpHelper->retrievePageBody($url);

            if (!$bookmark->name) {
                $bookmark->name = $this->httpHelper->getPageName($pageBody);
            }

            $bookmark->icon = $this->httpHelper->getIcon($url, $pageBody);
        }

        $this->currentUser->bookmarks()->save($bookmark);

        return $bookmark;
    }

    /**
     * @return Bookmark
     */
    public function update(int $id, string $url, string $name = null): Bookmark
    {
        $bookmark = $this->currentUser->bookmarks()->findOrFail($id);
        $bookmark->name = $name;

        if ($bookmark->isBookmark()) {
            $oldUrl = $bookmark->url;

            if ($url != $oldUrl) {
                $bookmark->url = $url;
                $pageBody = $this->httpHelper->retrievePageBody($url);

                if (!$bookmark->name) {
                    $bookmark->name = $this->httpHelper->getPageName($pageBody);
                }

                $bookmark->icon = $this->httpHelper->getIcon($url, $pageBody);
            }
        }

        $bookmark->save();

        return $bookmark;
    }

    /**
     * @throws \Exception
     */
    public function delete(int $id): void
    {
        $bookmark = $this->currentUser->bookmarks()->findOrFail($id);
        $bookmark->delete();
    }

    public function move(int $id, int $parentId = null): void
    {
        $bookmark = $this->currentUser->bookmarks()->findOrFail($id);
        $bookmark->parent_id = $parentId;
        $bookmark->save();
    }

    /**
     * Returns the bookmarks containing the keyword in the name field, ordered
     * by type (folders first).
     *
     * @return Collection of Bookmark
     */
    public function search(string $keyword): Collection
    {
        return $this->currentUser->bookmarks()
            ->where('name', 'like', "%{$keyword}%")
            ->orderBy('type_id', 'DESC')
            ->get();
    }

    /**
     * @return array
     */
    public function getSubtreeForFolder(int $id = null): array
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

        // TODO: maybe calculate this as part of the previous loop
        foreach ($subtree as &$tree) {
            $this->calculateRecursiveBookmarkCount($tree);
        }

        return $subtree;
    }

    /**
     * @return int
     */
    private function getBookmarkCountByParent(int $parentId): int
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
    private function calculateRecursiveBookmarkCount(array &$subtree): int
    {
        if (!is_null($subtree['nodes'])) {
            foreach ($subtree['nodes'] as $node) {
                $subtree['bookmarkCount'] += $this->calculateRecursiveBookmarkCount($node);
            }
        }

        return $subtree['bookmarkCount'];
    }
}