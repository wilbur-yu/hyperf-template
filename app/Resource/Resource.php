<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Resource;

use Hyperf\Resource\Json\JsonResource;

class Resource extends JsonResource
{
    protected array $hidden = [];
    private bool $hide = true;

    public static function collection($resource): CustomAnonymousResourceCollection
    {
        return tap(
            new CustomAnonymousResourceCollection($resource, static::class),
            static function ($collection) {
                if (property_exists(static::class, 'preserveKeys')) {
                    $collection->preserveKeys = (new static([]))->preserveKeys;
                }
            }
        );
    }

    /**
     * 动态隐藏字段.
     *
     * @return $this
     */
    public function makeHidden(array $fields): Resource
    {
        $this->hidden = $fields;

        return $this;
    }

    public function makeVisible(array $fields): Resource
    {
        $this->hidden = $fields;
        $this->hide = false;

        return $this;
    }

    protected function filterFields($array): array
    {
        $action = $this->hide ? 'except' : 'only';

        return collect($array)->$action($this->hidden)->toArray();
    }
}
