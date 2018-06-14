<?php

namespace App\Services;

class FileHelper
{
    public function read($path)
    {
        return file_get_contents($path);
    }
}