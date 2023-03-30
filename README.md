
# Easy Crud

Laravel package for automating simple Crud  by providing required operation for creating curd api in lighting speed.




## Installation

Install easy-crud using following command:

```bash
  composer require prolaxu/easy-curd
```
You can find this package in packagist: [prolaxu/easy-curd](https://packagist.org/packages/prolaxu/easy-curd)
    
## How to use?

It is very simple to use as you just have to use  as shown below:


Suppose i have Post model, resource,request and controller i can make quick curd as shown below:

#### Step 1:
Use Curd on model:
```bash
...
use Prolaxu\EasyCrud\Traits\Crud;
...
class ModelName extends Model
{
    use Crud;
    ...
}
```

example:
```bash
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Prolaxu\EasyCrud\Traits\Crud;

class Post extends Model
{
    use HasFactory,Crud;

    protected $table = 'posts';
    protected $fillable = [
        'title',
        'body',
        'slug',
        'status',
    ];
}


```
#### Step 2:
create request and resource for more control:
request: for request rule.
resource: for return rule on response.

```bash
php artisan make:resource PostResponse
php artisan make:resource Post/CreateRequest
php artisan make:resource Post/UpdateRequest
```


#### Step 3:
Create Controller.

example:
```bash
<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\UpdateRequest;
use App\Http\Requests\Post\CreateRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Prolaxu\EasyCrud\Controllers\BaseController;

class PostController extends BaseController
{
    public function __construct(){
        parent::__construct(
            Post::class,//model
            PostResource::class, // response
            CreateRequest::class, // create request
            UpdateRequest::class, // update request
        );
    }
}


```
#### Step 4:
Create Route:
```bash
Route::controller( PostController::class)->prefix('posts')->group(function (){
    Route::get('', 'index'); //list of items
    Route::post('', 'store'); // store the item
    Route::get('{id}', 'show');  //show one item
    Route::put('{id}', 'update'); // update one item
    Route::delete('delete', 'delete'); //delete multiple items
    Route::delete('{id}', 'destroy'); //delete one item
});

```
## License

[MIT](https://choosealicense.com/licenses/mit/)


## Authors

- [@prolaxu](https://www.github.com/prolaxu)

