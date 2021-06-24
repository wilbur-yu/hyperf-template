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

use Hyperf\Resource\Json\ResourceCollection;

class CustomResourceCollection extends ResourceCollection
{
    /**
     * The name of the resource being collected.
     *
     * @var string
     */
    public $collects;

    protected array $withoutFields = [];

    /**
     * Create a new anonymous resource collection.
     *
     * @param mixed $resource
     */
    public function __construct($resource, string $collects)
    {
        $this->collects = $collects;

        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(): array
    {
        return $this->processCollection();
    }

    public function hide(array $fields): self
    {
        $this->withoutFields = $fields;

        return $this;
    }

    /**
     * 将隐藏字段通过 Resource 处理集合.
     */
    protected function processCollection(): array
    {
        return $this->collection->map(function (Resource $resource) {
            return $resource->hide($this->withoutFields)->toArray();
        })->all();
    }
}
