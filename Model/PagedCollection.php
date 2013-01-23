<?php

namespace FSC\HateoasBundle\Model;

use Pagerfanta\PagerfantaInterface;

class PagedCollection implements PagedCollectionInterface
{
	/**
     * The pager that proxies the collection
     * @var PagerfantaInterface
     */
    private $pager;

    /**
     * The rel for the paged collection
     * @var string
     */
	private $rel;

    /**
     * @param PagerfantaInterface $pager
     * @param string              $rel
     */
    public function __construct(PagerfantaInterface $pager = null, $rel = "")
    {
        $this->pager = $pager;
        $this->rel = $rel;
    }

    /**
     * @param  PagerfantaInterface $pager
     * @param  string              $rel
     * @return PagedCollection
     */
    public static function create(PagerfantaInterface $pager, $rel)
    {
        return new static($pager, $rel);
    }

	/**
	 * @param PagerfantaInterface $pager
	 */
	public function setPager(PagerfantaInterface $pager)
	{
		$this->pager = $pager;
	}

	/**
	 * @return PagerfantaInterface
	 */
	public function getPager()
	{
		return $this->pager;
	}

	/**
	 * @return string
	 */
	public function getRel()
	{
	    return $this->rel;
	}

	/**
	 * @param string $rel
	 */
	public function setRel($rel)
	{
	    $this->rel = $rel;
	}
}