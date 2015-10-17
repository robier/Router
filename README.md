Router
======

In hope to make fast routing system this library was build. It's so fast
that caching would be an overhead and results wouldn't be so good :) 

Features
--------

- very fast matching of unknown route
- almost the same timing for matching first and last route
- routes can be nameless
- support for multiple domains and no reduction in speed
- support for http methods (GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS)
- reverse generate URL from a specified route name with parameters check
- special syntax for writing URL definitions
    - two types of patterns (fixed and combined)
    - short codes for the frequently used patterns
    - possibility to register your own frequently used patterns

Installation
------------

This project requires PHP 5.5 or higher.
Installation of this library is simple via composer, you just run command

    composer require robier/router

How to use
----------

To make everything working, you will need to setup `Pattern` and `Parser`
objects, because they are dependencies for `Domain` collection. After setting
them up, you can register your routes.

```php
$pattern = new Pattern();
$pattern->register('urlAlias', '[a-zA-Z0-9-\.äÄöÖüÜß]+', true);

$parser = new Parser($pattern);

$collection = new Domain('localhost.loc', $parser);

$collection->add(Route::get('/[foo]'));
$collection->add(Route::get('/contact'));
$collection->add(Route::get('/about'));
$collection->add(Route::get('/prices'));

// will return MatchedRoute with first route
var_dump($collection->match('/bar', 'GET'));

// will return MatchedRoute with last route
var_dump($collection->match('/prices', 'GET'));

// will return false
var_dump($collection->match('/prices', 'POST'))
```

Also there is a possibility to use multi domain feature.

```php
$pattern = new Pattern();
$pattern->register('urlAlias', '[a-zA-Z0-9-\.äÄöÖüÜß]+', true);

$parser = new Parser($pattern);

$main = new Domain('www.localhost.loc', $parser);

$main->add(Route::get('/[foo]'));
$main->add(Route::get('/')->setName('home'));
$main->add(Route::get('/user')->setName('user'));
$main->add(Route::get('/about'));
$main->add(Route::get('/prices'));

$api = new Domain('api.localhost.loc', $parser);
$api->add(Route::get('/')->setName('home'));
$api->add(Route::post('/user/[user_id]')->setName('user'));

$domains = new MultiDomains();
$domains->add('main', $main);
$domains->add('api', $api);


// will match user route in api domain
var_dump($domains->match('/user/asd', 'POST'));

// will match last route in main domain
var_dump($domains->match('/prices', 'GET'));

// will generate user route for domain www.localhost.loc
var_dump($domains->generate('user'));

// will generate user route for domain api.localhost.loc
var_dump($domains->generate('api:user', ['user_id' => 1]));
```

Url Syntax
----------

Rules:

- every parameter must have a name
- there should not be repeated parameter names in one url definition
- name should consist only letters and numbers
- there are 2 type of defined patterns, combined and strict
    - combined patterns can be combined with AND and OR logic operators
- only last parameter can be optional ie. have `?` char on the right side

###### Patterns

Patterns can be defined in different ways depending on the situation and requirements.
There are two types of patterns. Any type can be defined and registered by user in
`Pattern` object. All patterns should be inside `[]` brackets. Inside those brackets
you need to add pattern name as it is obligatory. If you only have name in the pattern
then we will match anything until next fragment separator `/`.

    /test/[foo:n]{5}/bar
          ----------                pattern definition
           ---                      name (required)
              -                     separator between name and patterns (optional)
               -                    pattern or pattern combination (optional)
                 ---                quantifier (optional)

Examples:

    /test/[foo]?                    foo can be anything and it is optional
    /test/[foo:*]                   foo is everything until end of url
    /test/[foo]/bar                 will match anything until next /
    /test/[foo]{5,10}/bar           will match anything that have 5 to 10 characters
    /test/[foo]{5,}/bar             will match anything that have min 5 characters
    /test/[foo]{,10}/bar            will match anything that have max 10 characters
    /test/[foo:sha1]/bar            will match only sha1 string (length 40 and hexadecimal)
    /test/[foo:n]{5}/bar            will match only number with 5 digits
    /test/[foo:n|au]/bar            will match numbers or string (capital letters), but no both
    /test/[foo:n-a]/bar             will match string containing number and letter 
    /test/[foo:<\d{5}>]/bar         will match number with 5 digits (regex definition)
    /test/[foo:(1|18|foo)]/bar      will match numbers 1 or 18 or string foo

