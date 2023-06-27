<?php


namespace ToolSet\Service;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

trait ServiceFile
{
    // 递归删除文件夹
    public static function forceDeleteDirectory($directory) {
        if (!is_dir($directory)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($directory);
    }
}