<?php

namespace Sludio\HelperBundle\Guzzle\Cache\NamingStrategy;

use Psr\Http\Message\RequestInterface;

class SubfolderNamingStrategy extends AbstractNamingStrategy
{
    /**
     * {@inheritdoc}
     */
    public function filename(RequestInterface $request)
    {
        $filename = $request->getUri()->getHost();

        if ('' !== $path = urldecode(ltrim($request->getUri()->getPath(), '/'))) {
            $filename .= '/'.$path;
        }

        $filename .= '/'.$request->getMethod();
        $filename .= '_'.$this->getFingerprint($request);

        return $filename;
    }
}