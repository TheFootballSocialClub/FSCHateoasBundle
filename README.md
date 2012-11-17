FSCHateoasBundle
================

[![Build Status](https://secure.travis-ci.org/TheFootballSocialClub/FSCHateoasBundle.png)](http://travis-ci.org/TheFootballSocialClub/FSCHateoasBundle)

This bundle hooks into the JMSSerializerBundle serialization process, and provides HATEOAS features.
Right now, only adding links is supported.

Even though there are some tests, be aware that this is a work in progress.
For example, only yaml and annotation metadata configuration is supported.

## Adding links

With the following configuration and entity:

### Routing and serializer/hateoas metadata

```yaml
# routing.yml
api_user_get:
    pattern: /api/users/{id}

api_user_list:
    pattern: /api/users

user_profile:
    pattern: /profile/{user_id}
```

*Note that you can also configure serializer/hateoas metadatas using yaml to keep serialisation out of your model*

```php
<?php

// src/Acme/FooBundle/Entity/User.php

use JMS\SerializerBundle\Annotation as Serializer;
use FSC\HateoasBundle\Annotation as Rest;

/**
 * @Rest\Relation("self",      href = @Rest\Route("api_user_get", parameters = { "id" = ".id" }))
 * @Rest\Relation("alternate", href = @Rest\Route("user_profile", parameters = { "user_id" = ".id" }))
 * @Rest\Relation("users",     href = @Rest\Route("api_user_list"))
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

### Usage

```php
<?php

$user = new User();
$user->id = 24;
$user->username = 'adrienbrault';

$serializedUser = $container->get('serializer')->serialize($user, $format);
```

### Results

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

## Json Format

The bundle supports customizing the keys of links and embedded relations when serializing to Json. They are
controlled by the following configuration:

```yml
# app/config/config.yml

fsc_hateoas:
    json:
        links: _links         # default: links
        relations: _embedded  # default: relations
```

The above configuration will result in serialization to valid [hal+json](http://stateless.co/hal_specification.html).

## Pagerfanta Handler

Default configuration:

```yaml
fsc_hateoas:
    pagerfanta:
        xml_elements_names_use_serializer_metadata: true
```

With this configuration he pagerfanta handler will use the serializer's xml root name metadata to know what xml element
name should be used for each result. (ie: `/** @Serializer\XmlRootName("user") */ class User {}`)

### Example

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

    $this->get('serializer')->getSerializationVisitor('xml')->setDefaultRootName('users');

    return new Response($this->get('serializer')->serialize($pager, 'xml')));
}
```

`GET /list?page=3` would result in

```xml
<users page="3" limit="10" total="234">
  <user>
    <link rel="self" href="..."/>
  </user>
  <user>
    <link rel="self" href="..."/>
  </user>
  <user>
    <link rel="self" href="..."/>
  </user>
  ...
</users>
```

## RouteAwarePagerHandler

The Pagerfanta alone doesn't create links to self/next/previous/last/first pages.
The RouteAwarePagerHandler can automatically creates links to theses pages.

Default configuration:

```yaml
fsc_hateoas:
    pagerfanta:
        links:
            page_parameter_name: 'page'
            limit_parameter_name: 'limit'
```

### Example

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

    $this->get('serializer')->getSerializationVisitor('xml')->setDefaultRootName('users');

    return new Response($this->get('serializer')->serialize($routeAwarePager, 'xml')));
}
```

`GET /list?page=3` would result in

```xml
<users page="3" limit="10" total="234">
  <link rel="self" href="http://localhost/api/users?limit=10&amp;page=3"/>
  <link rel="first" href="http://localhost/api/users?limit=10&amp;page=1"/>
  <link rel="last" href="http://localhost/api/users?limit=10&amp;page=24"/>
  <link rel="previous" href="http://localhost/api/users?limit=10&amp;page=2"/>
  <link rel="next" href="http://localhost/api/users?limit=10&amp;page=4"/>
  <user>
    <link rel="self" href="..."/>
  </user>
  <user>
    <link rel="self" href="..."/>
  </user>
  <user>
    <link rel="self" href="..."/>
  </user>
  ...
</users>
```

## Embedding relations

Sometimes, your representations have embedded relations that require a service to be fetched, or need to be paginated.
To embed a relation using this bundle, you create a simple Relation metadata (with an annotation for example),
and add extra "content" parameter.

Example:

### Routing and controller

```yaml
# routing.yml
api_user_get:
    pattern: /api/users/{id}

api_user_friends_list:
    pattern: /api/users/{id}/friends
