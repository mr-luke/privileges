Privileges Manager - Laravel multi-roles manager Package.
==============

[![License](https://poser.pugx.org/mr-luke/settings-manager/license)](https://packagist.org/packages/mr-luke/settings-manager)

This package provides privileges manager that supports multi-roles, permissions and restrictions.

* [Getting Started](#getting-started)
* [Installation](#installation)
* [Configuration](#configuration)
* [Usage](#usage)
* [Examples](#examples)
* [Plans](#plans)

## Getting Started

Privileges Manager has been developed using `Laravel 5.5`
It's recommended to test it out before using with previous versions. PHP >= 7.1.3 is required.

## Installation

To install through composer, simply put the following in your composer.json file and run `composer update`

```json
{
    "require": {
        "mr-luke/privileges": "~1.0beta-1"
    }
}
```
Or use the following command

```bash
composer require "mr-luke/privileges"
```

Next, add the service provider to `app/config/app.php`

```
Mrluke\Privileges\PrivilegesServiceProvider::class,
```
*Note: Package is auto-discoverable!*

## Configuration

To use `Privileges` you need to setup your `Authorizable` model & allowed `scopes` in [config file](config/privileges.php):

```php
/*
|--------------------------------------------------------------------------
| Authorizable model class
|--------------------------------------------------------------------------
|
| This config specify which model class is authorizable.
|
*/

'authorizable' => \App\User::class,

/*
|--------------------------------------------------------------------------
| Available scopes
|--------------------------------------------------------------------------
|
| This config is a list of all available in application scopes.
|
*/

'scopes'   => [
    'users', 'settings',
],
```

You can also set a mapping rule that transform a given Eloquent model reference to a scope:

```php
/*
|--------------------------------------------------------------------------
| Models mapping
|--------------------------------------------------------------------------
|
| This config allows you to map all application models to specific scopes.
|
| Example:      \App\Model::class => 'scope'
|
*/

'mapping'   => [
    \App\Users::class => 'users'
],
``` 

By default `Detector` returns `bool` value in case of allowed or denied access but you can set a custom on:

```php
'allowed_value' => true,
'denied_value'  => false,
```

You can also publish config file via command:
```bash
php artisan vendor:publish
```

## Usage

### Facade

You can access to `Manager` and `Detector` using `Mrluke\Privileges\Facades` namespace.

### Contracts

`Privileges` is a packages built with Contracts. You need to implement `Mrluke\Privileges\Contracts\Authorizable` to your User model first. It is recommended to use default `Mrluke\Privilges\Extentions\Authorizable` trait combined with Contract:

```php
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Mrluke\Privileges\Contracts\Authorizable as Contract;
use Mrluke\Privileges\Extentions\Authorizable;

class User extends Authenticatable implements Contract
{
    use Authorizable, Notifiable;
}
```

### Manager - `Mrluke\Privileges\Manager`

`Manager` is a main tool that provides a simple interface to assign `Role` & `Privileges` to `Authorizable`. 

* `assignRole(Authorizable $auth, $role): void`

Allows you to assign new `Mrluke\Privileges\Models\Role` to `Authorizable`. `$role` can be one of three values: `int` in case you know `Role` id, `Role` instance or `array<int>` in case you want to assign more roles at once.

* `considerPermission(Authorizable $auth, string $scope): int`

Returns permission level for given `Authorizable` based on roles & personal permissions in a `$scope`.
*Note! Personal permissions are always on top.* 

* `considerRestriction(Authorizable $auth, string $scope): array`

Returns array of restrictions in given `$scope` like: IP or Time restrictions for a given `Authorizable`.

* `detectScope(string $model)`

Using this method you can get a `scope` that is connected with Model by mapping, eg: `\App\User::class => 'users'`.

* `getPermission(Permitable $subject, string $scope): mixed`

Returns `Mrluke\Privileges\Models\Permission` instance for a given `Authorizable` & `scope`.

* `grantPermission(Permitable $subject, string $scope, int $level): void`

Creates new `Permission` in given `scope` & `level` and assigns it to a given `Mrluke\Privileges\Contracts\Permitable`.
*Note! `Authorizable` and `Role` implements the `Permitable` contract.*

* `hasPermission(Permitable $subject, string $scope): bool`

Determine if given `Permitable` has assigned `scope` permission.

* `regainPermission(Permitable $subject, string $scope): void`

Regains a `Permission` from `Permitable`.

* `removeRole(Authorizable $auth, $role): void`

Removes a `Role` from `Authorizable`. 

### Levels

There are 5 different `Permission` level that can by apply to `Permitable`. All of them with combination of multi-roles, personal permissions, restrictions and role's levels gives you a wide range of many possibilities but let's have a look on those 5:

* 0 - No access.
* 1 - `Authorizable` can only view.
* 2 - `Authorizable` can manager & view but only existing ones.
* 3 - `Authorizable` can create & manage owns.
* 4 - `Authorizable` can manage all.

### Detector - `Mrluke\Privileges\Detector`

`Detector` is a main tool that provides an interface for detecting `Authorizable` privileges. There are 6 methods that can perform various check for you:

* `has(Model $model, string $relation = null): bool`

This method is responsible for `belongsToMany` scenario when two models are connected Many-to-many relation. By defult the name of function (relation) is detected from `$model` base class name (plural). In case you have your own convention of naming use parameter `$relation` to provide a function name. 

* `hasOrLevel(Model $model, int $min, string $relation = null): bool`

This method is combination of `has` & `level` in row.

* `level(int $min): bool`

This method detects if `Authorizable` has assigned `Permission` by role or prsonal that satisfy the condition.

* `owner(Model $model, string $foreign = null): bool`

This method is responsible for `hasOne` and `hasMany` scenarion when `Authorizable` is an owner of model by flat relation. By default the foreign key is detecting from `Authorizable` base class name (with `_id`). In case you have a different key name use parameter `$foreign` to provide a foreign key's column name. 

* `ownerOrLevel(Model $model, int $min, string $foreign = null): bool`

This method is combination of `owner` & `level` in row.

* `share(Model $model, string $modelRelation, string $relation): bool`

This method is responsible for a deeper relation scenario when `Authorizable` shares some model with a parent of `$model`. Let's imagine that you have a `User` that has many `Thread` which can have many `Reply`. Now you need to consider if it can rate `Reply` & only only an owner can do this. This is method for you.

```php
Detector::subject($auth)->share($reply, 'thread', 'threads');
```

### Subject & Scope

Detector has two methods that are required as a predefinition. Befor any check you need to set a subject:
```php
$detector->subject($authorizable);
```

In case you want to detect level you need also to specife the scope by:
```php
$detector->scope($scope);
```

## Examples

1. Let's check if user can update all posts (level 4):
```php
/**
 * Determine whether the user can update the post.
 *
 * @param  \App\Models\User  $user
 * @param  \App\Models\Post  $post
 * @return mixed
 */
public function update(User $user, Post $post)
{
    $scope = Manager::detectScope(Post::class);
    
    return Detector::subject($user)->scope($scope)->level(4);
}
```

2. Let's check if user is an owner or can update all posts (custom key):
```php
/**
 * Determine whether the user can update the post.
 *
 * @param  \App\Models\User  $user
 * @param  \App\Models\Post  $post
 * @return mixed
 */
public function update(User $user, Post $post)
{
    $scope = Manager::detectScope(Post::class);
    
    return Detector::subject($user)->scope($scope)->ownerOrLevel($post, 4, 'author_id');
}
```

*Note! `Manager` & `Detector` are singleton instances.*

## Plans

* Tests
* Auto-detection extention for Policy
* Two-factor confirmation (Manager/Director approval)
