# WP Before & After Plugin 

This is a plugin specifically designed to showcase before and after forest clearing photos in Natura 2000 sites across the EU. This plugin requires special modifications to wp-config and is keyed to a specific custom template, so it may not work for other uses. However, I chose to upload the code anyway, in case aspects of the plugin itself are interesting or useful to other scientists and WordPress developers. 

## Features

- Two custom post types (Before&After and Natura 2000 Sites)
- Leaflet integration, showing a polygon and a point 
- Before/after sliding image revealer with dates on the bottom left and right corners
- Simple archive page in the style of the theme
- Integration into the FacetWP search system used in the current theme

## Future Features

- Adding raster overlays of forest disturbance to the posts
- Moving the site polygon to the correct custom post type
- Adding additional supplementary information from the Natura 2000 database
- Adding a single page view for Natura 2000 Sites
- Adding supplementary information on target species from the IUCN and Natura 2000 databases
- Adding sample files to the repository

## Installation Instructions
 - Install and activate the plugin
 - Modify FacetWP settings. 
 - Add a new Facet called `location filters for beforeafters`
    - Data source is locations, count is -1, soft limit is 8. 
 - Add a new Listing called    `template for ba locations`
    - in Query arguments, make sure the code matches:
    
```php
<?php
return [
  "post_type" => [
    "beforeafter"
  ],
	"facetwp" => true,
  "post_status" => [
    "publish"
  ],
  "posts_per_page" => 10
];
```

If you want the Before&Afters to show in the resource library, 
you'll need to edit /templates/resources.php in the theme. Add
'beforeafters' to the list of post types in the query. 