# Cloudbeds

The Cloudbeds plugin allows users to connect their WordPress website to their Cloudbeds data using the [Cloudbeds API](https://www.cloudbeds.com/features/api/).

![PHP version](https://img.shields.io/badge/PHP-7.4+-4F5B93.svg?logo=php)
![WP version](https://img.shields.io/badge/WordPress-6.0+-0073aa.svg?&logo=wordpress)
[![GitHub release](https://img.shields.io/github/v/release/MGPelloni/cloudbeds.svg?logo=github)](https://github.com/MGPelloni/cloudbeds/releases/latest)
[![CI status](https://github.com/MGPelloni/cloudbeds/actions/workflows/ci.yml/badge.svg)](https://github.com/MGPelloni/cloudbeds/actions/workflows/ci.yml)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![semantic-release: angular](https://img.shields.io/badge/semantic--release-angular-e10079?logo=semantic-release)](https://github.com/angular/angular/blob/main/CONTRIBUTING.md#-commit-message-format)

## Installation

Download the Cloudbeds plugin and install it on your WordPress site. Once the plugin is activated, API integration instructions are located in "Settings -> Cloudbeds" within the WordPress administration sidebar.

After the integration is complete, developers can interact with the API utilizing `cloudbeds_api_get` and `cloudbeds_api_post`.

```
// @link https://hotels.cloudbeds.com/api/docs/#api-Room-getRoomTypes
$room_types = cloudbeds_api_get('getRoomTypes');

if ($room_types) {
    foreach ($room_types as $room_type) {
        echo esc_html($room_type['roomTypeName']);
    }
}
```

## CLI Commands

- `wp cloudbeds data` retrieves all stored data relevant to accessing Cloudbeds. 
- `wp cloudbeds new-token` will request a new access token from Cloudbeds.
- `wp cloudbeds reset` will delete all Cloudbeds plugin data.