```

```php
<?php

class Controller extends Controller
{
    public function getUserFriendsAction($id, $page = 1, $limit = 20)
    {
        $pager = $this->get('acme.foo.user_manager')->getUserFriendsPager($id, $page, $limit);

        $routeAwarePager = new RouteAwarePager($pager, $request->attributes->get('_route'), $request->attributes->get('_route_params'));

        $this->get('serializer')->getSerializationVisitor('xml')->setDefaultRootName('users');

        return new Response($this->get('serializer')->serialize($routeAwarePager, 'xml'));
    }

    public function getUserAction($id)
    {
        $user = ...;

        return new Response($this->get('serializer')->serialize($user, 'xml'));
    }
}
```

### Model and serializer/hateoas metadata

*Note that you can also configure serializer/hateoas metadata using yaml to keep serialisation out of your model*

```php
<?php

// src/Acme/FooBundle/Entity/User.php

use JMS\SerializerBundle\Annotation as Serializer;
use FSC\HateoasBundle\Annotation as Rest;

// The embedded content being a PagerfantaInterface instance, the bundle will
// automatically wraps it in a RouteAwarePager using the links' route/params

/**
 * @Rest\Relation("self", href = @Rest\Route("api_user_get", parameters = { "id" = ".id" }))
 * @Rest\Relation("friends",
 *     href =  @Rest\Route("api_user_friends_list", parameters = { "id" = ".id" }),
 *     embed = @Rest\Content(
 *         provider = {"acme.foo.user_manager", "getUserFriendsPager"},
 *         providerArguments = { ".id", 1, 5 },
 *         serializerXmlElementName = "users"
 *     )
 * )
 *
 * @Serializer\XmlRoot("user")
 */
class User
{
    ...
}
```

### Define the provider service used to get the data to embed

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

### Results

`GET /api/users/42` would result in

```xml
<user>
  <link rel="self" href="http://localhost/api/users/42"/>
  <link rel="friends" href="http://localhost/api/users/42/friends"/>
  <users rel="friends" page="1" limit="5" total="134">
    <link rel="self" href="http://localhost/api/users/42/friends?limit=10&amp;page=1"/>
    <link rel="first" href="http://localhost/api/users/42/friends?limit=10&amp;page=1"/>
    <link rel="last" href="http://localhost/api/users/42/friends?limit=10&amp;page=27"/>
    <link rel="next" href="http://localhost/api/users/42/friends?limit=10&amp;page=2"/>
    <user>
      <link rel="self" href="..."/>
    </user>
    <user>
      <link rel="self" href="..."/>
    </user>
    <user>
      <link rel="self" href="..."/>
    </user>
    <user>
      <link rel="self" href="..."/>
    </user>
    <user>
      <link rel="self" href="..."/>
    </user>
  </users>
</user>
```

and `GET /api/users/42/friends` would result in

```xml
<users rel="friends" page="1" limit="20" total="134">
  <link rel="self" href="http://localhost/api/users/42/friends?limit=20&amp;page=1"/>
  <link rel="first" href="http://localhost/api/users/42/friends?limit=20&amp;page=1"/>
  <link rel="last" href="http://localhost/api/users/42/friends?limit=20&amp;page=7"/>
  <link rel="next" href="http://localhost/api/users/42/friends?limit=20&amp;page=2"/>
  <user>
    <link rel="self" href="..."/>
  </user>
  <user>
    <link rel="self" href="..."/>
  </user>
  <user>
    <link rel="self" href="..."/>
  </user>
  <user>
    <link rel="self" href="..."/>
  </user>
  <user>
    <link rel="self" href="..."/>
  </user>
</users>
```

### Embedding relations from properties

Instead of defining a service to embed resources you can also embed resources, that are properties of your main
resource.

```php
<?php

// src/Acme/FooBundle/Entity/User.php

use JMS\SerializerBundle\Annotation as Serializer;
use FSC\HateoasBundle\Annotation as Rest;

/**
 * @Rest\Relation("self", href = @Rest\Route("api_user_get", parameters = { "id" = ".id" }))
 * @Rest\Relation("friends",
 *     href =  @Rest\Route("api_user_friends_list", parameters = { "id" = ".id" }),
 *     embed = @Rest\Content(
 *         property = ".friends"
 *     )
 * )
 *
 * @Serializer\XmlRoot("user")
 */
class User
{
    ...

    /**
     * @var array<User>
     */
    private $friends;
}
```

This will serialize the `friends` property and embed it as a relation.


