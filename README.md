# drupal-advanced-content-field
Provide a custom field with several template to replace the classic « body » field within an entity

## Installation

## Add github repository
```
composer config repositories.drupal-advanced-content-field vcs https://github.com/kgaut/drupal-advanced-content-field
```
### Download via composer
```
composer require kgaut/advanced_content_field
```

### Patchs
In order to get states working, you'll need theses patchs on drupal/core :
  - https://www.drupal.org/files/issues/2018-08-12/997826-states-doesnt-work-with-format-16.patch (Related issue : https://www.drupal.org/project/drupal/issues/997826)
  - https://www.drupal.org/files/issues/2019-09-03/states_not_affecting-2847425-39.patch (Related issue : https://www.drupal.org/node/2847425)
