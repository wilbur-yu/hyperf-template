<?php

declare(strict_types = 1);
/**
 * This file is part of project hyperf-template.
 *
 * @author   wenber.yu@creative-life.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Kernel\Utils\Packer;

use Hyperf\Contract\PackerInterface;

class PhpSerializerPacker implements PackerInterface
{
    public function pack($data): string
    {
        return is_numeric($data) && ! in_array($data, [INF, -INF], true)
                                 && ! is_nan((float) $data) ? $data : serialize($data);
    }

    public function unpack(string $data)
    {
        return is_numeric($data) ? $data : unserialize($data);
    }
}
