## Video URL Parser
Easily extract information about a video from its URL.

This Drupal 8 module detects the hosting service from a video's URL and is able to retrieve the video's ID, thumbnail and embed URL to use in an iframe. 

Using this [gist](https://gist.github.com/astockwell/11055104#file-videourlparser-class-php) as a starting point, this module currently supports YouTube and Vimeo URLs. It also registers a Twig extension to easily get video information from within a Twig template.

### Usage
From a PHP file (Controller/Form/etc...):
```php
// Use the parser class
use Drupal\video_url_parser\VideoUrlParser;

$vid_parser = new VideoUrlParser();
$my_video = $vid_parser->parse(VIDEO_URL); // Pass in the video URL
```

From a Twig template:
```twig
{% set url = VIDEO_URL %} // Pass in the video URL
{% set vid = url|video_url %} // Use the video_url filter to invoke the parser
```

On success, the parser will return an array containing the information extracted:
```php
$vid = [
  'type' => "youtube",
  'thumb' => "http://img.youtube.com/vi/w3jLJU7DT5E/mqdefault.jpg",
  'embed' => "http://youtube.com/embed/w3jLJU7DT5E?autoplay=1"
];
``` 
