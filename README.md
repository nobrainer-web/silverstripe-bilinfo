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

### API Credentials
The BilInfo API requires a username and password. You must set these in `.env` with these variables:

- **SS_BILINFO_USER**
- **SS_BILINFO_PASSWORD**

Example:

- **SS_BILINFO_USER='demo'**
- **SS_BILINFO_PASSWORD='ocfB6XzF73'**

### Build Tasks
To pull down data from the BilInfo API and save it, you can use `GetApiDataTask`. This task should probably be run as a cron job, once a day.

Then you would use `GetSinceDaysDataTask` every hour or so, to get latest updated API data. The `?sincedays` param is by default set 1.

### Deletion
A Listing is "Sold" when the `ExternalDeletedDate` field is set.

By default automatic clean up of sold listings is enabled. This can be disabled on `Listings::$enabled_automatic_cleanup`.

Run `dev/tasks/bi-cleanup-listings-task` to clean up listings. It will check the `Listings::$deletion_after_days_sold` setting to decide if a Listing should be deleted or not

### A note on DB fields in this module
All fields from the API are strings. Some of these fields are mapped to INT instead.

### TODO
- Translations and proper strings for equipment names
- Support for Video/360 images
- Support to output other sizes of listing image urls