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