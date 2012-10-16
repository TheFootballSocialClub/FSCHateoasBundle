<?php

namespace FSC\HateoasBundle\Tests\Fixtures;

use FSC\HateoasBundle\Annotation as Hateoas;

/**
 * @Hateoas\Relation("self", route = "_some_route", params = { "identifier" = "id"})
 * @Hateoas\Relation("alternate", route = "_some_route2")
 * @Hateoas\Relation("alternate", route = "_some_route3")
 * @Hateoas\Relation("home",      route = "homepage")
 * @Hateoas\Relation("friends",
 *     route = "user_friends_list",
 *     params = { "id" = "id" },
 *     content = { "provider_id" = "acme.foo.user_provider", "provider_method" = "getUserFriendsPager", "serializer_xml_element_name_root_metadata" = true }
 * )
 * @Hateoas\Relation("favorites",
 *     route = "user_favorites_list",
 *     params = { "id" = "id" },
 *     content = {
 *          "provider_id" = "acme.foo.favorite_provider",
 *          "provider_method" = "getUserFavoritesPager",
 *          "serializer_type" = "Pagerfanta<custom>",
 *          "serializer_xml_element_name" = "favorites"
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
