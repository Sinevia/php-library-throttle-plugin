#SINEVIA PLUGIN THROTTLE

A plugin that throttles access to a resource (i.e. login form).
Throttling is based on number of attempts before locking down the access
for a defined timeframe.

SQL backend (MySQL or SQLite) is used for keeping track attempts
to access the resource.


# Installation #

```
#!json

   "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Sinevia/php-library-throttle-plugin.git"
        }
    ],
    "require": {
        "php": ">=5.5.9",
        "sinevia/phplibrary/plugin-throttle": "dev-master"
    },
```

# How to Use? #
