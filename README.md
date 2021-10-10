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

    // This property is optional and allows each user's files to be saved in a separate folder.
    protected $user_directory = true;

    public function coverUrl()
    {
        return asset(Storage::url($this->cover));
    }

```

### Change upload path
By default, files will be uploaded to the `public` directory, but you can change it by adding the `$upload_path` property to your model.

```php
protected static $upload_path = 'public';
```