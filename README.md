FSCHateoasBundle
================

[![Build Status](https://secure.travis-ci.org/TheFootballSocialClub/FSCHateoasBundle.png)](http://travis-ci.org/TheFootballSocialClub/FSCHateoasBundle)

This bundle hooks into the JMSSerializerBundle serialization process, and provides HATEOAS features.
Right now, only adding links is supported.

Even though there are some tests, be aware that this is a work in progress.
For example, only yaml and annotation metadata configuration is supported.

Some of the examples will looks weird, in particular xml elements name like "result" or "entry"; we'll work on
features to customize/automate this!

Adding links
------------

With the following configuration and entity:

```yaml
# routing.yml
api_user_get:
    pattern: /api/users/{id}

api_user_list:
    pattern: /api/users

user_profile:
    pattern: /profile/{user_id}
```

```php
<?php

// src/Acme/FooBundle/Entity/User.php

use JMS\SerializerBundle\Annotation as Serializer;
use FSC\HateoasBundle\Annotation as Hateoas;

/**
 * @Hateoas\Relation("self",      route = "api_user_get", parameters = { "id" = "id" })
 * @Hateoas\Relation("alternate", route = "user_profile", parameters = { "user_id" = "id" })
 * @Hateoas\Relation("users",     route = "api_user_list")
 *
 * @Serializer\XmlRoot("user")
 */
class User
{
    /** @Serializer\XmlAttribute */
    public $id;
    public $username;
}
```

Then doing:

```php
<?php

$user = new User();
$user->id = 24;
$user->username = 'adrienbrault';

$serializedUser = $container->get('serializer')->serialize($user, $format);
```

Would result in:

```xml
<user id="24">
  <username><![CDATA[adrienbrault]]></username>
  <link rel="self" href="http://localhost/api/users/24"/>
  <link rel="alternate" href="http://localhost/profile/24"/>
  <link rel="users" href="http://localhost/api/users"/>
</user>
```

or

```json
{
    "id": 24,
    "links": [
        {
            "rel": "self",
            "href": "http:\/\/localhost\/api\/users\/24"
        },
        {
            "rel": "alternate",
            "href": "http:\/\/localhost\/profile\/24"
        },
        {
            "rel": "users",
            "href": "http:\/\/localhost\/api\/users"
        }
    ]
}
```

Pagerfanta Handler
------------------

The bundle provides a Pagerfanta handler.

Example:

```php
<?php

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

public function getListAction($page = 1, $limit = 10)
{
    $query = $this->get('doctrine')->getRepository('User')->createQueryXXX();
    $pager = new Pagerfanta(new DoctrineORMPager($results)); // or any Pagerfanta adapter
    $pager->setCurrentPage($page);
    $pager->setMaxPerPage($limit);

    return new Response($this->get('serializer')->serialize($pager, 'xml')));
}
```

`GET /list?page=3` would result in

```
<result page="3" limit="10" total="234">
  <entry/>
  <entry/>
  <entry/>
  <entry/>
  <entry/>
  <entry/>
  <entry/>
  <entry/>
  <entry/>
  <entry/>
</result>
```

RouteAwarePagerHandler
-----------------------

The Pagerfanta alone doesn't create links to self/next/previous/last/first pages.
The RouteAwarePagerHandler can automatically creates links to theses pages.

Examples:

```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use FSC\HateoasBundle\Model\RouteAwarePager;

public function getListAction(Request $request, $page = 1, $limit = 10)
{
    $query = $this->get('doctrine')->getRepository('User')->createQueryXXX();
    $pager = new Pagerfanta(new DoctrineORMPager($results)); // or any Pagerfanta adapter
    $pager->setCurrentPage($page);
    $pager->setMaxPerPage($limit);

    $routeAwarePager = new RouteAwarePager($pager, $request->attributes->get('_route'), $request->attributes->get('_route_params'));

    return new Response($this->get('serializer')->serialize($routeAwarePager, 'xml')));
}
```

`GET /list?page=3` would result in

```
<result page="3" limit="10" total="234">
  <link rel="self" href="http://localhost/api/users?limit=10&amp;page=3"/>
  <link rel="first" href="http://localhost/api/users?limit=10&amp;page=1"/>
  <link rel="last" href="http://localhost/api/users?limit=10&amp;page=24"/>
  <link rel="previous" href="http://localhost/api/users?limit=10&amp;page=2"/>
  <link rel="next" href="http://localhost/api/users?limit=10&amp;page=4"/>
  <entry/>
  <entry/>
  <entry/>
  <entry/>
  <entry/>
  <entry/>
  <entry/>
  <entry/>
  <entry/>
  <entry/>
</result>
```

Embedding relations
-------------------

Sometimes, your representations have embedded relations that require a service to be fetched, or need to be paginated.
To embed a relation using this bundle, you create a simple Relation metadata (with an annotation for example),
and add extra "content" parameters.

Example:

```yaml
# routing.yml
api_user_get:
    pattern: /api/users/{id}

api_favorite_get:
    pattern: /api/favorites/{id}

api_user_favorites_list:
    pattern: /api/users/{id}/favorites
```

```php
<?php

// src/Acme/FooBundle/Entity/User.php

use JMS\SerializerBundle\Annotation as Serializer;
use FSC\HateoasBundle\Annotation as Hateoas;

// The embedded content being a PagerfantaInterface instance, the bundle will
// automatically wraps it in a RouteAwarePager using the links' route/params

/**
 * @Hateoas\Relation("self", route = "api_user_get", parameters = { "id" = "id" })
 * @Hateoas\Relation("friends",
 *     route = "api_user_favorites_list",
 *     parameters = { "id" = "id" },
 *     content = {
 *         "provider-id" = "acme.foo.user_manager",
 *         "provider-method" = "getUserFriendsPager",
 *         "provider-parameters" = { "userId" = "id", "limit" = "=5" },
 *         "serializer_xml_element_name" = "users"
 *     }
 * )
 *
 * @Serializer\XmlRoot("user")
 */
class User
{
    ...
}
```

```php
<?php

// src/Acme/FooBundle/Entity/User.php

use JMS\SerializerBundle\Annotation as Serializer;
use FSC\HateoasBundle\Annotation as Hateoas;

/**
 * @Hateoas\Relation("self", route = "api_favorite_get", parameters = { "id" = "id" })
 *
 * @Serializer\XmlRoot("user")
 */
class User
{
    ...
}
```

```php
<?php

// This is the class behing the "acme.foo.user_manager" service
class UserManager
{
    public function getUserFriendsPager($userId, $page = 1, $limit = 20)
    {
        $doctrineQuery = ...;

        $pager = Pagerfanta(new DoctrineORMAdapter($doctrineQuery));
        $pager->setCurrentPage($page);
        $pager->setMaxPerPage($limit);

        return $pager;
    }
}
```

```php
<?php

class Controller extends Controller
{
    public function getUserFriendsAction($id, $page = 1, $limit = 20)
    {
        $pager = $this->get('acme.foo.user_manager')->getUserFriendsPager($id, $page, $limit);

        $routeAwarePager = new RouteAwarePager($pager, $request->attributes->get('_route'), $request->attributes->get('_route_params'));

        return new Response($this->get('serializer')->serialize($routeAwarePager, 'xml'));
    }

    public function getUserAction($id)
    {
        $user = ...;

        return new Response($this->get('serializer')->serialize($user, 'xml'));
    }
}
```

`GET /api/users/42` would result in

```
<user>
  <link rel="self" href="http://localhost/api/users/42"/>
  <link rel="friends" href="http://localhost/api/users/42/friends"/>
  <users rel="friends" page="1" limit="5" total="134">
    <link rel="self" href="http://localhost/api/users/42/friends?limit=10&amp;page=1"/>
    <link rel="first" href="http://localhost/api/users/42/friends?limit=10&amp;page=1"/>
    <link rel="last" href="http://localhost/api/users/42/friends?limit=10&amp;page=27"/>
    <link rel="next" href="http://localhost/api/users/42/friends?limit=10&amp;page=2"/>
    <entry/>
    <entry/>
    <entry/>
    <entry/>
    <entry/>
  </users>
</user>
```

and `GET /api/users/42/friends` would result in

```
<result rel="friends" page="1" limit="20" total="134">
  <link rel="self" href="http://localhost/api/users/42/friends?limit=20&amp;page=1"/>
  <link rel="first" href="http://localhost/api/users/42/friends?limit=20&amp;page=1"/>
  <link rel="last" href="http://localhost/api/users/42/friends?limit=20&amp;page=7"/>
  <link rel="next" href="http://localhost/api/users/42/friends?limit=20&amp;page=2"/>
  <entry/>
  <entry/>
  <entry/>
  <entry/>
  <entry/>
</result>
```
