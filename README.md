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

For validation a `rules()` method should be defined. This method should return an array of laravel validation rules.

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

By default, if the validation fails a validation error json response will automatically be sent. This response will have
a status code of 422.

You can prevent this by passing `false` as the second parameter in which case
a `Illuminate\Validation\ValidationException` will be thrown instead.

This exception can be caught to get the error messages.

The `validate()` method will return an array of the validated fields. The data will also be run through any defined
filters.

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
        "name": [
            "The email field is required."
        ],
        "email": [
            "The email field is required."
        ]
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

Custom validation rules cna be created by creating a class that implements the `Illuminate\Contracts\Validation\Rule`
interface

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

## Admin Routing

There are two admin url routers.

Routes should eventually be converted to the new router but the old router can still be used for now.

### Old Admin Routing

The first using a standard WordPress admin url with the page query parameter set to `gpx-admin-page` and the `gpx-pg`
query parameter set to the name of the route.

This example route would have the url of `/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=route_url`.

When accessing a route using this url style it will automatically be rendered inside the admin layout. This type of url
will thus only work for full html responses and not json or html partial responses.

Like any of the core WordPress routing, controllers using these routes should print any output rather than return it as
a response.

By the time these routes are accessed WordPress has already rendered the html `<head>` so it is too late to add any
styles or scripts using `wp_enqueue_script` or `wp_enqueue_style`.

By default, the "controllers" for these routes will be methods in the `GpxAdmin` class found in `wp-content/plugins/gpxadmin/dashboard/functions/class.gpxadmin.php`.

#### Admin Controller Method

To find the method name based on the `$page` variable from the  `gpx-pg` query parameter, the following rules are used:

If the `$page` has no underscores the method name will be the same as the `$page` parameter.

For example `/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=something` will call the `something` on the `GpxAdmin` class.

If the `$page` parameter is `{something}_all` then the method name will be `{something}`.

For example `/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=transactions_all` will call the `transactions` on the `GpxAdmin` class.

If the first part of the `$page` parameter before the underscore ends with an `s`, the `s` will be stripped off and underscores will also be stripped out.

For example `/wp-admin/admin.php?page=gpx-admin-page&gpx-pg={somethings}_view` will call the `somethingview` on the `GpxAdmin` class.

If the `$page` parameter does not end in `s` then the method will just be the `$page` parameter with underscores stripped out.

For example `/wp-admin/admin.php?page=gpx-admin-page&gpx-pg={something}_view` will call the `somethingview` on the `GpxAdmin` class.

Multiple underscores are not supported.

If there is a `id` query parameter in the url this will be passed in as the first parameter to the method.

The method must return an array of data to be passed to the template.

#### Admin Template File

Admin templates are in `wp-content/plugins/gpxadmin/dashboard/templates/admin`.

To determine the admin template file to use, the following rules are used:

If the `$page` parameter has no underscores the template file will be `{page}.php`.

For example `/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=something` will use the `wp-content/plugins/gpxadmin/dashboard/templates/admin/something.php` template.

If the `$page` parameter is `{something}_all` then the template file will be `{something}/{something}.php`.

For example `/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=customrequests_all` will use the `wp-content/plugins/gpxadmin/dashboard/templates/admin/customrequests/customrequests.php` template.

For other urls that contain an underscore such as `{a}_{b}` the template file will be `wp-content/plugins/gpxadmin/dashboard/templates/admin/{a}/{a}{b}.php`.

For example `/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_add` will use the `wp-content/plugins/gpxadmin/dashboard/templates/admin/room/roomadd.php` template.

If the first part of the `$page` parameter before the underscore ends with an `s`, the `s` will be stripped off from the template filename but not the directory.

For example `/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=tradepartners_view` will use the `wp-content/plugins/gpxadmin/dashboard/templates/admin/tradepartners/tradepartnerview.php` template.

### New Admin Routing

Admin routes can be defined in the `wp-content/plugins/gpxadmin/routes/gpxadmin.php` file.

Routes are added using the `add()` method on the `$routes` instance in the file.

The first parameter is the name of the route and the second is the controller class or callback function.

The route name should be unique and may contain lower-case letters, numbers, and underscores.

```php
$router->add('route_url', AddResortController::class);
```

If the route has any url parameters they can be defined as an array as the third parameter.

```php
$router->add('route_url', AddResortController::class, ['id']);
```

The defined parameters will be passed in as arguments to the controller method or callback function.

Any other method parameters will be pulled from the service container.

### Alternative Route urls

New admin routes can also be accessed from the url `/gpxadmin/{something}/`. This url style will work for any type of response,
including html or json.

Any `/` characters in the endpoint will be converted to `_` characters so `/gpxadmin/transactions/all/` will match a
route with the name `transactions_all`.

These routes should be used as a replacement for core WordPress ajax callbacks using `/wp-admin/admin-ajax.php`.

Any routes under the `gpxadmin` prefix will require admin access.

The responses for these routes can be json by using `wp_send_json()` or returning an array or a `JsonResponse` instance.

Redirects can be done using the `wp_redirect()` function or by returning a `RedirectResponse` instance.

