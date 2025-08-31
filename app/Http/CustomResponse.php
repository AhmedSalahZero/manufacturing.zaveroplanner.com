<?php

namespace App\Http;

use Symfony\Component\HttpFoundation\Response as BaseResponse;

class CustomResponse extends BaseResponse
{
    public function sendHeaders()
    {
        if (headers_sent()) {
            return $this;
        }

        foreach ($this->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            $replace = 0 === strcasecmp($name, 'Content-Type');
            foreach ($values as $value) {
                $cleanValue = preg_replace('/[\r\n]+/', '', $value); // Sanitize
                header($name . ': ' . $cleanValue, $replace, $this->statusCode);
            }
        }

        foreach ($this->headers->getCookies() as $cookie) {
            $cleanCookie = preg_replace('/[\r\n]+/', '', $cookie->__toString());
            header('Set-Cookie: ' . $cleanCookie, false, $this->statusCode);
        }

        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode);

        return $this;
    }
}
