# Mautic Badge Generator for Mautic

Plugin from Mautic Extension family ([https://mtcextendee.com](https://mtcextendee.com/))

Download 7 ultimate free Mautic email themes https://mtcextendee.com/themes/

In general, plugin modify any PDF and add new text custom slots and barcode to PDF. Useful for your events.

### Features

- Add with custom text slots to PDF
    - Custom contact fields
    - Custom font-size, color and position
- Add barcode to PDF
    - Require https://github.com/mtcextendee/mautic-barcode-generator-bundle
    - Any custom contact field to generate Barcode
    - Custom position and width/height of barcode
 - Add QRcode to PDF
    - Require https://github.com/mtcextendee/mautic-barcode-generator-bundle
    - Any custom contact field to generate QRcode
    - Custom position and width/height of barcode, color etc.
- Token support - use link to badge in email/page (`{badge=1}`)
- Add to stage after download generate PDF
- Generate custom PDF for each contacts from contact list
- Display badge generator bundle just for contact with certain tags

## Installation

#### Installation from command line:

1. `composer require mtcextendee/mautic-badge-generator-bundle`
1. `php app/console mautic:plugins:reload`

Manual installation is not allowed because plugins depend on another setasign/fpdi-tcpdf, which is installed automatically from command line.

## Setup

Just go to plugins and enable new BadgeGenerator integration. Then you should see new column in left menu.

![image](https://user-images.githubusercontent.com/462477/55947007-fee26d80-5c4d-11e9-8e07-47bf08b3b4fa.png)

### Generate PDF

In contact list

![image](https://user-images.githubusercontent.com/462477/55949170-4c60d980-5c52-11e9-8c77-d7db28b38330.png)

### Before

![image](https://user-images.githubusercontent.com/462477/55948833-9d240280-5c51-11e9-8222-8d9f8a61476a.png)

### After

![image](https://user-images.githubusercontent.com/462477/55949107-25a2a300-5c52-11e9-9a7d-8e84bcb4f851.png)

### API

Library: https://github.com/mautic/api-library

Example for Basic Authorization:
```php
// Bootup the Composer autoloader
include __DIR__.'/vendor/autoload.php';

use Mautic\Auth\ApiAuth;

$apiUrl = 'http://mautic.site';
$settings = [
    'userName' => 'admin',
    'password' => 'mautic',
];
$initAuth = new ApiAuth();
$auth     = $initAuth->newAuth($settings, 'BasicAuth');
$api = new \Mautic\Api\Api($auth, $apiUrl);
$contactId = 1;
$badgeId = 2;
$response = $api->makeRequest(sprintf("badge/generator/%d/%d", $contactId, $badgeId));
if (isset($response['pdf'])) {
$result = base64_decode($response['pdf']);
// Write on file
file_put_contents('test.pdf', $result);
echo 'PDF created';
} elseif (isset($response['errors'][0]['message'])) {
echo $response['errors'][0]['message'];
}
```

Response:

- If badge exist for contact

`$response['pdf'] = 'base64 encode of PDF content';`

You need base64decode of PDF content and then process it (dispay or save..). For more info check: https://selectpdf.com/web-html-to-pdf-rest-api-for-php-samples/


- If badge or contact not exist:

`$response['errors']`


