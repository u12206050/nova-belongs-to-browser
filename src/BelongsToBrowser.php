<?php

namespace Day4\BelongsToBrowser;

use Laravel\Nova\Fields\Field;
use Illuminate\Support\Facades\Hash;

class BelongsToBrowser extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'belongs-to-browser';

    /**
     * All the options available and required
     *
     * @param string  $resource The resource endpoint to be requested
     * @param string  $title The field name to use as title
     * @param string  $image The field name containing the src to an image
     * @param string  $orderby The field name to order results by
     * @param string  $direction The direction to order by
     * @return $this
     */
    public function config(string $resource, string $title = 'title', string $image = 'image')
    {
        return $this->withMeta([
            'resource' => $resource,
            'title' => $title,
            'image' => $image,
            'ck' => Hash::make("$resource:-:$title:-:$image")
        ]);
    }

    /**
     * Specifiy field and direction to order results by
     */
    public function order(string $orderby = 'updated_at', string $direction = 'desc') {
        return $this->withMeta([
            'orderby' => $orderby,
            'direction' => $direction
        ]);
    }

    /**
     * Specifiy preset values to be used to filter and create new resource
     */
    public function filter(string $field, string $value) {
        return $this->withMeta([
            'filter' => [
                "$field" => $value
            ]
        ]);
    }

    /**
     * Specifiy an optional field that results should be grouped and filtered in.
     */
    public function group(string $groupBy, array $groupOptions) {
        return $this->withMeta([
            'group' => [
                'by' => $groupBy,
                'options' => $groupOptions
            ]
        ]);
    }
}
