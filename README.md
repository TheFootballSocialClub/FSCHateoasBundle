FSCHateoasBundle
================

[![Build Status](https://secure.travis-ci.org/TheFootballSocialClub/FSCHateoasBundle.png)](http://travis-ci.org/TheFootballSocialClub/FSCHateoasBundle)

This bundle hooks into the JMSSerializerBundle serialization process, and provides HATEOAS features.
Right now, only adding links is supported.

Even though there are some tests, be aware that this is a work in progress.
For example, only yaml and annotation metadata configuration is supported.

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
 * @Hateoas\Link("self", route = "api_user_get", params = { "id" = "id" })
 * @Hateoas\Link("alternate", route = "user_profile", params = { "user_id" = "id" })
 * @Hateoas\Link("users", route = "api_user_list")
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
    "links": {
        "self": {
            "rel": "self",
            "href": "http:\/\/localhost\/api\/users\/24"
        },
        "alternate": {
            "rel": "alternate",
            "href": "http:\/\/localhost\/profile\/24"
        },
        "users": {
            "rel": "users",
            "href": "http:\/\/localhost\/api\/users"
        }
    }
}
```
