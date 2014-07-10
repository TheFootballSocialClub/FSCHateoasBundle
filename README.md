FSCHateoasBundle
================

[![Build Status](https://secure.travis-ci.org/TheFootballSocialClub/FSCHateoasBundle.png)](http://travis-ci.org/TheFootballSocialClub/FSCHateoasBundle)
[![Latest Stable Version](https://poser.pugx.org/fsc/hateoas-bundle/v/stable.png)](https://packagist.org/packages/fsc/hateoas-bundle)
[![Total Downloads](https://poser.pugx.org/fsc/hateoas-bundle/downloads.png)](https://packagist.org/packages/fsc/hateoas-bundle)

This bundle hooks into the JMSSerializerBundle serialization process, and provides HATEOAS features.
Right now, only adding links is supported.

Even though there are some tests, be aware that this is a work in progress.
For example, only yaml and annotation metadata configuration is supported.

## Installation

composer.json

```json
{
    "require": {
        "fsc/hateoas-bundle": "0.5.x-dev"
    },
    "minimum-stability": "dev"
}
```

## Example application

You can find a symfony 2.1 example application using this bundle at [https://github.com/adrienbrault/symfony-hateoas-sandbox](https://github.com/adrienbrault/symfony-hateoas-sandbox).

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

use JMS\Serializer\Annotation as Serializer;
use FSC\HateoasBundle\Annotation as Rest;

/**
 * @Rest\Relation("self",               href = @Rest\Route("api_user_get", parameters = { "id" = ".id" }))
 * @Rest\Relation("alternate",          href = @Rest\Route("user_profile", parameters = { "user_id" = ".id" }))
 * @Rest\Relation("users",              href = @Rest\Route("api_user_list"))
 * @Rest\Relation("rss",                href = "http://domain.com/users.rss")
 * @Rest\Relation("from_property_path", href = ".dynamicHref")
 *
 * @Serializer\XmlRoot("user")
 */
class User
{
    /** @Serializer\XmlAttribute */
    public $id;
    public $username;

    public function getDynamicHref() {
        return "dynamic/Href/here";
    }
}
```

Note that the href can either be a `@Route` annotation, a string, or a property path, which will be resolved
when serializing.

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
  <link rel="rss" href="http://domain.com/users.rss"/>
</user>
```

or

```json
{
    "id": 24,
    "links": [
        "self": {
            "href": "http:\/\/localhost\/api\/users\/24"
        },
        "alternate": {
            "href": "http:\/\/localhost\/profile\/24"
        },
        "users": {
            "href": "http:\/\/localhost\/api\/users"
        },
        "rss": {
            "href": "http:\/\/domain.com\/users.rss"
        }
    ]
}
```

## Add relations on objects at runtime

In some cases you want to add relations on objects at runtime. For example, if you want a root controller with links
to your different collection, you would create a Root object with hateoas metadata. But what if you want to create a
"me" relation to the current connected, only if a user is connected ?

We'll use this example.

```php
<?php

// src/Acme/FooBundle/Model/Model.php

use JMS\Serializer\Annotation as Serializer;
use FSC\HateoasBundle\Annotation as Rest;

/**
 * @Rest\Relation("users", href = @Rest\Route("api_user_list"))
 * @Rest\Relation("posts", href = @Rest\Route("api_post_list"))
 *
 * @Serializer\XmlRoot("root")
 */
class Root
{

}
```

```php
<?php

class RootController extends Controller
{
    public function indexAction()
    {
        $root = new Root();

        if (null !== ($user = $this->getUser())) {
            $relationsBuilder = $this->get('fsc_hateoas.metadata.relation_builder.factory')->create();
            $relationsBuilder->add('me', array(
                'route' => 'api_user_get',
                'parameters' => array('id' => $user->getId())
            ));
            $relationsBuilder->add('me2', 'http://api.com/users/32'); // if you want to use the router here

            $this->get('fsc_hateoas.metadata.factory')->addObjectRelations($root, $relationsBuilder->build());
        }

        return new Response($this->get('serializer')->serialize($root, $request->get('_format')));
    }
}
```

### Results

#### No user connected

`GET /api` would result in

```xml
<root>
  <link rel="users" href="http://localhost/api/users"/>
  <link rel="posts" href="http://localhost/api/posts"/>
</root>
```

#### User 32 connected

`GET /api` would result in

```xml
<root>
  <link rel="users" href="http://localhost/api/users"/>
  <link rel="posts" href="http://localhost/api/posts"/>
  <link rel="me" href="http://localhost/api/users/32"/>
  <link rel="me2" href="http://localhost/api/users/32"/>
</root>
```

## Json Format

The bundle supports customizing the keys of links and embedded relations when serializing to Json. They are
controlled by the following configuration:

```yml
# app/config/config.yml

fsc_hateoas:
    json:
        links_key: _links         # default: links
        relations_key: _embedded  # default: relations
```

The above configuration will result in serialization to valid [hal+json](http://stateless.co/hal_specification.html).

## Pagerfanta Handler

Default configuration:

```yaml
fsc_hateoas:
    pagerfanta:
        xml_elements_names_use_serializer_metadata: true
```

With this configuration the pagerfanta handler will use the serializer's xml root name metadata to know what xml element
name should be used for each result. (ie: `/** @Serializer\XmlRootName("user") */ class User {}`)

### Example

```php
<?php

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

public function getListAction($page = 1, $limit = 10)
{
    $query = $this->get('doctrine')->getRepository('User')->createQueryXXX();
    $pager = new Pagerfanta(new DoctrineORMAdapter($query)); // or any Pagerfanta adapter
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

## Add pagerfanta navigation links

The Pagerfanta alone doesn't create links to self/next/previous/last/first pages (only when embedded in relations).

### Example

```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

public function getListAction(Request $request, $page = 1, $limit = 10)
{
    $query = $this->get('doctrine')->getRepository('User')->createQueryXXX();
    $pager = new Pagerfanta(new DoctrineORMPager($query)); // or any Pagerfanta adapter
    $pager->setCurrentPage($page);
    $pager->setMaxPerPage($limit);

    $this->get('fsc_hateoas.metadata.relations_manager')->addBasicRelations($pager); // Automatically add self/first/last/prev/next links

    $this->get('serializer')->getSerializationVisitor('xml')->setDefaultRootName('users');

    return new Response($this->get('serializer')->serialize($pager, 'xml')));
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

class UserController extends Controller
{
    public function getUserFriendsAction($id, $page = 1, $limit = 20)
    {
        $pager = $this->get('acme.foo.user_manager')->getUserFriendsPager($id, $page, $limit);

        $this->get('fsc_hateoas.metadata.relations_manager')->addBasicRelations($pager); // Automatically add self/first/last/prev/next links

        $this->get('serializer')->getSerializationVisitor('xml')->setDefaultRootName('users');

        return new Response($this->get('serializer')->serialize($pager, 'xml'));
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

use JMS\Serializer\Annotation as Serializer;
use FSC\HateoasBundle\Annotation as Rest;

// The bundle will automatically add navigation links to the embedded pagerfanta using the correct route

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

use JMS\Serializer\Annotation as Serializer;
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

## FormView handler

You can serialize FormView. (Available only in XML, if you need this in JSON, feel try to make a PR :) )

Telling your client developers to build requests based on forms, has many advantages, and remove some logic from clients.
It is also really easy to test your api, because you only have to follow links to the form, then use the symfony DomCrawler to
fill and then submit the form.

```php
<?php

class UserController extends Controller
{
    public function getEditFormAction(User $user)
    {
        $formFactory = $this->getKernel()->getContainer()->get('form.factory');
        $form = $formFactory->createBuilder('user')
            ->add('name', 'text')
            ->add('email', 'email')
            ->add('gender', 'choice', array(
                'choices' => array('m' => 'male', 'f' => 'female')
            ))
            ->getForm();
        $formView = $this->get('fsc_hateoas.factory.form_view')->create($form, 'PUT', 'api_user_edit'); // Create form view and add method/action data to the FormView

        $this->get('fsc_hateoas.metadata.relations_manager')->addBasicRelations($formView); // Automatically add self links to the form

        $this->get('serializer')->getSerializationVisitor('xml')->setDefaultRootName('form');

        return new Response($this->get('serializer')->serialize($formView, $request->get('_format')));
    }
}
```

### Results

```xml
<form method="PUT" action="http://localhost/api/users/25">
    <input type="text" name="form[name]" required="required" value="Adrien"/>
    <input type="email" name="form[email]" required="required" value="monsti@gmail.com"/>
    <select name="form[gender]" required="required">
        <option value="m" selected="selected">male</option>
        <option value="f">female</option>
    </select>
</form>
```

## RelationUrlGenerator

You can leverage the fact that the hateoas bundle knows how to create url to an object's relation. This is useful if you
want to generate the `self` url to an object:

```php
$user = ...
$userUrl = $container->get('fsc_hateoas.routing.relation_url_generator')->generateUrl($user, 'self')
```

You can even use the controller trait:

```php
<?php

use FSC\HateoasBundle\Controller\HateoasTrait;

class UserController extends Controller
{
    public function createUserAction(Request $request)
    {
        $user = new User();

        ... // you own stuff

        return Response('', 201, array(
            'Location' => $this->generateSelfUrl($user),
        ));
    }
}
```

## Relation Attributes

There is an attributes array that you can set on the relations that will be serialized to attributes of the
link. This can be useful for things, such as marking the links as being templated. Example:

```php
/**
 * @Rest\Relation("search", href = "http://domain.com/search?{&q}", attributes = { "templated" = "true" })
 */
class User
{
}
```

### Results

```xml
<user id="24">
  <link rel="search" href="http://domain.com/search?{&q}" templated="true" />
</user>
```

or

```json
{
    "id": 24,
    "links": [
        "search": {
            "href": "http:\/\/domain.com/search?{&q}",
            "templated": "true"
        }
    ]
}
```

## Route options

### Using different routers

The bundle supports registering different routers with it. This can be useful for example to use a different router
for templated URLs. To register the router, you need to tag the service with `fsc_hateoas.url_generator`, and you
can provide an `alias`, so that you don't have to write out the full service name. This is useful for example if you
want to provide URI Templates ([RFC-6570](https://tools.ietf.org/html/rfc6570)) by using the
[Hautelook Templated URI Bundle](https://github.com/hautelook/TemplatedUriBundle).

Example:

```yaml
services:
    test.url_generator.prepend:
        class: FSC\HateoasBundle\Tests\Functional\TestBundle\Routing\PrependUrlGenerator
        arguments:
            - @router
        tags:
            - { name: fsc_hateoas.url_generator, alias: prepend }
```

You can then use this router in the annotation by using the `options`. Example:

```php
/**
 * @Rest\Relation("self", href = @Rest\Route("api_user_get", parameters = { "id" = ".id" }, options = { "router" = "prepend" }))
 */
class User
{

}
```

### Creating absolute / relative URLs

You can force a link to be absolute or relative by specifying it as an `option` to the Route. Example:

```php
/**
 * @Rest\Relation("self", href = @Rest\Route("api_user_get", parameters = { "id" = ".id" }, options = { "absolute" = true }))
 */
class User
{

}
```

## Conditionally excluding links

You can add conditions on relations that will determine whether the link should be excluded or not. For example:

```php
/**
 * @Rest\Relation(
 *      "parent",
 *      href = @Rest\Route("api_post_get", parameters = { "id" = ".parent.id"}),
 *      excludeIf = { ".parent" = null }
 * )
 */
class Post
{
}
```

This will not include the `parent` link if the value of `parent` is `null`. This can also be done through the YAML configuration:

```yml
relations:
    - rel: parent
        href:
            route: api_post_get
            parameters: { id: .parent.id }
        exclude_if:
            ".parent": ~
```
