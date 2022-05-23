# Positron48 CommentExtension

Author: Anton Filatov

This Bolt extension can be used as comment system.

## Installation:

```bash
composer require positron48/comment-extension
```

If you want to use Google recaptcha enterprise - get the api key via [this guide](https://cloud.google.com/recaptcha-enterprise/docs/set-up-non-google-cloud-environments-api-keys?hl=en_US) and next steps:

* go to https://console.cloud.google.com/apis/credentials and create new service account
* select to your service account role `reCaptcha Enterprise Agent`
* go to service account and create new JSON key
* store credentials in /config/extensions/service-account-recaptcha-credentials.json 
  or change variable `GOOGLE_APPLICATION_RECAPTHA_CREDENTIALS` in your .env file. 
  Default value is `../config/extensions/service-account-recaptcha-credentials.json`



## Usage

After installation in your admin panel /bolt you will see a new menu item called "Comments". 
There are all comments with edit ability.


To show comment list on contentpage use twig function `commentList` with content as first param:

```html
{{ commentList(content) }}
```

Also you can override default template by creating file `comment_list.html.twig` in your theme folder.