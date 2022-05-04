# Webinar
Package enabling live video stream

[![swagger](https://img.shields.io/badge/documentation-swagger-green)](https://escolalms.github.io/Webinar/)
[![codecov](https://codecov.io/gh/EscolaLMS/Webinar/branch/main/graph/badge.svg?token=NRAN4R8AGZ)](https://codecov.io/gh/EscolaLMS/Webinar)
[![phpunit](https://github.com/EscolaLMS/Webinar/actions/workflows/test.yml/badge.svg)](https://github.com/EscolaLMS/Webinar/actions/workflows/test.yml)
[![downloads](https://img.shields.io/packagist/dt/escolalms/webinar)](https://packagist.org/packages/escolalms/webinar)
[![downloads](https://img.shields.io/packagist/v/escolalms/webinar)](https://packagist.org/packages/escolalms/webinar)
[![downloads](https://img.shields.io/packagist/l/escolalms/webinar)](https://packagist.org/packages/escolalms/webinar)
[![Maintainability](https://api.codeclimate.com/v1/badges/0c9e2593fb30e2048f95/maintainability)](https://codeclimate.com/github/EscolaLMS/Webinar/maintainability)

## What does it do

This package is used for creating Webinar for EscolaLms.

## Installing

- `composer require escolalms/webinar`
- `php artisan migrate`
- `php artisan db:seed --class="EscolaLms\Webinar\Database\Seeders\WebinarsPermissionSeeder"`
- Integration with [Youtube](https://github.com/EscolaLMS/Youtube)

## Schedule

- In App\Console\Kernel to method schedule add 
  - `$schedule->job(new ReminderAboutWebinarJob(WebinarTermReminderStatusEnum::REMINDED_HOUR_BEFORE))->everyFiveMinutes()` - reminder about to webinar before one hour, executed every 5 minutes
  - `$schedule->job(new ReminderAboutWebinarJob(WebinarTermReminderStatusEnum::REMINDED_DAY_BEFORE))->everySixHours();` - reminder about to webinar before one day, executed every 6 hours

## Endpoints

All the endpoints are defined in [![swagger](https://img.shields.io/badge/documentation-swagger-green)](https://escolalms.github.io/Webinar/)

## Tests

Run `./vendor/bin/phpunit --filter=Webinar` to run tests. See [tests](tests) folder as it's quite good staring point as documentation appendix.

Test details [![codecov](https://codecov.io/gh/EscolaLMS/Webinar/branch/main/graph/badge.svg?token=NRAN4R8AGZ)](https://codecov.io/gh/EscolaLMS/Webinar) [![phpunit](https://github.com/EscolaLMS/Webinar/actions/workflows/test.yml/badge.svg)](https://github.com/EscolaLMS/Webinar/actions/workflows/test.yml)

## Events

- `EscolaLms\Webinar\Events\ReminderAboutTerm` => Event is dispatched after execute cron job `EscolaLms\Webinar\Jobs\ReminderAboutWebinarJob`, Event is dispatched when deadline for purchased webinars before 1 hours and 1 day
- `EscolaLms\Webinar\Events\WebinarTrainerAssigned` => Event is dispatched after assigned trainer to webinar
- `EscolaLms\Webinar\Events\WebinarTrainerUnassigned` => Event is dispatched after unassigned trainer from webinar

## Listeners

- `EscolaLms\Webinar\Listeners\ReminderAboutTermListener` => Listener execute a method that singed the status in the webinar reminder

## How to use this on frontend.

### Admin panel

**Left menu**

![Menu](docs/menu.png "Menu")

**List of webinars**

![List of webinars](docs/list.png "List of webinars")

**Creating/editing webinar**

![Creating/editing webinars](docs/new_webinar.png "Creating or editing webinars")

### Front Application

...

## Permissions

Permissions are defined in [seeder](vendor/escolalms/webinar/database/seeders/WebinarsPermissionSeeder.php)

## Database relation

1. `Trainers` Webinar is related belongs to many with User
2. `Tags` Webinar model morph many to model tags
3. `Users` Webinar is related belongs to many with User which bought webinar
```
Webinar 1 -> n User
Webinar 1 -> n Tags
Webinar 1 -> n User
```
