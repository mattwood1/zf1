<?php
class God_Model_WebCrawlerUrl extends God_Model_Base_WebCrawlerUrl
{
    const FOLLOWEDTARGET = 2;

    protected $_curl;

    public static function create(God_Model_Curl $curl)
    {
        $domainTable = new God_Model_WebCrawlerDomainTable();
        $domain = $domainTable->findInsert($curl);

        $url = new God_Model_WebCrawlerUrl();
        $url->url = $curl->lastUrl();
        $url->contenttype = $curl->contentType();
        $url->contentlength = $curl->contentLength();
        $url->statuscode = $curl->statusCode();
        $url->domain_id = $domain->id;

        if ($curl->contentType() == 'image/jpeg') {
            list($width, $height, $type, $attr) = getimagesizefromstring($curl->rawdata());
            $url->width = $width;
            $url->height = $height;
            $url->pixels = $width * $height;
        }

        if (strlen($curl->lastUrl()) > 1000) {
            $url->id = -1;
            return $url;
        }

        $url->save();

        return $url;
    }

    public function processUrl()
    {
        if (!$this->blockEmailAddressLinks()) {
            var_dump('Blocking Email Address');
            return $this;
        }

        $names = array();
        $names = $this->linkModelName();

        $this->_curl = new God_Model_Curl();
        $this->_curl->Curl($this->url, null, null, 10, true);
        $html = $this->_curl->rawdata();

        $links = $this->processHTMLLinks($html);
        $images = $this->processHTMLImages($html);

        if (!$this->checkFake404($links)) {
            var_dump('Blocking fake 404 page');
            return $this;
        }

        checkCPULoad();

        $links = $this->filterLinksFromExistingDBEntries($links);
        $images = $this->filterLinksFromExistingDBEntries($images);

        checkCPULoad();

        if ($links) {
            $priority = 50;
            if ($this->link->priority > $priority) {
                $priority = $this->link->priority;
            }
            $this->addLinks($links, $priority);
        }

        if ($images) {
            $this->addLinks($images, $this->link->priority);
        }

        if ($this->frequency) {
            $this->date = date('Y-m-d H:i:s', strtotime($this->frequency));
        }

        $this->followed = 1;
        $this->save();

    }

    public function linkModelName()
    {
        if (stripos($this->contenttype, "text/html") === false || $this->statuscode != 200) return;

        $modelNames = God_Model_ModelNameTable::getByUrl($this->url);

        return God_Model_WebCrawlerUrlModelName::createLink($this, $modelNames);

    }

    protected function processHTMLLinks($html)
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);

        $links = array();

        $linkspath = new DOMXPath($dom);

        $aTag = $linkspath->evaluate('//a');
        for ($i = 0; $i < $aTag->length; $i++) {
            $link = $aTag->item($i);
            $href = $link->getAttribute('href');
            $links[] = trim($this->_curl->normalizeURL($href, $this->url));
        }

        $links = array_unique(array_filter($links));

        if ($this->domain->reg_filter) {
            foreach ($links as $key => $link) {
                $links[$key] = preg_replace("~" . $this->domain->reg_filter . "~", "", $link);
            }
        }

        return $links;
    }

    protected function processHTMLImages($html)
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);

        $images = array();

        $linkspath = new DOMXPath($dom);

        $imgTag = $linkspath->evaluate('//img');
        for ($i = 0; $i < $imgTag->length; $i++) {
            $image = $imgTag->item($i);
            $src = $image->getAttribute('src');
            $images[] = trim($this->_curl->normalizeURL($src, $this->url));
        }

        $images = array_unique(array_filter($images));

        if ($this->domain->reg_filter) {
            foreach ($images as $key => $image) {
                $images[$key] = preg_replace("~" . $this->domain->reg_filter . "~", "", $image);
            }
        }

        return $images;
    }

    protected function checkFake404($links = array())
    {
        // Check for fake 404 responses that returns to the root page.
        $p_url = parse_url($this->url);
        $root_url = $p_url['scheme'] . '://' . $p_url['host'];

        if ($this->url != $root_url) {

            $root_curl = $this->_curl->Curl($root_url, null, null, 10, true);

            $root_links = $this->processHTMLLinks($root_curl);

            $links_slice = array_slice($links, 0, 1000);
            $root_links_slice = array_slice($root_links, 0, 1000);

            $link_diff = array_diff($links_slice, $root_links_slice);

            // Links are the same as home page. Fake 404 needed
            if (count($link_diff) == 0) {
                $this->statuscode = 404;
                $this->followed = 1;
                $this->save();
                return false;
            }
        }
        return true;
    }

    protected function blockEmailAddressLinks()
    {
        // Don't try to follow links to email addresses
        if (strpos($this->url, '@') !== false) {
            $this->statuscode = 404;
            $this->followed = 1;
            $this->save();
            return false;
        }
        return true;
    }

    protected function filterLinksFromExistingDBEntries($links)
    {
        // Known links
        $linkChunks = array_chunk($links, 1000);
        $knownLinks = array();
        foreach ($linkChunks as $linkChunk) {
            $dblinkQuery = God_Model_WebCrawlerLinkTable::getInstance()
                ->createQuery('wc')
                ->whereIn('link', $linkChunk);

            $dblinks = $dblinkQuery->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

            foreach ($dblinks as $dblink) {
                $knownLinks[] = $dblink['link'];
            }
        }

        $knownLinks = array_unique($knownLinks);
        $knownLinks = array_filter($knownLinks);

        $linksMissing = array_diff($links, $knownLinks);

        return $linksMissing;
    }

    protected function addLinks($links = array(), $priority = 0)
    {
        foreach ($links as $link) {

            checkCPULoad();

            if (strlen($link) <= 1000) {
                $newLink = new God_Model_WebCrawlerLink();
                $newLink->fromArray(array(
                    'link' => trim($link),
                    'parent_url_id' => $this->id,
                    'priority' => $priority
                ));
                $newLink->save();
            }

        }
    }
}
