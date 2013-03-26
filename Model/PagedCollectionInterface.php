<?php

namespace FSC\HateoasBundle\Model;

use Pagerfanta\PagerfantaInterface;

interface PagedCollectionInterface
{
    /**
     * @param PagerfantaInterface $pager
     */
    public function setPager(PagerfantaInterface $pager);

    /**
     * @return PagerfantaInterface
     */
    public function getPager();

    /**
     * @return string
     */
    public function getRel();

    /**
     * @param string $rel
     */
    public function setRel($rel);
}