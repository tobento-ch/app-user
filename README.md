# App User

User support for the app with authentication and authorization.

## Table of Contents

- [Getting Started](#getting-started)
    - [Requirements](#requirements)
- [Documentation](#documentation)
    - [App](#app)
    - [User Boot](#user-boot)
        - [User Config](#user-config)
        - [User Repositories And Factories](#user-repositories-and-factories)  
        - [User Authentication And Authenticator](#user-authentication-and-authenticator)      
        - [Retrieve Current User](#retrieve-current-user)
        - [Retrieve Authenticated User](#retrieve-authenticated-user)
        - [Authenticate User](#authenticate-user)
        - [Unauthenticate User](#unauthenticate-user)
    - [Acl Boot](#acl-boot)
        - [Adding Roles](#adding-roles)
        - [Adding Rules](#adding-rules)
        - [Authorize User By Permission](#authorize-user-by-permission)
        - [Authorize User By Role](#authorize-user-by-role)
    - [Http User Error Handler Boot](#http-user-error-handler-boot)
    - [Middleware](#middleware)
        - [User Middleware](#user-middleware)
        - [Authentication Middleware](#authentication-middleware)
        - [Authentication With Middleware](#authentication-with-middleware)
        - [Authenticated Middleware](#authenticated-middleware)
        - [Unauthenticated Middleware](#unauthenticated-middleware)
        - [Verified Middleware](#verified-middleware)
        - [Verify Permission Middleware](#verify-permission-middleware)
        - [Verify Role Middleware](#verify-role-middleware)
    - [Authenticator](#authenticator)
        - [Identity Authenticator](#identity-authenticator)
        - [Attributes Authenticator](#attributes-authenticator)
        - [User Verifier](#user-verifier)
            - [User Role Verifier](#user-role-verifier)
            - [User Role Area Verifier](#user-role-area-verifier)
        - [Token Authenticator](#token-authenticator)
        - [Token Verifier](#token-verifier)
            - [Token Password Hash Verifier](#token-verifier)
            - [Token Payload Verifier](#token-payload-verifier)
    - [Token Storage](#token-storage)
        - [Null Storage](#null-storage)
        - [In Memory Storage](#in-memory-storage)
        - [Repository Storage](#repository-storage)
        - [Session Storage](#session-storage)
    - [Token Transport](#token-transport)
        - [Cookie Transport](#cookie-transport)
        - [Header Transport](#header-transport)
    - [Events](#events)
    - [Commands](#commands)
    - [Migration](#migration)
        - [Role Permissions Action](#role-permissions-action)
    - [Learn More](#learn-more)
        - [Different Authentication Per Routes](#different-authentication-per-routes)
        - [Password Hashing](#password-hashing)
        - [User Channel Verification](#user-channel-verification)
    - [App User Bundles](#app-user-bundles)
- [Credits](#credits)
___

# Getting Started

Add the latest version of the app user project running this command.

```
composer require tobento/app-user
```

## Requirements

- PHP 8.0 or greater

# Documentation

## App

Check out the [**App Skeleton**](https://github.com/tobento-ch/app-skeleton) if you are using the skeleton.

You may also check out the [**App**](https://github.com/tobento-ch/app) to learn more about the app in general.

## User Boot

The user boot does the following:

* installs and loads user config file
* user and role repositories implementation based on config
* adds middleware for authentication and authorization based on config

```php
use Tobento\App\AppFactory;

// Create the app
$app = (new AppFactory())->createApp();

// Adding boots
$app->boot(\Tobento\App\User\Boot\User::class);

// Run the app
$app->run();
```

### User Config

The configuration for the user is located in the ```app/config/user.php``` file at the default App Skeleton config location.

### User Repositories And Factories

The following repositories and factories are available as defined in the ```app/config/user.php``` config file. The default repository implementation uses the ```Tobento\Service\Storage\StorageInterface::class``` as the storage, defined in the ```app/config/database.php``` file, which is the file storage by default:

```php
use Tobento\App\AppFactory;
use Tobento\App\User\UserRepositoryInterface;
use Tobento\App\User\UserFactoryInterface;
use Tobento\App\User\AddressRepositoryInterface;
use Tobento\App\User\AddressFactoryInterface;
use Tobento\App\User\RoleRepositoryInterface;
use Tobento\App\User\RoleFactoryInterface;
use Tobento\Service\Repository\RepositoryInterface;

$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\User\Boot\User::class);
$app->booting();

// User:
$userRepository = $app->get(UserRepositoryInterface::class);
// var_dump($userRepository instanceof RepositoryInterface);
// bool(true)

$userFactory = $app->get(UserFactoryInterface::class);

// Address:
$addressRepository = $app->get(AddressRepositoryInterface::class);
// var_dump($addressRepository instanceof RepositoryInterface);
// bool(true)

$addressFactory = $app->get(AddressFactoryInterface::class);

// Role:
$roleRepository = $app->get(RoleRepositoryInterface::class);
// var_dump($roleRepository instanceof RepositoryInterface);
// bool(true)

$roleFactory = $app->get(RoleFactoryInterface::class);

// Run the app:
$app->run();
```

You may check out the [**Repository Interface**](https://github.com/tobento-ch/service-repository#repository-interface) to learn more about it.

### User Authentication And Authenticator

The following authentication and authenticator interfaces are available as defined in the ```app/config/user.php``` config file by default.

```php
use Tobento\App\AppFactory;
use Tobento\App\User\Authentication\AuthInterface;
use Tobento\App\User\Authentication\Token\TokenStoragesInterface;
use Tobento\App\User\Authentication\Token\TokenStorageInterface;
use Tobento\App\User\Authentication\Token\TokenTransportsInterface;
use Tobento\App\User\Authentication\Token\TokenTransportInterface;
use Tobento\App\User\Authenticator\TokenAuthenticatorInterface;
use Tobento\App\User\Authenticator\UserVerifierInterface;

$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\User\Boot\User::class);
$app->booting();

// Auth:
$auth = $app->get(AuthInterface::class);

// Token storages:
$tokenStorages = $app->get(TokenStoragesInterface::class);

// Default token storage:
$tokenStorage = $app->get(TokenStorageInterface::class);

// Token transports:
$tokenTransports = $app->get(TokenTransportsInterface::class);

// Default token transport:
$tokenTransport = $app->get(TokenTransportInterface::class);

// Default token authenticator:
$tokenAuthenticator = $app->get(TokenAuthenticatorInterface::class);

// Default user verifier:
$userVerifier = $app->get(UserVerifierInterface::class);

// Run the app:
$app->run();
```

### Retrieve Current User

The current user will be available by the ```ServerRequestInterface::class``` after the process of the ```Tobento\App\User\Middleware\User::class``` defined in the ```app/config/user.php``` config file.

```php
use Tobento\App\AppFactory;
use Tobento\App\User\UserInterface;
use Tobento\Service\User\UserInterface as ServiceUserInterface;
use Tobento\Service\Acl\Authorizable;
use Psr\Http\Message\ServerRequestInterface;

$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Http\Boot\Routing::class);
$app->boot(\Tobento\App\User\Boot\User::class);
$app->booting();

// Routes:
$app->route('GET', 'user', function(ServerRequestInterface $request) {
    
    // Get the current user (may be authenticated or not):
    $user = $request->getAttribute(UserInterface::class);

    // var_dump($user instanceof UserInterface);
    // bool(true)
    
    // var_dump($user instanceof ServiceUserInterface);
    // bool(true)
    
    // var_dump($user instanceof Authorizable);
    // bool(true)
    
    // Check if authenticated:
    $user->isAuthenticated();
    // bool(false)
    
    return $user?->toArray();
});

// Run the app:
$app->run();
```

### Retrieve Authenticated User

The authenticated user will be available by the ```ServerRequestInterface::class``` after the process of the ```Tobento\App\User\Middleware\Authentication::class``` defined in the ```app/config/user.php``` config file.

```php
use Tobento\App\AppFactory;
use Tobento\App\User\UserInterface;
use Tobento\App\User\Authentication\AuthInterface;
use Tobento\App\User\Authentication\AuthenticatedInterface;
use Tobento\App\User\Authentication\Token\TokenInterface;
use Tobento\Service\User\UserInterface as ServiceUserInterface;
use Tobento\Service\Acl\Authorizable;
use Psr\Http\Message\ServerRequestInterface;

$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Http\Boot\Routing::class);
$app->boot(\Tobento\App\User\Boot\User::class);
$app->booting();

// Routes:
$app->route('GET', 'user', function(ServerRequestInterface $request) {
    
    // Get the auth:
    $auth = $request->getAttribute(AuthInterface::class);
    // var_dump($auth instanceof AuthInterface);
    // bool(true)
    
    // You may check if user is authenticated:
    if ($auth->hasAuthenticated()) {
        return null;
    }
    
    // Get authenticated:
    $authenticated = $auth->getAuthenticated();
    // var_dump($auth->getAuthenticated() instanceof AuthenticatedInterface);
    // bool(true)
    
    // Get the authenticated user:
    $user = $authenticated->user();
    // var_dump($user instanceof UserInterface);
    // bool(true)
    
    // var_dump($user instanceof ServiceUserInterface);
    // bool(true)
    
    // var_dump($user instanceof Authorizable);
    // bool(true)
    
    // Get the authenticated token:
    $token = $authenticated->token();
    // var_dump($token instanceof TokenInterface);
    // bool(true)

    // Get the authenticated via:
    // var_dump($authenticated->via());
    // string(9) "loginlink"
    
    // Get the authenticated by (authenticator class name):
    // var_dump($authenticated->by());
    // string(52) "Tobento\App\User\Authenticator\IdentityAuthenticator"
    
    return $user;
});

// Run the app:
$app->run();
```

### Authenticate User

There are many ways to authenticate a user depending on your ```app/config/user.php``` config file configuration.

**General authentication workflow:**

```php
use Tobento\App\User\UserRepositoryInterface;
use Tobento\App\User\Authentication\AuthInterface;
use Tobento\App\User\Authentication\Authenticated;
use Tobento\App\User\Authentication\Token\TokenStorageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class AuthController
{
    public function authenticate(
        ServerRequestInterface $request,
        UserRepositoryInterface $userRepository,
        AuthInterface $auth,
        TokenStorageInterface $tokenStorage,
    ): ResponseInterface {
        
        // You may check if user is authenticated:
        // Or use the \Tobento\App\User\Middleware\Unauthenticated::class to do so.
        if ($auth->hasAuthenticated()) {
            // create and return response if is already authenticated.
        }
        
        // authenticate user manually:
        $user = $userRepository->findById(1);
        
        if (is_null($user)) {
            // create and return response if user does not exist.
        }
        
        // create token and start auth:
        $token = $tokenStorage->createToken(
            // Set the payload:
            payload: ['userId' => $user->id(), 'passwordHash' => $user->password()],

            // Set the name of which the user was authenticated via:
            authenticatedVia: 'login.auth', // The name is up to you.
            
            // Set the name of which the user was authenticated by (authenticator name) or null if none:
            authenticatedBy: null,
            
            // Set the point in time the token has been issued or null (now):
            issuedAt: new \DateTimeImmutable('now'),
            
            // Set the point in time after which the token MUST be considered expired or null:
            // The time might depend on the token storage e.g. session expiration!
            expiresAt: new \DateTimeImmutable('now +10 minutes'),
        );
        
        $auth->start(new Authenticated(token: $token, user: $user));
        
        // create and return response:
        return $createdResponse;
    }
}
```

**You may use an [authenticator](#authenticator) to authenticate a user:**

See [Identity Authenticator - Login Controller Example](#identity-authenticator) for instance.

### Unauthenticate User

To unauthenticate a user call the ```close``` method from the ```AuthInterface::class```:

```php
use Tobento\App\User\Authentication\AuthInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class AuthController
{
    public function unauthenticate(
        AuthInterface $auth,
    ): ResponseInterface {
        
        $auth->close();
        
        // create and return response:
        return $createdResponse;
    }
}
```

## Acl Boot

The acl boot does the following:

* implements acl interface and set roles

The acl boot gets booted automatically by the [User Boot](#user-boot)!

```php
use Tobento\App\AppFactory;
use Tobento\Service\Acl\AclInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Adding boots

// The acl boot gets booted automatically by the user boot:
// $app->boot(\Tobento\App\User\Boot\Acl::class);

$app->boot(\Tobento\App\User\Boot\User::class);

$acl = $app->get(AclInterface::class);

// Run the app
$app->run();
```

You may check out the [**Acl Service**](https://github.com/tobento-ch/service-acl) to learn more about it.

### Adding Roles

The acl boot will add all roles found from the ```Tobento\App\User\RoleRepositoryInterface::class```. You can add roles by the following ways:

**Using a migration**

```php
use Tobento\Service\Migration\MigrationInterface;
use Tobento\Service\Migration\ActionsInterface;
use Tobento\Service\Migration\Actions;
use Tobento\Service\Repository\Storage\Migration\RepositoryAction;
use Tobento\Service\Repository\Storage\Migration\RepositoryDeleteAction;
use Tobento\App\User\RoleRepositoryInterface;

class RolesStorageMigration implements MigrationInterface
{
    public function __construct(
        protected RoleRepositoryInterface $roleRepository,
    ) {}
    
    /**
     * Return a description of the migration.
     *
     * @return string
     */
    public function description(): string
    {
        return 'User roles.';
    }
        
    /**
     * Return the actions to be processed on install.
     *
     * @return ActionsInterface
     */
    public function install(): ActionsInterface
    {
        return new Actions(
            RepositoryAction::newOrNull(
                repository: $this->roleRepository,
                description: 'Default roles',
                items: [
                    ['key' => 'guest', 'areas' => ['frontend'], 'active' => true],
                    ['key' => 'registered', 'areas' => ['frontend'], 'active' => true],
                    ['key' => 'business', 'areas' => ['frontend'], 'active' => true],
                    ['key' => 'developer', 'areas' => ['backend'], 'active' => true],
                    ['key' => 'administrator', 'areas' => ['backend'], 'active' => true],
                    ['key' => 'editor', 'areas' => ['backend'], 'active' => true],
                ],
            ),
        );
    }

    /**
     * Return the actions to be processed on uninstall.
     *
     * @return ActionsInterface
     */
    public function uninstall(): ActionsInterface
    {
        return new Actions();
    }
}
```

Then [Install The Migration](https://github.com/tobento-ch/app-migration#install-and-uninstall-migration) using the app migration.

**Using In Memory Storage**

You may use the In Memory Storage instead of the default defined in the ```app/config/user.php``` config file:

```php
//...

User\RoleRepositoryInterface::class => static function(ContainerInterface $c) {

    $storage = new InMemoryStorage([
        'roles' => [
            1 => [
                'key' => 'guest',
                'areas' => ['frontend'],
                'active' => true,
            ],
        ],
    ]);
            
    return new User\RoleStorageRepository(
        storage: $storage,
        table: 'roles',
        entityFactory: $c->get(User\RoleFactoryInterface::class),
    );
},

//...
```

You may verify roles by the [Verify Role Middleware](#verify-role-middleware).

### Adding Rules

By default, no rules are added. You might add rules by the following ways:

**Using the app**

```php
use Tobento\App\AppFactory;
use Tobento\Service\Acl\AclInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Adding boots

// The acl boot gets booted automatically by
// the user boot:
// $app->boot(\Tobento\App\User\Boot\Acl::class);

$app->boot(\Tobento\App\User\Boot\User::class);

$acl = $app->get(AclInterface::class);

$acl->rule('articles.read')
    ->title('Article Read')
    ->description('If a user can read articles');

// Run the app
$app->run();
```

**Using the acl boot**

```php
use Tobento\App\Boot;
use Tobento\App\User\Boot\Acl;

class AnyServiceBoot extends Boot
{
    public const BOOT = [
        // you may ensure the acl boot.
        Acl::class,
    ];
    
    public function boot(Acl $acl)
    {
        $acl->rule('articles.read')
            ->title('Article Read')
            ->description('If a user can read articles');
    }
}
```

Permissions will be verified by the [Verify Permission Middleware](#verify-permission-middleware).

Check out the [**Rules**](https://github.com/tobento-ch/service-acl#rules) to learn more about it.

### Authorize User By Permission

There are several ways to authorize a user to access resources by checking its permission.

You may check out the [Acl - Permissions](https://github.com/tobento-ch/service-acl#permissions) section to learn more about it.

**Using the acl**

```php    
use Tobento\Service\Acl\AclInterface;

class ArticleController
{
    public function index(AclInterface $acl): string
    {
        if ($acl->cant('articles.read')) {
            return 'can not read';
        }

        return 'can read';
    }
}
```

**Using the user**

```php
use Tobento\App\User\UserInterface;
use Psr\Http\Message\ServerRequestInterface;

class ArticleController
{
    public function index(ServerRequestInterface $request): string
    {
        $user = $request->getAttribute(UserInterface::class);
        
        if ($user->cant('articles.read')) {
            return 'can not read';
        }

        return 'can read';
    }
}
```

**Using middleware**

Use the [Verify Permission Middleware](#verify-permission-middleware) to verify permission on routes.

### Authorize User By Role

**Using the acl**

```php    
use Tobento\Service\Acl\AclInterface;

class ArticleController
{
    public function index(AclInterface $acl): string
    {
        $user = $this->acl->getCurrentUser();
        
        if (
            $user
            && $user->hasRole()
            && $user->role()->key() !== 'editor'
        ) {
            return 'can not read';
        }
        
        // or
        if ($user?->getRoleKey() !== 'editor') {
            return 'can not read';
        }

        return 'can read';
    }
}
```

**Using the user**

```php    
use Tobento\App\User\UserInterface;
use Psr\Http\Message\ServerRequestInterface;

class ArticleController
{
    public function index(ServerRequestInterface $request): string
    {
        $user = $request->getAttribute(UserInterface::class);
    
        if ($user->hasRole() && $user->role()->key() !== 'editor') {
            return 'can not read';
        }
        
        // or
        if ($user->getRoleKey() !== 'editor') {
            return 'can not read';
        }

        return 'can read';
    }
}
```

**Using middleware**

Use the [Verify Role Middleware](#verify-role-middleware) to verify role on routes.

## Http User Error Handler Boot

The http user error handler boot handles any user specific exceptions such as:

* ```Tobento\App\User\Exception\TokenExpiredException```
* ```Tobento\App\User\Exception\AuthenticationException```
* ```Tobento\App\User\Exception\AuthorizationException```
* ```Tobento\App\User\Exception\PermissionDeniedException```
* ```Tobento\App\User\Exception\RoleDeniedException```

**Adding the boot:**

```php
use Tobento\App\AppFactory;

// Create the app
$app = (new AppFactory())->createApp();

// Adding boots
$app->boot(\Tobento\App\User\Boot\HttpUserErrorHandler::class);
$app->boot(\Tobento\App\User\Boot\User::class);

// Run the app
$app->run();
```

You may create a custom [Error Handler](https://github.com/tobento-ch/app-http/#handle-other-exceptions) or add an [Error Handler With A Higher Priority](https://github.com/tobento-ch/app-http/#prioritize-error-handler) of ```3000``` as defined on the ```Tobento\App\User\Boot\HttpUserErrorHandler::class```.


## Middleware

### User Middleware

The ```Tobento\App\User\Middleware\User::class``` middleware will ensure that there is always a user available from the request attributes. Furthermore, it sets the current acl user.

Code snippet from the middleware process method:

```php
public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
{
    // Get the authenticated user if exists:
    $user = $request->getAttribute(AuthInterface::class)?->getAuthenticated()?->user();

    // Otherwise, create guest user:
    if (is_null($user)) {
        $user = $this->userFactory->createGuestUser();
    }

    $request = $request->withAttribute(UserInterface::class, $user);

    // Set user on acl:
    if ($this->acl) {
        $this->acl->setCurrentUser($user);
    }

    return $handler->handle($request);
}
```

### Authentication Middleware

The ```Tobento\App\User\Middleware\Authentication::class``` middleware will try to authenticate the user on every request by
using the defined token transport(s) to fetch the token from the request which will be authenticated by the token authenticator.

Code snippet from the middleware process method:

```php    
public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
{
    // Add auth to the request:
    $request = $request->withAttribute(AuthInterface::class, $this->auth);

    // Handle token:
    $tokenId = $this->tokenTransport->fetchTokenId($request);

    if (!is_null($tokenId)) {
        try {
            $token = $this->tokenStorage->fetchToken($tokenId);
            $user = $this->tokenAuthenticator->authenticate($token);

            $this->auth->start(
                new Authenticated(token: $token, user: $user),
                $this->tokenTransport->name()
            );
        } catch (TokenNotFoundException $e) {
            // ignore TokenNotFoundException as to
            // proceed with handling the response.
            // other exceptions will be handled by the error handler!
        }
    }

    // Handle the response:
    $response = $handler->handle($request);

    if (! $this->auth->hasAuthenticated()) {
        return $response;
    }

    if ($this->auth->isClosed()) {
        $this->tokenStorage->deleteToken($this->auth->getAuthenticated()->token());

        return $this->tokenTransport->removeToken(
            token: $this->auth->getAuthenticated()->token(),
            request: $request,
            response: $response,
        );
    }

    return $this->tokenTransport->commitToken(
        token: $this->auth->getAuthenticated()->token(),
        request: $request,
        response: $response,
    );
}
```

### Authentication With Middleware

Check out the [**Different Authentication Per Routes**](#different-authentication-per-routes) section to learn more about this middleware.

### Authenticated Middleware

The ```Authenticated::class``` middleware protects routes from unauthenticated users.

```php
use Tobento\App\AppFactory;
use Tobento\App\User\Middleware\Authenticated;

$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Http\Boot\Routing::class);
$app->boot(\Tobento\App\User\Boot\User::class);
$app->booting();

// Routes:
$app->route('GET', 'account', function() {
    // only for authenticated user!
    return 'response';
})->middleware(Authenticated::class)->name('account');

$app->route('GET', 'account', function() {
    // only for authenticated user!
    return 'response';
})->middleware([
    Authenticated::class,
    
    // you may allow access only to user authenticated via:
    'via' => 'loginform|loginlink',
    
    // or you may allow access only to user authenticated except via:
    'exceptVia' => 'remembered|loginlink',
    
    // you may specify a custom message to show to the user:
    'message' => 'You have insufficient rights to access the resource!',
    
    // you may specify a message level:
    'messageLevel' => 'notice',
    
    // you may specify a route name for redirection:
    'redirectRoute' => 'login',
    
    // or you may specify an uri for redirection
    'redirectUri' => '/login',
]);

// you may use the middleware alias defined in user config:
$app->route('GET', 'account', function() {
    return 'response';
})->middleware(['auth', 'via' => 'loginform|loginlink']);

// Run the app:
$app->run();
```

### Unauthenticated Middleware

The ```Unauthenticated::class``` middleware protects routes from authenticated users.

```php
use Tobento\App\AppFactory;
use Tobento\App\User\Middleware\Unauthenticated;

$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Http\Boot\Routing::class);
$app->boot(\Tobento\App\User\Boot\User::class);
$app->booting();

// Routes:
$app->route('GET', 'login', function() {
    // only for unauthenticated user!
    return 'response';
})->middleware(Unauthenticated::class)->name('login');

$app->route('GET', 'login', function() {
    // only for unauthenticated user!
    return 'response';
})->middleware([
    Unauthenticated::class,
    
    // you may specify a custom message to show to the user:
    'message' => 'Already authenticated!',
    
    // you may specify a message level:
    'messageLevel' => 'notice',
    
    // you may specify a route name for redirection:
    'redirectRoute' => 'home',
    
    // or you may specify an uri for redirection
    'redirectUri' => '/home',
]);

// you may use the middleware alias defined in user config:
$app->route('GET', 'login', function() {
    return 'response';
})->middleware(['guest', 'message' => 'Already authenticated!']);

// Run the app:
$app->run();
```

### Verified Middleware

The ```Verified::class``` middleware protects routes from unverified users.

```php
use Tobento\App\AppFactory;
use Tobento\App\User\Middleware\Verified;

$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Http\Boot\Routing::class);
$app->boot(\Tobento\App\User\Boot\User::class);
$app->booting();

// Routes:
$app->route('GET', 'account', function() {
    // only for users with at least one channel verified!
    return 'response';
})->middleware(Verified::class)->name('account');

$app->route('GET', 'account', function() {
    // only for users with specific channels verified!
    return 'response';
})->middleware([
    Verified::class,
    
    // specify the channels the user must have at least verified one of:
    'oneOf' => 'email|smartphone',
    
    // OR specify the channels the user must have verified all:
    'allOf' => 'email|smartphone',
    
    // you may specify a custom message to show to the user:
    'message' => 'You are not verified to access the resource!',
    
    // you may specify a message level:
    'messageLevel' => 'notice',
    
    // you may specify a route name for redirection:
    'redirectRoute' => 'login',
    
    // or you may specify an uri for redirection
    'redirectUri' => '/login',
]);

// you may use the middleware alias defined in user config:
$app->route('GET', 'account', function() {
    return 'response';
})->middleware(['verified', 'oneOf' => 'email|smartphone']);

// Run the app:
$app->run();
```

Check out the [User Channel Verification](#user-channel-verification) section to learn more about it.

### Verify Permission Middleware

The ```VerifyPermission::class``` middleware protects routes from users without the defined permission(s). If a user has insufficient permission a ```Tobento\App\User\Exception\PermissionDeniedException::class``` will be thrown.

```php
use Tobento\App\AppFactory;
use Tobento\App\User\Middleware\VerifyPermission;

$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Http\Boot\Routing::class);
$app->boot(\Tobento\App\User\Boot\User::class);
$app->booting();

// Routes:
$app->route('GET', 'login', function() {
    // only for user with permission login.show!
    return 'response';
})->middleware(VerifyPermission::class)->name('login.show');

// you may specify the following parameters:
$app->route('GET', 'login', function() {
    return 'response';
})->middleware([
    VerifyPermission::class,
    
    // set the permission (optional).
    // if not set it uses the route name:
    'permission' => 'login.show|anotherPermission',
    
    // you may specify a custom message to show to the user:
    'message' => 'You do not have permission to access the resource!',
    
    // you may specify a message level:
    'messageLevel' => 'notice',
    
    // you may specify a route name for redirection:
    'redirectRoute' => 'home',
    
    // or you may specify an uri for redirection
    'redirectUri' => '/home',
    
])->name('login.show');

// you may use the middleware alias defined in user config:
$app->route('GET', 'login', function() {
    return 'response';
})->middleware('can');

// Run the app:
$app->run();
```

Check out the [Http User Error Handler Boot](#http-user-error-handler-boot) how to handle the exception.

### Verify Role Middleware

The ```VerifyRole::class``` middleware protects routes from users without the defined role(s). If a user has insufficient role a ```Tobento\App\User\Exception\RoleDeniedException::class``` will be thrown.

```php
use Tobento\App\AppFactory;
use Tobento\App\User\Middleware\VerifyRole;

$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Http\Boot\Routing::class);
$app->boot(\Tobento\App\User\Boot\User::class);
$app->booting();

// Routes:
$app->route('GET', 'login', function() {
    // only for user with role editor and administrator!
    return 'response';
})->middleware([
    VerifyRole::class,
    
    // set the role.
    'role' => 'editor|administrator',
    
    // you may specify a custom message to show to the user:
    'message' => 'You do not have a required role to access the resource!',
    
    // you may specify a message level:
    'messageLevel' => 'notice',
    
    // you may specify a route name for redirection:
    'redirectRoute' => 'home',
    
    // or you may specify an uri for redirection
    'redirectUri' => '/home',
    
]);

// you may use the middleware alias defined in user config:
$app->route('GET', 'login', function() {
    return 'response';
})->middleware(['role', 'role' => 'editor|administrator']);

// Run the app:
$app->run();
```

Check out the [Http User Error Handler Boot](#http-user-error-handler-boot) how to handle the exception.

## Authenticator

### Identity Authenticator

The ```Tobento\App\User\Authenticator\IdentityAuthenticator::class``` identifies the user by the ```email``` and/or ```username``` and/or ```smartphone``` from the request input ```user```. Furthermore, it verifies the user password from the request input ```password```. You can specify which attributes are allowed for identification and if you want to verify the password.

**Login Controller Example**

```php
use Tobento\App\User\Authenticator\IdentityAuthenticator;
use Tobento\App\User\Authenticator\UserVerifiers;
use Tobento\App\User\Authenticator\UserRoleAreaVerifier;
use Tobento\App\User\Authentication\AuthInterface;
use Tobento\App\User\Authentication\Authenticated;
use Tobento\App\User\Authentication\Token\TokenStorageInterface;
use Tobento\App\User\Exception\AuthenticationException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class LoginController
{
    public function login(
        ServerRequestInterface $request,
        IdentityAuthenticator $authenticator,
        AuthInterface $auth,
        TokenStorageInterface $tokenStorage,
    ): ResponseInterface {
        // You may specify the identity attributes to be checked.
        // At least one attribute is required.
        $authenticator->identifyBy(['email', 'username', 'smartphone', 'password']);
        
        // or only identify by email and verify password.
        $authenticator->identifyBy(['email', 'password']);
        
        // you may verify user attributes:
        $authenticator = $authenticator->withUserVerifier(
            new UserVerifiers(
                new UserRoleAreaVerifier('frontend'),
            ),
        );
        
        // You may change the request input names:
        $authenticator->userInputName('user');
        $authenticator->passwordInputName('password');
        
        // You may change the request method,
        // only 'POST' (default), 'GET' and 'PUT':
        $authenticator->requestMethod('POST');
        
        // try to authenticate user:
        try {
            $user = $authenticator->authenticate($request);
        } catch (AuthenticationException $e) {
            // handle exception:
            // create and return response for exception:
            return $createdResponse;
        }
        
        // on success create token and start auth:
        $token = $tokenStorage->createToken(
            payload: ['userId' => $user->id(), 'passwordHash' => $user->password()],
            authenticatedVia: 'loginform',
            authenticatedBy: $authenticator::class,
            // issuedAt: $issuedAt,
            // expiresAt: $expiresAt,
        );
        
        $auth->start(new Authenticated(token: $token, user: $user));
        
        // create and return response:
        return $createdResponse;
    }
}
```

### Attributes Authenticator

The ```Tobento\App\User\Authenticator\AttributesAuthenticator::class``` identifies the user by the specified user attributes. Unlike the [Identity Authenticator](identity-authenticator) this authenticator identifies the user by all attributes.

**Login Controller Example**

```php
use Tobento\App\User\Authenticator\AttributesAuthenticator;
use Tobento\App\User\Authenticator\UserVerifiers;
use Tobento\App\User\Authenticator\UserRoleAreaVerifier;
use Tobento\App\User\Authentication\AuthInterface;
use Tobento\App\User\Authentication\Authenticated;
use Tobento\App\User\Authentication\Token\TokenStorageInterface;
use Tobento\App\User\Exception\AuthenticationException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class LoginController
{
    public function login(
        ServerRequestInterface $request,
        AttributesAuthenticator $authenticator,
        AuthInterface $auth,
        TokenStorageInterface $tokenStorage,
    ): ResponseInterface {
        // Specify the user attributes to be identified:
        $authenticator->addAttribute(
            // specify the user attribute name:
            name: 'email',
            
            // specify the reuest input name:
            // optional, if not set name will be used!
            inputName: 'email',
            
            // you may specify the validation rules:
            // default rule is required|string|minLen:3|maxLen:150
            validate: 'required|email',
        );
        
        //$authenticator->addAttribute(name: 'smartphone');
        
        // will verify password.
        $authenticator->addAttribute(name: 'password');
        
        // you may verify user attributes:
        $authenticator = $authenticator->withUserVerifier(
            new UserVerifiers(
                new UserRoleAreaVerifier('frontend'),
            ),
        );
        
        // You may change the request method,
        // only 'POST' (default), 'GET' and 'PUT':
        $authenticator->requestMethod('POST');
        
        // try to authenticate user:
        try {
            $user = $authenticator->authenticate($request);
        } catch (AuthenticationValidationException $e) {
            // you may handle specific exception:
            // create and return response for exception:
            return $createdResponse;
        } catch (AuthenticationException $e) {
            // handle exception:
            // create and return response for exception:
            return $createdResponse;
        }
        
        // on success create token and start auth:
        $token = $tokenStorage->createToken(
            payload: ['userId' => $user->id(), 'passwordHash' => $user->password()],
            authenticatedVia: 'loginform',
            authenticatedBy: $authenticator::class,
            // issuedAt: $issuedAt,
            // expiresAt: $expiresAt,
        );
        
        $auth->start(new Authenticated(token: $token, user: $user));
        
        // create and return response:
        return $createdResponse;
        
        // create and return response:
        return $createdResponse;
    }
}
```

### User Verifier

User verifiers may be used to verify certain user attributes while authenticating a user. See [Identity Authenticator](#identity-authenticator) for instance.

#### User Role Verifier

```php
use Tobento\App\User\Authenticator\UserRoleVerifier;

// User must have one of the specified role.
$verifier = new UserRoleVerifier('editor', 'author');
```

#### User Role Area Verifier

```php
use Tobento\App\User\Authenticator\UserRoleAreaVerifier;

// User must have one of the specified role area.
$verifier = new UserRoleAreaVerifier('frontend', 'api');
```

### Token Authenticator

The token authenticator is responsible to authenticate the user based on the token. See [Authentication Middleware](#authentication-middleware) for more detail.

You may add token verifiers in the ```app/config/user.php``` file to verify certain token payload attributes:

```php
use Tobento\App\User\Authentication;
use Psr\Container\ContainerInterface;

return [    
    // ...

    'interfaces' => [
        // ...

        // Default token authenticator:
        // Authenticator\TokenAuthenticatorInterface::class => Authenticator\TokenAuthenticator::class,
        
        // Example with token verifiers:
        Authenticator\TokenAuthenticatorInterface::class => static function(ContainerInterface $c) {
            return new Authenticator\TokenAuthenticator(
                verifier: new Authenticator\TokenVerifiers(
                    new Authenticator\TokenPasswordHashVerifier(
                        // The token issuers (storage names) to verify password hash. 
                        // If empty it gets verified for all issuers.
                        issuers: ['session'],
                        
                        // The attribute name of the payload:
                        name: 'passwordHash',
                    ),
                ),
            );
        },

        // ...
    ],

];
```

### Token Verifier

Token verifiers may be used to verify certain token payload attributes while authenticating a user by token. See [Token Authenticator](#token-authenticator) for instance.

#### Token Password Hash Verifier

The ```TokenPasswordHashVerifier::class``` may be used to invalidate tokens if user changes password.

```php
use Tobento\App\User\Authenticator\TokenPasswordHashVerifier;

$verifier = new TokenPasswordHashVerifier(
    // The token issuers (storage names) to verify password hash. 
    // If empty it gets verified for all issuers.
    issuers: ['session'],
    
    // Will only be verified if authenticated
    // via remembered or loginlink if specified:
    authenticatedVia: 'remembered|loginlink',
    
    // The attribute name of the payload:
    name: 'passwordHash',
);
```

#### Token Payload Verifier

The ```TokenPayloadVerifier::class``` may be used to invalidate tokens if the specified payload attribute does not match the given value.

```php
use Tobento\App\User\Authenticator\TokenPayloadVerifier;

$verifier = new TokenPayloadVerifier(
    // Specify the payload attribute name:
    name: 'remoteAddress',
    
    // Specify the value to match:
    value: $_SERVER['REMOTE_ADDR'] ?? null,

    // The token issuers (storage names) to verify password hash. 
    // If empty it gets verified for all issuers.
    issuers: ['session'],
    
    // Will only be verified if authenticated
    // via remembered or loginlink if specified:
    authenticatedVia: 'remembered|loginlink',
);
```

## Token Storage

### Null Storage

The ```NullStorage``` does not store any token at all. This means you will never be authenticated.

In ```app/config/user.php``` file:

```php
use Tobento\App\User\Authentication;
use Psr\Container\ContainerInterface;

return [    
    // ...

    'interfaces' => [
        // ...

        // Define the token storages you wish to support:
        Authentication\Token\TokenStoragesInterface::class => static function(ContainerInterface $c) {
            return new Authentication\Token\TokenStorages(
                // add null storage:
                new Authentication\Token\NullStorage(),
            );
        },
        
        // Define the default token storage used for auth:
        Authentication\Token\TokenStorageInterface::class => static function(ContainerInterface $c) {
            // you might change to null:
            return $c->get(Authentication\Token\TokenStoragesInterface::class)->get('null');
        },

        // ...
    ],

];
```

### In Memory Storage

The ```InMemoryStorage``` does store tokens in memory only.

In ```app/config/user.php``` file:

```php
use Tobento\App\User\Authentication;
use Psr\Container\ContainerInterface;
use Psr\Clock\ClockInterface;

return [    
    // ...

    'interfaces' => [
        // ...

        // Define the token storages you wish to support:
        Authentication\Token\TokenStoragesInterface::class => static function(ContainerInterface $c) {
            return new Authentication\Token\TokenStorages(
                // add inmemory storage:
                new Authentication\Token\InMemoryStorage(
                    clock: $c->get(ClockInterface::class),
                ),
            );
        },
        
        // Define the default token storage used for auth:
        Authentication\Token\TokenStorageInterface::class => static function(ContainerInterface $c) {
            // you might change to inmemory:
            return $c->get(Authentication\Token\TokenStoragesInterface::class)->get('inmemory');
        },

        // ...
    ],

];
```

### Repository Storage

The ```RepositoryStorage``` uses the [Service Repository Storage](https://github.com/tobento-ch/service-repository-storage) to store tokens.

In ```app/config/user.php``` file:

```php
use Tobento\App\User\Authentication;
use Tobento\Service\Storage\StorageInterface;
use Psr\Container\ContainerInterface;
use Psr\Clock\ClockInterface;

return [    
    // ...

    'interfaces' => [
        // ...

        // Define the token storages you wish to support:
        Authentication\Token\TokenStoragesInterface::class => static function(ContainerInterface $c) {
            return new Authentication\Token\TokenStorages(
                new Authentication\Token\RepositoryStorage(
                    clock: $c->get(ClockInterface::class),
                    repository: new Authentication\Token\TokenRepository(
                        storage: $c->get(StorageInterface::class)->new(),
                        table: 'auth_tokens',
                    ),
                    name: 'repository',
                ),
            );
        },
        
        // Define the default token storage used for auth:
        Authentication\Token\TokenStorageInterface::class => static function(ContainerInterface $c) {
            return $c->get(Authentication\Token\TokenStoragesInterface::class)->get('repository');
        },

        // ...
    ],

];
```

**Delete expired tokens**

You may call the ```deleteExpiredTokens``` method to delete all expired tokens:

```php
$repositoryStorage->deleteExpiredTokens();
```

### Session Storage

Stores the token in PHP session.

In ```app/config/user.php``` file:

```php
use Tobento\App\User\Authentication;
use Tobento\Service\Session\SessionInterface;
use Psr\Container\ContainerInterface;
use Psr\Clock\ClockInterface;

return [    
    // ...

    'interfaces' => [
        // ...

        // Define the token storages you wish to support:
        Authentication\Token\TokenStoragesInterface::class => static function(ContainerInterface $c) {
            return new Authentication\Token\TokenStorages(
                // add session storage:
                new Authentication\Token\SessionStorage(
                    session: $c->get(SessionInterface::class),
                    clock: $c->get(ClockInterface::class),
                ),
            );
        },
        
        // Define the default token storage used for auth:
        Authentication\Token\TokenStorageInterface::class => static function(ContainerInterface $c) {
            // you might change to session:
            return $c->get(Authentication\Token\TokenStoragesInterface::class)->get('session');
        },

        // ...
    ],

];
```

**Make sure you boot the [App Http - Session Boot](https://github.com/tobento-ch/app-http#session-boot) in your app:**

```php
use Tobento\App\AppFactory;

// Create the app
$app = (new AppFactory())->createApp();

// Adding boots
$app->boot(\Tobento\App\User\Boot\User::class);
$app->boot(\Tobento\App\Http\Boot\Session::class);

// Run the app
$app->run();
```

## Token Transport

### Cookie Transport

Stores the authentication token in a cookie.

In ```app/config/user.php``` file:

```php
use Tobento\App\User\Authentication;
use Psr\Container\ContainerInterface;
use Psr\Clock\ClockInterface;

return [    
    // ...

    'interfaces' => [
        // ...

        // Define the token transport you wish to support:
        Authentication\Token\TokenTransportsInterface::class => static function(ContainerInterface $c) {
            return new Authentication\Token\TokenTransports(
                new Authentication\Token\CookieTransport(
                    clock: $c->get(ClockInterface::class),
                    cookieName: 'token',
                ),
            );
        },
        
        // Define the default token transport(s) used for auth:
        Authentication\Token\TokenTransportInterface::class => static function(ContainerInterface $c) {
            return $c->get(Authentication\Token\TokenTransportsInterface::class)->get('cookie');
            //return $c->get(Authentication\Token\TokenTransportsInterface::class); // all
            //return $c->get(Authentication\Token\TokenTransportsInterface::class)->only(['cookie']);
        },

        // ...
    ],

];
```

**Make sure you boot the [App Http - Cookies Boot](https://github.com/tobento-ch/app-http#cookies-boot) in your app:**

```php
use Tobento\App\AppFactory;

// Create the app
$app = (new AppFactory())->createApp();

// Adding boots
$app->boot(\Tobento\App\User\Boot\User::class);
$app->boot(\Tobento\App\Http\Boot\Cookies::class);

// Run the app
$app->run();
```

### Header Transport

Stores the authentication token in a HTTP header.

In ```app/config/user.php``` file:

```php
use Tobento\App\User\Authentication;
use Psr\Container\ContainerInterface;

return [    
    // ...

    'interfaces' => [
        // ...

        // Define the token transport you wish to support:
        Authentication\Token\TokenTransportsInterface::class => static function(ContainerInterface $c) {
            return new Authentication\Token\TokenTransports(
                new Authentication\Token\HeaderTransport(name: 'header', headerName: 'X-Auth-Token'),
            );
        },
        
        // Define the default token transport(s) used for auth:
        Authentication\Token\TokenTransportInterface::class => static function(ContainerInterface $c) {
            return $c->get(Authentication\Token\TokenTransportsInterface::class)->get('header');
            //return $c->get(Authentication\Token\TokenTransportsInterface::class); // all
            //return $c->get(Authentication\Token\TokenTransportsInterface::class)->only(['header']);
        },

        // ...
    ],

];
```

## Events

**Available Events**

```php
use Tobento\App\User\Event;
```

| Event | Description |
| --- | --- |
| ```Event\Authenticated::class``` | The event will dispatch **after** the user authenticated success |
| ```Event\Unauthenticated::class``` | The event will dispatch **after** the user unauthenticated success. |
| ```Event\UserCreated::class``` | The event will dispatch **after** the user is created. |
| ```Event\UserUpdated::class``` | The event will dispatch **after** the user is updated. |
| ```Event\UserDeleted::class``` | The event will dispatch **after** the user is deleted. |

**Supporting Events**

Simply, install the [App Event](https://github.com/tobento-ch/app-event) bundle.

## Commands

Before using commands, you will need to install the [App Console](https://github.com/tobento-ch/app-console) bundle.

**Delete Expired Tokens**

You may delete expired tokens from token storages supporting it.

```
php app.php auth:purge-tokens

// or you may delete only from specific token storages:
php app.php auth:purge-tokens --storage=name
```

## Migration

### Role Permissions Action

You may use the ```RolePermissionsAction::class``` to add and remove permissions for roles.

```php
use Tobento\App\User\Migration\RolePermissionsAction;
use Tobento\App\User\RoleRepositoryInterface;
use Tobento\Service\Migration\MigrationInterface;
use Tobento\Service\Migration\ActionsInterface;
use Tobento\Service\Migration\Actions;

class RolesPermissionMigration implements MigrationInterface
{
    public function __construct(
        protected RoleRepositoryInterface $roleRepository,
    ) {}
    
    /**
     * Return a description of the migration.
     *
     * @return string
     */
    public function description(): string
    {
        return 'Role permissions.';
    }
        
    /**
     * Return the actions to be processed on install.
     *
     * @return ActionsInterface
     */
    public function install(): ActionsInterface
    {
        return new Actions(
            new RolePermissionsAction(
                roleRepository: $this->roleRepository,
                add: [
                    'developer' => ['roles', 'roles.create', 'roles.edit', 'roles.delete', 'roles.permissions'],
                    'administrator' => ['roles', 'roles.create', 'roles.edit', 'roles.delete', 'roles.permissions'],
                ],
                description: 'Roles permissions added for developer and administrator',
            ),
        );
    }

    /**
     * Return the actions to be processed on uninstall.
     *
     * @return ActionsInterface
     */
    public function uninstall(): ActionsInterface
    {
        return new Actions(
            new RolePermissionsAction(
                roleRepository: $this->roleRepository,
                remove: [
                    'developer' => ['roles', 'roles.create', 'roles.edit', 'roles.delete', 'roles.permissions'],
                    'administrator' => ['roles', 'roles.create', 'roles.edit', 'roles.delete', 'roles.permissions'],
                ],
                description: 'Roles permissions removed for developer and administrator',
            ),
        );
    }
}
```

Then [Install The Migration](https://github.com/tobento-ch/app-migration#install-and-uninstall-migration) using the app migration.

## Learn More

### Different Authentication Per Routes

First, configure the ```app/config/user.php``` file and define token storages and transports you wish to use:

```php
use Tobento\App\User\Authentication;
use Psr\Container\ContainerInterface;
use Psr\Clock\ClockInterface;

return [    
    // ...

    'middlewares' => [
        // Uncomment it and set it on each route individually!
        // User\Middleware\Authentication::class,
    ],
    
    'interfaces' => [
        // ...

        // Define the token storages you wish to support:
        Authentication\Token\TokenStoragesInterface::class => static function(ContainerInterface $c) {
            return new Authentication\Token\TokenStorages(
                new Authentication\Token\SessionStorage(
                    session: $c->get(SessionInterface::class),
                    clock: $c->get(ClockInterface::class),
                ),
            );
        },
        
        // Define the token transport you wish to support:
        Authentication\Token\TokenTransportsInterface::class => static function(ContainerInterface $c) {
            return new Authentication\Token\TokenTransports(
                new Authentication\Token\CookieTransport(
                    clock: $c->get(ClockInterface::class),
                    cookieName: 'token',
                ),
                new Authentication\Token\HeaderTransport(name: 'header', headerName: 'X-Auth-Token'),
            );
        },

        // ...
    ],

];
```

Finally, add middleware to your routes:

```php
use Tobento\App\User\Middleware\AuthenticationWith;
use Tobento\Service\Routing\RouteGroupInterface;

// ...

// Routes:
// Web example:
$app->routeGroup('', function(RouteGroupInterface $group) {
    
    // define your web routes:
    
})->middleware([
    AuthenticationWith::class,
    
    // specify the token transport name:
    'transportName' => 'cookie',
    
    // specify the token storage name:
    'storageName' => 'session',
]);

// Api example:
$app->routeGroup('api', function(RouteGroupInterface $group) {
    
    // define your api routes:

})->middleware([
    AuthenticationWith::class,
    
    // specify the token transport name:
    'transportName' => 'header',
    
    // specify the token storage name:
    'storageName' => 'session',
]);

// ...
```

Another way to is to use a [Http - Area Boot](https://github.com/tobento-ch/app-http#area-boot) for each area "web" and "api" running in its own application.

### Password Hashing

Use the ```PasswordHasherInterface::class``` defined in the ```app/config/user.php``` file to hash and verify user passwords.

**Basic Usage**

```php
use Tobento\App\User\PasswordHasherInterface;

class SomeService
{
    public function __construct(
        private PasswordHasherInterface $passwordHasher,
    ) {
        // hash password:
        $hashedPassword = $passwordHasher->hash(plainPassword: 'password');
        
        // verify password:
        $isValid = $passwordHasher->verify(hashedPassword: $hashedPassword, plainPassword: 'password');
    }
}
```

The following authenticators use the password hasher to verify the password:

* [Identity Authenticator](#identity-authenticator)
* [Attributes Authenticator](#attributes-authenticator)

### User Channel Verification

**Adding verified channels**

```php
use Tobento\App\User\UserRepositoryInterface;
use DateTime;

class SomeService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
        $updatedUser = $userRepository->addVerified(
            id: 5, // user id
            channel: 'email',
            verifiedAt: new DateTime('2023-09-24 00:00:00'),
        );
    }
}
```

**Removing verified channels**

```php
use Tobento\App\User\UserRepositoryInterface;
use DateTime;

class SomeService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
        $updatedUser = $userRepository->removeVerified(
            id: 5, // user id
            channel: 'email',
        );
    }
}
```

**User verified methods**

```php
use Tobento\App\User\UserInterface;

var_dump($user instanceof UserInterface);
// bool(true)

// Get the verified channels:
$verified = $user->getVerified();
// ['email' => '2023-09-24 00:00:00']

// Get the date verified at for a specific channel:
$emailVerifiedAt = $user->getVerifiedAt(channel: 'email');
// '2023-09-24 00:00:00' or NULL

// Returns true if the specified channels are verified, otherwise false.
$verified = $user->isVerified(channels: ['email', 'smartphone']);

// Returns true if at least one channel is verified, otherwise false.
$verified = $user->isOneVerified();

// or one of the specified channels:
$verified = $user->isOneVerified(channels: ['email', 'smartphone']);
```

## App User Bundles

You may use the following user bundles for your app.

* [App User Web](#) - Login, register and more. (Coming soon)
* [App User Manager](#) - CRUD for users, roles and permissions. (Coming soon)
* [App User Jwt](#) - Authentication via JSON web token support. (Coming soon)
* [App User Login Link](#) - Authentication via login link. (Coming soon)

# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)
- [Spiral Framework - For authentication inspiration](https://spiral.dev/docs/security-authentication)