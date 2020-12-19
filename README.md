# PLUGIN THROTTLE #

A plugin that throttles access to a resource (i.e. login form).
Throttling is based on number of attempts before locking down the access
for a defined timeframe.

SQL backend (MySQL or SQLite) is used for keeping track attempts
to access the resource.


## Installation ##

1) Via Composer (preferred)

```
composer require sinevia/php-library-throttle-plugin
```

2) Manually
```json
   "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Sinevia/php-library-throttle-plugin.git"
        }
    ],
    "require": {
        "php": ">=5.5.9",
        "sinevia/php-library-throttle-plugin": "dev-master"
    },
```

# How to Use? #

```
\Sinevia\Plugins\ThrottlePlugin::configure([
   'pdo' => \DB::getPdo()
]);

$userIp = \Sinevia\Plugins\ThrottlePlugin::getCurrentUserIp();

$mayAttempt = \Sinevia\Plugins\ThrottlePlugin::mayAttempt('LoginForm', $userIp, 6, 10 * 60); // 6 attempts in 10 minutes

if ($mayAttempt == false) {
   $message = 'You have passed the maximum allowed authentication attempts.';
   $message .= ' Please, check your password and try again in 10 minutes.';
   return redirect()->back()->withErrors($message)->withInput();
}
```