Predefined strict patterns

    md5  - md5 hash matching (32 characters and hexadecimal)
    sha1 - sha1 hash matching (40 characters and hexadecimal)
    *    - will match everything until end of URL

Predefined combined patterns

    n  - numeric (0-9)
    a  - alpha (a-zA-Z)
    al - alpha lower (a-z)
    au - alpha upper (A-Z)
    c  - characters (_-)
    h  - hexadecimal (a-fA-F0-9)

Combined patterns supports logical operators OR `|` and AND `-` and those patterns
can be combined.

    n-a    - numeric and alpha (0-9a-zA-Z)
    c-au-n - characters and alpha upper and numeric (-_A-Z0-9)
    n|a    - numeric or alpha (0-9|a-zA-Z)
    c|au|n - characters or alpha upper or numeric (-_|A-Z|0-9)
    c-a|n  - characters and alpha or numeric (-_a-zA-Z|0-9)

You can also define quantification for combined patterns. It's defined after the pattern
in it's own `{}` curly brackets. There is also and special quantifier `?` that will mark that
pattern as optional one and that question mark can only appear on last pattern in URL
definition.

    {5}   - exactly 5 characters
    {1,5} - between 1 and 5 characters
    {,5}  - max 5 characters
    {5,}  - min 5 characters
    ?     - pattern is optional

Todo
----

- [ ] make possibility for any pattern inside URL definition to be optional
- [ ] make specific exceptions
- [ ] make better exceptions

Benchmarking
------------

Worst case matching results, last and unknown route against 1000 routes.

Test Name | Results | Time | + Interval | Change
--------- | ------- | ---- | ---------- | ------
Robier/Router - unknown route (1000 routes) | 988 | 0.0000143068 | +0.0000000000 | baseline
Robier/Router - last route (1000 routes) | 999 | 0.0000483677 | +0.0000340609 | 238% slower
FastRoute - unknown route (1000 routes) | 967 | 0.0002400878 | +0.0002257809 | 1578% slower
FastRoute - last route (1000 routes) | 999 | 0.0002518330 | +0.0002375262 | 1660% slower
Symfony2 Dumped - unknown route (1000 routes) | 998 | 0.0006161809 | +0.0006018741 | 4207% slower
Symfony2 Dumped - last route (1000 routes) | 993 | 0.0006666702 | +0.0006523634 | 4560% slower
Pux PHP - unknown route (1000 routes) | 996 | 0.0014638989 | +0.0014495921 | 10132% slower
Pux PHP - last route (1000 routes) | 998 | 0.0015648818 | +0.0015505750 | 10838% slower
Symfony2 - unknown route (1000 routes) | 998 | 0.0030423162 | +0.0030280093 | 21165% slower
Symfony2 - last route (1000 routes) | 993 | 0.0030943178 | +0.0030800110 | 21528% slower
Aura v2 - last route (1000 routes) | 996 | 0.0824833132 | +0.0824690064 | 576432% slower
Aura v2 - unknown route (1000 routes) | 996 | 0.0938626260 | +0.0938483192 | 655969% slower

Best case matching results, first route against 1000 routes.

Test Name | Results | Time | + Interval | Change
--------- | ------- | ---- | ---------- | ------
Pux PHP - first route | 985 | 0.0000178673 | +0.0000000000 | baseline
FastRoute - first route | 980 | 0.0000238312 | +0.0000059638 | 33% slower
Symfony2 Dumped - first route | 987 | 0.0000355971 | +0.0000177297 | 99% slower
Robier/Router - first route | 998 | 0.0000480750 | +0.0000302077 | 169% slower
Symfony2 - first route | 999 | 0.0001610693 | +0.0001432019 | 801% slower
Aura v2 - first route | 978 | 0.0002484000 | +0.0002305326 | 1290% slower

How it's so fast?
-----------------

When we try to match some route for example `/bar/foo/test`, the system will:

1. try to match this url inside fixed URLs where the URL is the key of array
and if it found something it return the result, otherwise we go on
2. try to match this url inside regex URLs where first we will break this
URL into variations (`/bar/foo/test`, `/bar/foo`, `/bar`, `/`). After that
we will try to check every variation if we can find it's fixed base as array
key. If we find it then we will run regex matching inside that route collection
(multiple URLs can have the same fixed prefix)