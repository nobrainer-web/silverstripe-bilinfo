# silverstripe-bilinfo
Implementation of the danish Bilinfo GET listings API

The API is very limited as it simply returns a big payload of json with all vehicle listings.

```
composer require nobrainer-web/silverstripe-bilinfo
```

#### Requirements

- SilverStripe 4
- PHP 7.3 (for better json_encode error handling)
- A user at Bilinfo

### DB fields in this module
Almost all fields saved from the API data are saved as Varchar. 
This is because pretty much everything in the json data from the API, is formatted as strings.. and we also do not now all possible values of any of the fields