If a string is returned it will be rendered as html with a 200 status code.

Any Symfony response instance can also be returned.

### Admin Controllers

Admin controllers should be created in the `GPX\Admin\Controller` namespace.

Any dependencies in the constructor will automatically be pulled from the service container.

Method dependencies will also be pulled from the service container and any url parameters defined in the route will be
passed in as arguments.

## Rendering Templates

Templates are located in `wp-content/plugins/gpxadmin/dashboard/templates`.

They can be rendered using the `gpx_admin_view()` function.

They are stored in the `wp-content/plugins/gpxadmin/dashboard/templates/admin` directory.

They are rendered using the `gpx_admin_view()` function.

This function can render php templates or blade templates.

It will check for a blade template first and if it does not exist it will check for a php template.

This function will automatically add the `.php` extension if it is not provided.

```php
// this will render `wp-content/plugins/gpxadmin/dashboard/templates/a/b.blade.php`, then if not found will render `wp-content/plugins/gpxadmin/dashboard/templates/admin/a/b.php`
gpx_admin_view('a/b');
```

Any variables can be passed to the template as an array as the second parameter.

```php
gpx_admin_view('a/b', ['transaction' => $transaction]);
```

By default, the template will automatically be printed. Passing `false` as the third parameter will instead return the
rendered template as a string.

```php
// render a blade template for use in an email
$message = gpx_admin_view('email/invoice', ['transaction' => $transaction], false);
```

### Blade Templates

Blade templates can be used by adding the `.blade.php` extension to the template file.

[Blade Documentation](https://laravel.com/docs/9.x/blade)

They can be rendered using the `gpx_render_blade()` function.

For templates to be used in gpxadmin, the `admin::` namespace should be used.

For templates to be used in the theme, the `theme::` namespace should be used.

```php
// render a blade template for the front end
// this will render `wp-content/plugins/gpxadmin/dashboard/templates/a/b.blade.php`
gpx_render_blade('a.b');

// render a blade template for the admin
// this will render `wp-content/plugins/gpxadmin/dashboard/templates/admin/a/b.blade.php`
gpx_render_blade('admin::a.b');

// render a blade template from the theme
// this will render `wp-content/themes/gpx_new/templates/a/b.blade.php`
gpx_render_blade('theme::a.b');

// render a partial blade template from the theme
// this will render `wp-content/themes/gpx_new/template-parts/a/b.blade.php`
gpx_render_blade('partial::a.b');

// render an email blade template
// this will render `wp-content/plugins/gpxadmin/dashboard/templates/email/a/b.blade.php`
gpx_render_blade('email.a.b');
```

Any variables can be passed to the template as an array as the second parameter.

```php
gpx_render_blade('admin::a.b', ['transaction' => $transaction]);
```

By default, the template will automatically be printed. Passing `false` as the third parameter will instead return
a `View` instance.

The instance can be returned from a controller method or turned into a string using the `render()` method.

```php
// render a blade template for use in an email
$message = gpx_render_blade('email::invoice', ['transaction' => $transaction], false);
$body = $message->render();
```

#### Clearing Blade Cache

Blade cache is stored in `wp-content/gpx-cache/view`.

The blade cache can be cleared using the following command from the project root.

```bash
php console cache:clear:view
```

## Ajax Routing

WordPress has a built-in ajax handler that can be used for ajax requests.

```php
// should start with `gpx_` to prevent namespace collisions.
function gpx_callback_function() {
    // do stuff
    
    // send json response
    wp_send_json(['success' => true]);
}
// needed if ajax endpoint is accessible to logged-in users
add_action( "wp_ajax_gpx_callback_function", "gpx_callback_function" );
// needed if ajax endpoint is accessible to non-logged in users
add_action( "wp_ajax_nopriv_gpx_callback_function", "gpx_callback_function" );
```

This callback will have the url `/wp-admin/admin-ajax.php?action=gpx_callback_function`. 

### Custom Ajax Routing

Due to the limitations of these core WordPress ajax functions custom ajax endpoints can also be used.

These endpoints can be created be defining a function with the name `gpx_endpoint_{endpoint_name}` for front-end accessible endpoints or `gpxadmin_endpoint_{endpoint_name}` for gpxadmin endpoints.

Endpoints in gpxadmin will automatically require admin access.

The urls for these endpoints will be `/gpx/endpoint_name` from front-end or `/gpxadmin/endpoint_name` from gpxadmin.

Any slashes in the url will be replaced with underscores so `/gpxadmin/transaction/delete` will match the function `gpxadmin_endpoint_transaction_delete`.

Unlike the core WordPress ajax functions, these endpoints can return any type of response including json, html, or a redirect.

Also unlike core WordPress ajax functions, these callbacks should return the response rather than printing it.

#### Ajax Controllers

For gpxadmin endpoints only, controllers can be used instead of plain functions.

The controller routes can be registered in the `wp-content/plugins/gpxadmin/routes/gpxadmin.php` file in the same way as the html routes.

This allows controllers for both html pages and ajax callbacks to be defined in the same place and work the same way.

See the routing section above for more details.
