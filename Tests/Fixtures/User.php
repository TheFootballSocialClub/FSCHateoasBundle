<?php

namespace FSC\HateoasBundle\Tests\Fixtures;

use FSC\HateoasBundle\Annotation as Hateoas;

/**
 * @Hateoas\Relation("self", route = "_some_route", parameters = { "identifier" = "id"})
 * @Hateoas\Relation("alternate", route = "_some_route2")
 * @Hateoas\Relation("alternate", route = "_some_route3")
 * @Hateoas\Relation("home",      route = "homepage")
 * @Hateoas\Relation("friends",
 *     route = "user_friends_list",
 *     parameters = { "id" = "id" },
 *     content = { "providerId" = "acme.foo.user_provider", "providerMethod" = "getUserFriendsPager", "serializerXmlElementNameRootMetadata" = true }
 * )
 * @Hateoas\Relation("favorites",
 *     route = "user_favorites_list",
 *     parameters = { "id" = "id" },
 *     content = {
 *          "providerId" = "acme.foo.favorite_provider",
 *          "providerMethod" = "getUserFavoritesPager",
 *          "providerArguments" = { "id", "=3" },
 *          "serializerType" = "Pagerfanta<custom>",
 *          "serializerXmlElementName" = "favorites"
 *     }
 * )
 */
class User
{
    private $id;
    private $username;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getUsername()
    {
        return $this->username;
    }
}
