<?php

namespace App\Services;

use App\Bookmark;
use App\BookmarkType;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ChromeImporter
{
    /** @var FileHelper */
    private $fileHelper;

    public function __construct(FileHelper $fileHelper)
    {
        $this->fileHelper = $fileHelper;
    }

    public function import(UploadedFile $file, int $userId): bool
    {
        $success = true;

        try {
            $fileContent = $this->fileHelper->read($file->getRealPath());
            $fileContent = $this->sanitize($fileContent);

            $crawler = new Crawler($fileContent);
            $topItems = $crawler->filterXPath('html/body/dl/dl')->children();

            $this->importFolder($topItems, $userId);
        } catch (\Exception $e) {
            $success = false;
        }

        return $success;
    }

    /**
     * @param \DOMNodeList $items
     */
    private function importFolder($items, int $userId, int $folderId = null)
    {
        $folderName = null;

        foreach ($items as $item) {
            switch ($item->nodeName) {
                // <a> contains the actual bookmarks
                case 'a':
                    $this->addBookmark($userId, $item->nodeValue, $item->getAttribute('href'), $item->getAttribute('icon'), $folderId);
                    break;

                // subfolders are represented by a <h3> (containing the folder name), followed by a <dl> (containing the folder contents)
                case 'h3':
                    $folderName = $item->nodeValue;
                    break;

                case 'dl':
                    $newFolderId = $this->addFolder($userId, $folderName, $folderId);
                    $this->importFolder($item->childNodes, $userId, $newFolderId);

                    $folderName = null;
                    break;
            }
        }
    }

    private function addBookmark(int $userId, string $name, string $url, string $icon, $parentId = null): void
    {
        if ($this->bookmarkExistsForUser($url, $userId)) {
            return;
        }

        $bookmark = new Bookmark();
        $bookmark->type_id = BookmarkType::BOOKMARK;
        $bookmark->user_id = $userId;
        $bookmark->name = $name;
        $bookmark->url = $url;
        $bookmark->icon = $icon;
        $bookmark->parent_id = $parentId;

        $bookmark->save();
    }


    private function addFolder(int $userId, string $name, int $parentId = null): int
    {
        $folder = new Bookmark();
        $folder->type_id = BookmarkType::FOLDER;
        $folder->user_id = $userId;
        $folder->name = $name;
        $folder->parent_id = $parentId;

        $folder->save();

        return $folder->id;
    }

    /**
     * Removes some unwanted tags from the input and returns the result.
     */
    private function sanitize(string $text): string
    {
        return strtr($text, [
            '<p>' => '',
            '<DT>' => '',
        ]);
    }

    private function bookmarkExistsForUser(string $url, int $userId): bool
    {
        return Bookmark::where('user_id', $userId)
            ->where('url', $url)
            ->count() > 0;
    }
}