# Relationship Browser

Display items with a title and an image from a relationship to attach to the resource.

## Setup

composer require day4/belongs-to-browser

## Usage


**BelongsToBrowser**

config(string $resource, string $title = 'title', string $image = 'image')

order(string $orderby = 'updated_at', string $direction = 'desc')

filter(string $field, string $value)

group(string $groupBy, array $groupOptions)


### Example Field for images resource

```
use Day4\BelongsToBrowser\BelongsToBrowser;

...

      BelongsToBrowser::make(__('Featured Image'), 'image_id')
        ->config('images','alt','src')
        ->group('ratio', \App\Statics\ImageRatios::Options())
        ->filter('ratio', '16:9'),
```

![](/screens/field.png)
![](/screens/browser.png)
![](/screens/creator.png)

## Roadmap

 * [x] Browser
 * [x] Creator
 * [x] Filter and Preset value
 * [ ] Group By
 * [ ] Multiple Select