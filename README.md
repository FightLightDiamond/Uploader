# Uploader
This package makes it easy to build project

## Postcardware
You're free to use this package (it's MIT-licensed), but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.
- Author: Fight Light Diamond <i.am.m.cuong@gmail.com>
- MIT: 2e566161fd6039c38070de2ac4e4eadd8024a825

## Requires
- Laravel 5.x

## Install
You can install the package via composer:
`composer require cuongpm/uploader`

## Usage
The service provider will automatically get registered. Or you may manually add the service provider in your config/app.php file:

```
'providers' => [
    // ...
    Uploader\UploaderServiceProvider::class,
];
```

You can publish the migration with:
```angular2html
php artisan vendor:publish
```

Command config upload: 
 ```angular2html
php artisan maker:uploader NameUpload
 ```
## Example for user
Config upload
```angular2html
namespace App\Uploads;


use Cuongpm\Uploader\UploadModel;

class UserUpload extends UploadModel
{
  // 0 => file, 1 =>images
    public $fileUpload = ['avatar' => 1];
    
  // path for upload

    public $pathUpload = ['avatar' => '/images/users'];
    
  // thumb for images, auto high or with if null 
    public $thumbImage = [
        'avatar' => [
        // thumbs size
            '/thumbs/' => [
                [200, 200], [300, NULL], [NULL, 400]
            ]
        ]
    ];
}
```
User register upload User.php
 ```angular2html
use UploadAble;
  
 public function modelUploader()
{
    return UserUpload::class;
}
```

Use to code

```
$input = request()->only('avatar');
$user = User::first(); // or $user = new User();
//upload no save
$input = $user->uploader($input);
//save
$user->fill($input);
$user->save();

// upload and save
$user->uploaderSave($input);

// get image
$user->getImage('avatar');
//get thumbs
$user->getThumbPath('avatar', [200, 200])
```
