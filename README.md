# Relationship Media Browser

Display items with a title and an image from a relationship to attach to the resource.


## Usage


**BelongsToBrowser**

config(string $resource, string $title = 'title', string $image = 'image', string $orderby = 'updated_at', string $direction = 'desc')


### Examples

```
use Day4\BelongsToBrowser\BelongsToBrowser;

...

      BelongsToBrowser::make(__('Featured Image'), 'image_id')
        ->config('images','alt','src')
        ->group('ratio', \App\Statics\ImageRatios::Options())
        ->filter('ratio', '16:9'),
```

## Roadmap

 * [x] Browser
 * [x] Creator
 * [x] Filter and Preset value
 * [ ] Group By
 * [ ] Multiple Select