# GPX

## PSR-4 Autoloader

Any class in the GPX namespace is autoloaded.

Creating a class in the `wp-content/plugins/gpxadmin/GPX` directory under the GPX namespace will allow the class to be
autoloaded.

For example if the following class is saved in `wp-content/plugins/gpxadmin/GPX/Model/Resort.php` it will be autoloaded
and not need to be included with require_once.

```php
<?php
namespace GPX\Model;

class Resort {
 //... 
}
```

The composer autoloader is used so the `composer.json` file can be modified to change any autoloader settings.

Any composer libraries will also be autoloaded.

## Service Container

The `league/container` library is used as a PSR-11 compatible service container.

[Container Documentation](https://container.thephpleague.com/4.x/)

### Usage

To get an instance of the service container the `gpx()` function can be used.

Calling the function without any arguments will return an instance of the container itself.

```php
// returns instance of League\Container\Container
$container = gpx();
```

For example to get an instance of a request object you can call the following.

```php
use Illuminate\Http\Request;

$request = gpx(Request::class);
```

### Service Providers

To add more classes to the service container it is recommended to write a service provider.

[Service Provider Documentation](https://container.thephpleague.com/4.x/service-providers/)

Service providers can be registered in the `wp-content/gpxadmin/services.php` file in the `gpx()` function.

```php
function gpx( string $key = null, array $args = [] ) {
    static $container;
    if ( ! $container ) {
        $container = new League\Container\Container();
        $laravel_container = new LaravelContainer($container);
        $container->delegate(
            new League\Container\ReflectionContainer()
        );
        $container->add('League\Container\Container', $container);
        $container->add('Psr\Container\ContainerInterface', $container);
        $container->add('Illuminate\Container\Container', $laravel_container);
        $container->add('Illuminate\Contracts\Container\Container', $laravel_container);

        // Add any service providers here
        $container->addServiceProvider( new GPX\ServiceProvider\HttpServiceProvider() );
        $container->addServiceProvider( new GPX\ServiceProvider\EventServiceProvider() );
        $container->addServiceProvider( new GPX\ServiceProvider\DatabaseServiceProvider() );
        $container->addServiceProvider( new GPX\ServiceProvider\ValidationServiceProvider() );
        $container->addServiceProvider( new GPX\ServiceProvider\TranslationServiceProvider() );
    }
    if ( null === $key ) {
        return $container;
    }

    return $container->get( $key, $args );
}
```

#### Example Service Provider:

```php
<?php

namespace GPX\ServiceProvider;

use Symfony\Component\HttpFoundation\Request;
use League\Container\ServiceProvider\AbstractServiceProvider;

class HttpServiceProvider extends AbstractServiceProvider {

    // this function should return a boolean for if the class provides the given key
    public function provides( string $id ): bool {
        return in_array($id, [
            'request',
            Request::class
        ]);
    }

    public function register(): void {
        // using addShared will result in the instance being reused rather than recreated whenever it is requested (sort of like a singleton)
        $this->getContainer()->addShared(Request::class, function() {
            // just return an instance of the requested class
            return Request::createFromGlobals();
        });
        // you can add an alias for a given class
        $this->getContainer()->add('request', Request::class);
    }
}
```

## Request Object

[Laravel Request Object Documentation](https://laravel.com/docs/9.x/requests#main-content)  
[Symfony Request Object Documentation](https://symfony.com/doc/current/components/http_foundation.html#request)

WordPress does not have any built-in way to interact with the request and instead just relies on accessing superglobals
like $_GET and $_POST directly.

For writing cleaner, more testable code, it can be useful to use an object to represent the request so it can be passed
around as a dependency.

For this the Laravel request classes have been included and added to the service container.

The Laravel request and response objects extend the Symfony request and response objects so documentation from both is
applicable.

```php
// Pull from service container
$request = gpx(\Symfony\Component\HttpFoundation\Request::class);
$request = gpx(\Illuminate\Http\Request::class);

// pull from service container using alias
$request = gpx('request');

// pull using helper function
$request = gpx_request();
```

The easiest way to get the request object is using the `gpx_request()` function.

The helper function can also be used to quickly access request variables by passing a $key as the first parameter.

```php
// the following are the same
$var = gpx_request('id');
$var = $_REQUEST['id'];

// can also pass a default value for if the key does not exist
$var = gpx_request('id', 'default value');
```

## Response Object

[Response Object Documentation](https://symfony.com/doc/current/components/http_foundation.html#response)

Wordpress does not have any easy way of handling responses outside
of [`wp_send_json()`](https://developer.wordpress.org/reference/functions/wp_send_json/) for json responses.

Settings things like response codes or headers are just done using core php functions
like [`header()`](https://www.php.net/manual/en/function.header.php)
and [`http_response_code()`](https://www.php.net/http-response-code)

To make some of this easier the Symfony/Laravel response object can be used.

### Creating a Response

```php
use Symfony\Component\HttpFoundation\Response;

// create a response
$status = Response::HTTP_OK; // the http status code (defaults to 200)
$headers = []; // optional array of http headers
$response = new Response('response content', $status, ['content-type' => 'text/plain']);
gpx_send_response($response);

// same as
http_response_code(200);
header('Content-Type: text/plain');
echo 'response content';
exit;
```

#### Json response

WordPress has [`wp_send_json()`](https://developer.wordpress.org/reference/functions/wp_send_json/) for sending json
responses but you can also use the response object.

```php
use Symfony\Component\HttpFoundation\JsonResponse;

$response = new JsonResponse(['data to be encoded as the json body'], $status);
gpx_send_response($response);

// same as 
http_response_code($status);
header('Content-Type: application/json');
echo json_encode(['data to be encoded as the json body']);
exit;

// also same as
wp_send_json(['data to be encoded as the json body'], $status);
```

#### Http redirects

Rather than using `header('location: xxxx')` for redirecting you can send a RedirectResponse.

```php
use Symfony\Component\HttpFoundation\RedirectResponse;

$response = new RedirectResponse($url /* the url to redirect to */, $status = 302 /* generally 302 or 301 */, $headers = [] /* additional headers */);
gpx_send_response($response);

// same as 
header("location: $url");
exit;
```

### Sending the Response

A `gpx_send_response` function has been created to send the symfony responses.

The function takes a symfony request object as the first parameter.

```php
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

// send response string
gpx_send_response(new Response('response content'));

// send json response
gpx_send_response(new JsonResponse(['example' => 'data']));

// send redirect response
gpx_send_response(new RedirectResponse($url));
```

By default, the application will [exit](https://www.php.net/manual/en/function.exit.php) after sending the response but
this can be changed by passing false as a second parameter.

```php
use Symfony\Component\HttpFoundation\Response;

// the response is returned after being sent
$response = gpx_send_response(new Response('response content'), false);
```

### Helper Functions

A helper function has been added to make creating and sending responses a bit quicker

```php
// this will create a response, send it, then exit all in one call
gpx_response('response content');

// the same as
echo 'response content';
exit;
```

Another helper function for sending redirect responses has also been created

```php
// this will create a redirect response, send it, then exit all in one call
gpx_redirect($url);

// or for 301 redirect
gpx_redirect($url, 301);

// the same as
header("location: $url");
exit;

// for 301
http_response_code(301);
header("location: $url");
exit;
```

## Dates

The [Carbon](https://carbon.nesbot.com/) library has been included to make working with dates a bit easier.

[Documentation](https://carbon.nesbot.com/docs/)

## Array Helpers

The array helpers from Laravel are available from the `Illuminate\Support\Arr` class.

[Documentation](https://laravel.com/docs/9.x/helpers#arrays)

## String Helpers

The string helpers from Laravel are available from the `Illuminate\Support\Str` class.

[Documentation](https://laravel.com/docs/9.x/helpers#strings)

The Symfony string component is available for chaining string methods.

[Documentation](https://symfony.com/doc/current/components/string.html)

## Url Helper

The Spatie Url package has been added to help deal with urls.

[Documentation](https://github.com/spatie/url)

## Laravel Collections

Laravel collections are usable via `Illuminate\Support\Collection`

```php
use Illuminate\Support\Collection;
$collection = new Collection([1,2,3,4]);

$collection = collect([1,2,3,4]);
```

## Query Builder

The Laravel database component is available for making eloquent models or using the query builder.

[Query Builder](https://laravel.com/docs/9.x/queries#main-content)  
[Eloquent Models](https://laravel.com/docs/9.x/eloquent)

### Query Builder

The Laravel query builder is available using the `DB` class alias.

```php
DB::table('wp_posts')->where('post_status', '=', 'publish')->orderBy('post_date', 'desc')->->take(3)->get();
```

### Create a Model

Models should go in the GPX\Model namespace.

The `$table` and `$primaryKey` properties should be set as the database does not follow the default Laravel naming
conventions.

If the table does not have a created_at and updated_at timestamps the `$timestamps` property should be set to false.

The columns of these timestamps could be changed by setting a `CREATED_AT` and/or `UPDATED_AT` constants in the model
class. Set either to `NULL` to disable them.

```php
<?php
namespace GPX\Model;

use Illuminate\Database\Eloquent\Model;

class Post extends Model {
    protected $table = 'wp_posts';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
    protected $hidden = ['post_password'];
    protected $casts = [
        'ID' => 'integer',
        'post_author' => 'integer',
        'post_date' => 'datetime',
        'post_date_gmt' => 'datetime',
        'post_modified' => 'datetime',
        'post_modified_gmt' => 'datetime',
        'comment_count' => 'integer',
    ];
}
```

## Doctrine DBAL

In addition to the Laravel database component `doctrine/dbal` has also been included.

[Documentation](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/index.html)

The dbal connection can be accessing using the `gpx_db()` function.

The query builder is available but as the Laravel generally better it is recommended to use that one instead.

The main helpful use of the dbal library is for preparing, executing, and fetching queries in one line.

```php
// fetches a key=>value pair array [123 => 'bob', 346 => 'steve']
$users = gpx_db()->fetchAllKeyValue("SELECT `ID`,`user_login` FROM `wp_users` LIMIT 100");
```

## Request / Form Validation

The Laravel validation component is included to make form validation easier.

[Documentation](https://laravel.com/docs/9.x/validation)

This can be especially helpful for ajax requests.

```php
$rules = [
    'email' => ['required', 'email'],
    'password' => ['required', 'minlength:8'],
];
$messages = [
    'email.required' => 'Email address is required',
    'password.minlength' => 'Password must be at least 8 characters'
];
$validator = gpx_validator()->make($_POST, $rules, $messages, $attributes);
if ($validator->fails()) {
    // pull the error message bag
    $errors = $validator->errors();
}
```

### Form Classes

A form class can be created to organize validation logic into a class.

Form classes should be created in `GPX/Forms` and should extend `GPX\Forms\BaseForm`.

For validation a `rules()` method should be defined.  This method should return an array of laravel validation rules.

```php
public function rules(): array {
    return [
        'name'     => [ 'nullable', 'max:255' ],
        'email'     => [ 'required', 'email' ],
    ];
}
```

To customize error messages a `messages()` method can be defined.

This works the same way as customizing the messages for a Laravel form request.

https://laravel.com/docs/9.x/validation#customizing-the-error-messages

```php
public function messages()
{
    return [
        'title.required' => 'A title is required',
        'body.required' => 'A message is required',
    ];
}
```
To customize the attribute names replaced in default error messages an `attributes()` method can be defined.

This works the same way as customizing the attributes for a Laravel form request.

https://laravel.com/docs/9.x/validation#customizing-the-error-messages

```php
public function attributes()
{
    return [
        'email' => 'email address',
    ];
}
```

A `filters()` method can be defined to filter data after validation.

This can be used to convert values to integers, booleans, or run through a callback function.

This should be used for filtering only and not for validation rules.

This will use the `filter_var_array` function to filter the data.

Fields without defined filters will be returned without being modified.

https://www.php.net/manual/en/filter.filters.validate.php

```php
public function filters(): array {
    return [
        'remember_me' => FILTER_VALIDATE_BOOLEAN,
        'email'   => [
            'filter' => FILTER_CALLBACK,
            'options' => 'mb_strtolower'
        ],
        'resort_id' => FILTER_VALIDATE_INT,
        'date_of_birth' => [
            'filter' => FILTER_CALLBACK,
            'options' => function($value) {
                if(empty($value)) return null;
                return date_create_from_format('Y-m-d', $value);
            }
        ],
    ];
}
```

#### Usage

Once a class has been defined the `validate()` method can be called to perform the form validation.

You can pass an array of the data to validate or if not provided it will be pulled from the global request data.

By default, if the validation fails a validation error json response will automatically be sent.  This response will have a status code of 422.

You can prevent this by passing `false` as the second parameter in which case a `Illuminate\Validation\ValidationException` will be thrown instead.

This exception can be caught to get the error messages.

The `validate()` method will return an array of the validated fields.  The data will also be run through any defined filters.

```php
use \GPX\Form\FormClass;

$form = FormClass::instance();
$data = $form->validate();
var_dump($data);
```
Example Validation Failed JSON Response

https://laravel.com/docs/9.x/validation#validation-error-response-format

```json
{
    "success": false,
    "message": "Submitted data was invalid.",
    "errors": {
        "name": ["The email field is required."],
        "email": ["The email field is required."]
    }
}
```
#### Example Class

```php
<?php

namespace GPX\Form;

use Illuminate\Validation\Rule;

class CustomForm extends BaseForm {
    public function rules(): array {
        return [
            'name'     => [ 'nullable', 'max:255' ],
            'username'     => [ 'required', 'max:60', Rule::unique('wp_users', 'user_login') ],
            'email'     => [ 'required', 'email' ],
            'adults'     => [ 'required', 'integer', 'min:0' ],
            'remember_me'   => [ 'required', 'boolean' ],
            'date'   => [ 'required', 'date_format:Y-m-d' ],
            'role'   => [ 'required', Rule::in( [ 'admin', 'owner', 'partner' ] ) ],
            'resort_id'   => [ 'required', Rule::exists('wp_resorts', 'id') ],
        ];
    }

    public function attributes(): array {
        return [
            'resort_id'      => 'resort',
        ];
    }

    public function filters(): array {
        return [
            'adults' => FILTER_VALIDATE_INT,
            'remember_me' => FILTER_VALIDATE_BOOLEAN,
            'email' => [
                'filter' => FILTER_CALLBACK,
                'options' => 'mb_strtolower'
            ],
            'resort_id' => FILTER_VALIDATE_INT,
            'date' => [
                'filter' => FILTER_CALLBACK,
                'options' => function($value) {
                    if(empty($value)) return null;
                    return date_create_from_format('Y-m-d', $value);
                }
            ],
        ];
    }
}
```

### Custom Validators

Custom validation rules cna be created by creating a class that implements the `Illuminate\Contracts\Validation\Rule` interface

[Documentation](https://laravel.com/docs/9.x/validation#custom-validation-rules)

```php
<?php
namespace GPX\Rules;
 
use Illuminate\Contracts\Validation\Rule;
 
class Uppercase implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return strtoupper($value) === $value;
    }
 
    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be uppercase.';
    }
}
```

## Event Dispatcher

WordPress does have basic event handling using `add_action()` and `do_action()` or `add_filter()` and `apply_filters()`
but sometimes a class-based event dispatcher might be more appropriate.

The Laravel event dispatcher component is available.

The event dispatcher is available using the `gpx_event` function.

```php
// get event dispatcher, then fire the event
$dispatcher = gpx_event();
$dispatcher->dispatch($event);

// fire an event
gpx_event($event);
```

### Create event class

The event class does not need to extend any base class or implement any interfaces.

For organization purposes it is recommended to put event classes under the `GPX\Event` namespace.

```php
class HelloWorldEvent {
    public string $name;
    public function __construct(string $name){
      $this->name = $name;
    }
}
```

### Creating an Event Listener

The class does not need to extend any base class or implement any interfaces other than it must have a public `handle()`
method that accepts the event it is listening to as a parameter.

Any parameters in the constructor will be pulled from the service container.

For organization purposes it is recommended to put event classes under the `GPX\Listener` namespace.

```php
class HelloWorldListener {
    public function handle(HelloWorldEvent $event){
        echo "Hello {$event->name}";
    }
}
```

### Register Event Listeners

The easiest way to register an event listener is to add it to the `$events` array in the `EventServiceProvider`.

This should be an array with the event you are listening for as the keys and an array of the listeners as the value

```php
protected $events = [
    EventClass::class => [
        EventListener::class,
        SecondEventListener::class,
    ],
    HelloWorldEvent::class => [
        HelloWorldListener::class,
    ]
];
```
