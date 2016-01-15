<?php

class CorePagination
{
    private $records;
    private $pages;
    private $url;
    private $paginas;
    private $total;

    /**
     * CorePagination constructor.
     */
    public function __construct($records, $pages, $url, $pagina, $total)
    {
        $this->records = $records;
        $this->pages = $pages;
        $this->url = $url;
        $this->currentPage = $pagina;
        $this->total = $total;
        $this->paginas = ceil($this->total/$pages);
    }

    /**
     * @return mixed
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * @return float
     */
    public function getPaginas()
    {
        return $this->paginas;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getPages()
    {
        return $this->pages;
    }

}