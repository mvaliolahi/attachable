## Attach file to Laravel Eloquent

### Installation

```bash
$ composer require mvaliolahi/attachable
```

```php
class Post extends Model
{
    use Attachable;

    protected $attachable = [
        'image',
        'cover'
    ];

    public function coverUrl()
    {
        return asset(Storage::url($this->cover));
    }

```

### Change upload path
By default, files will be uploaded to the `public` directory, but you can change it by adding the `$upload_path` property to your model.

```php
protected $upload_path = 'public';
```

### Separate files for each user
This property is optional and allows each user's files to be saved in a separate folder.

```php
protected $user_directory = true;
```