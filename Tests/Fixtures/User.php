<?php

namespace FSC\HateoasBundle\Tests\Fixtures;

use FSC\HateoasBundle\Annotation as Rest;

/**
 * @Rest\Relation("self",      href = @Rest\Route("_some_route", parameters = { "identifier" = "id"}))
 * @Rest\Relation("alternate", href = @Rest\Route("_some_route2"))
 * @Rest\Relation("alternate", href = @Rest\Route("_some_route3"))
 * @Rest\Relation("home",      href = @Rest\Route("homepage"))
 * @Rest\Relation("friends",
 *     href =  @Rest\Route("user_friends_list", parameters = { "id" = "id" }),
 *     embed = @Rest\Content(provider = { "acme.foo.user_provider", "getUserFriendsPager" }, serializerXmlElementNameRootMetadata = true)
 * )
 * @Rest\Relation("favorites",
 *     href =  @Rest\Route("user_favorites_list", parameters = { "id" = "id" }),
 *     embed = @Rest\Content(
 *          provider = {"acme.foo.favorite_provider", "getUserFavoritesPager"},
 *          providerArguments = { "id", "=3" },
 *          serializerType = "Pagerfanta<custom>",
 *          serializerXmlElementName = "favorites"
 *     )
 * )
 * @Rest\Relation("disclosure",
 *     href = @Rest\Route("homepage"),
 *     embed = @Rest\Content(property = ".property")
 * )
 * @Rest\Relation("adrienbrault",
 *     href = "http://adrienbrault.fr"
 * )
 * @Rest\Relation("options",
 *     href = @Rest\Route("homepage", options = { "key1" = "value1" } ),
 *     embed = @Rest\Content(property = ".property")
 * )
 * @Rest\Relation("templated", href = @Rest\Route("homepage"), templated = true)
 */
class User
{
    private $id;
    private $username;
    private $property;

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
