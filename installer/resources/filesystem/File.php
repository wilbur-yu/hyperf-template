<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Support;

use App\Kernel\Log\Log;
use Exception;
use Hyperf\Filesystem\FilesystemFactory;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;

class File
{
    /**
     * [将Base64图片转换为本地图片并保存].
     *
     * @param $base64ImageContent
     * @param $pathFileName
     *
     * @throws \League\Flysystem\FilesystemException
     * @return string
     */
    public static function uploadBase64Image($base64ImageContent, $pathFileName): string
    {
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64ImageContent, $result)) {
            $filesystem = make(Filesystem::class);
            try {
                ## 文件上传
                $filesystem->write($pathFileName, base64_decode(str_replace($result[1], '', $base64ImageContent)));
            } catch (Exception $e) {
                Log::get('file')->error(sprintf('%s [%s] %s', '图片上传失败', date('Y-m-d H:i:s'), $e->getMessage()));
                Log::get('file')->error($e->getTraceAsString());
            }
        }

        return $pathFileName;
    }

    /**
     * url图片上传.
     *
     * @param $url
     * @param $pathFileName
     *
     * @throws \League\Flysystem\FilesystemException
     * @return string
     */
    public static function uploadUrlImage($url, $pathFileName): string
    {
        $filesystem = make(Filesystem::class);
        $stream = file_get_contents($url, true);
        try {
            $filesystem->write($pathFileName, $stream);
        } catch (Exception $e) {
            Log::get('file')->error(sprintf('%s [%s] %s', '图片上传失败', date('Y-m-d H:i:s'), $e->getMessage()));
            Log::get('file')->error($e->getTraceAsString());
        }

        return $pathFileName;
    }

    /**
     * 下载远程文件.
     *
     * @param  string  $url
     * @param  string  $localPath
     *
     * @throws \League\Flysystem\FilesystemException
     * @return string
     */
    public static function download(string $url, string $localPath): string
    {
        $fileSystem = make(FilesystemFactory::class)->get('local');
        $fileSystem->write($localPath, file_get_contents($url));
        $root = config('file.storage.local.root');

        return realpath(rtrim($root, '/').'/'.$localPath);
    }

    /**
     * 生成完整的文件名.
     *
     * @param  string  $extension
     * @param  string  $path
     *
     * @return string
     */
    public static function generateFullFilename(string $extension, string $path = ''): string
    {
        if (empty($path)) {
            $path = date('Y/md/Hi');
        }

        $filename = (microtime(true) * 10000).uniqid('', true).'.'.$extension;
        $pathFileName = $path.'/'.$filename;

        return ltrim($pathFileName, '/');
    }

    protected static function logger(): LoggerInterface
    {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get('file');
    }
}
