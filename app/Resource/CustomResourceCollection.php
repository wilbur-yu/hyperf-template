<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Resource;

use Hyperf\Resource\Json\ResourceCollection;
use Hyperf\Utils\Collection;
use JetBrains\PhpStorm\ArrayShape;

class CustomResourceCollection extends ResourceCollection
{
    protected array $hidden = [];
    private bool $hide = true;

    public function hide(array $fields): CustomResourceCollection
    {
        $this->hidden = $fields;

        return $this;
    }

    public function show(array $fields): CustomResourceCollection
    {
        $this->hidden = $fields;
        $this->hide = false;

        return $this;
    }

    #[ArrayShape(['data' => Collection::class])]
    public function toArray(): array
    {
        $data = $this->collection->map(function ($item) {
            if (!$this->hide) {
                return collect($item)->only($this->hidden)->all();
            }

            return collect($item)->except($this->hidden)->all();
        });

        return [
            'data' => $data->isNotEmpty() ? $data->toArray() : [],
        ];
    }
}
