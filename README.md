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
No listings will ever be automatically deleted from the database. They will only be marked with `ExternalDeletedDate`.

This would give you the power to decide for yourself how long you want to keep deleted (sold listings) data.

### A note on DB fields in this module
All fields from the API are strings. Some of these fields are mapped to INT instead.

### TODO
- Translations and proper strings for equipment names
- Support for Video/360 images
- Support to output other sizes of listing image urls