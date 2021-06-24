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
namespace App\Resource;

use App\Support\Traits\Encrypter;
use Hyperf\Resource\Json\JsonResource;

class Resource extends JsonResource
{
    use Encrypter;

    protected array $withoutFields = [];

    public static function collection($resource): CustomResourceCollection
    {
        return tap(
            new CustomResourceCollection($resource, static::class),
            static function ($collection) {
                if (property_exists(static::class, 'preserveKeys')) {
                    $collection->preserveKeys = (new self([]))->preserveKeys === true;
                }
            }
        );
    }

    /**
     * 动态隐藏字段.
     *
     * @return $this
     */
    public function hide(array $fields): self
    {
        $this->withoutFields = $fields;

        return $this;
    }

    /**
     * Remove the filtered keys.
     *
     * @param $array
     *
     * @return array
     */
    protected function filterFields($array): array
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}
