# Positron48 CommentExtension

Author: Anton Filatov

This Bolt extension can be used as comment system.

## Installation:

```bash
composer require positron48/comment-extension
```

## Usage

After installation in your admin panel /bolt you will see a new menu item called "Comments". 
There are all comments with edit ability.


To show comment list on contentpage use twig function `commentList` with content as first param:

```html
{{ commentList(content) }}
```

Also you can override default template by creating file `comment_list.html.twig` in your theme folder.