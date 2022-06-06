# Positron48 CommentExtension

Author: Anton Filatov

This Bolt extension can be used as comment system.

## Installation:

```bash
composer require positron48/bolt-simple-comments
php bin/console doctrine:migrations:migrate
php bin/console cache:clear --no-warmup
```

Command `cache:clear` needs to refresh admin pages list.

If you want to use Google recaptcha enterprise - get the api key via [this guide](https://cloud.google.com/recaptcha-enterprise/docs/set-up-non-google-cloud-environments-api-keys?hl=en_US) and next steps:

* go to https://console.cloud.google.com/apis/credentials and create new service account
* select to your service account role `reCaptcha Enterprise Agent`
* go to service account and create new JSON key
* store credentials in /config/extensions/service-account-recaptcha-credentials.json 
  or change variable `GOOGLE_APPLICATION_RECAPTHA_CREDENTIALS` in your .env file. 
  Default value is `../config/extensions/service-account-recaptcha-credentials.json`


Add to .env file some parameters:

```dotenv
RECAPTCHA_KEY=
GOOGLE_API_KEY=
GOOGLE_PROJECT_ID=
GOOGLE_APPLICATION_RECAPTHA_CREDENTIALS=../config/extensions/service-account-recaptcha-credentials.json
```

`RECAPTCHA_KEY` - Key Id of [your recaptcha enterprise](https://console.cloud.google.com/security/recaptcha) key

`GOOGLE_API_KEY` - Api key from section 'API Keys' in [Credentials](https://console.cloud.google.com/apis/credentials) page

`GOOGLE_PROJECT_ID` - Project Id of your project in [Google Cloud Platform](https://console.cloud.google.com/iam-admin/settings)

`GOOGLE_APPLICATION_RECAPTHA_CREDENTIALS` - path to your json file with credentials.


## Usage

After installation in your admin panel /bolt you will see a new menu item called "Comments". 
There are all comments with edit ability.


To show comment list on contentpage use twig function `commentList` with content as first param:

```html
{{ commentList(content) }}
```

Also you can override default template by creating file `comment_list.html.twig` in your theme folder.