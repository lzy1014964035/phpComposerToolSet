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

    // 拆解文件路径
    public static function explodeFilePath($filePath)
    {
        $sourceFilePathArray = strpos($filePath, '\\') !== false ? explode('\\', $filePath) : explode('/', $filePath);
        return $sourceFilePathArray;
    }

    // 拷贝临时文件
    public static function copyTmpFile($tmpName)
    {
        $newTemFile = str_replace('.tmp', '_copy.tmp', $tmpName);
        copy($tmpName, $newTemFile);
        chmod($newTemFile, 0777);
        return $tmpName;
    }
}