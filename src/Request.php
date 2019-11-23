<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//			Example Header File	passed into withHeaderString from the socket connection							 
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// method, uri, protocol
// GET / HTTP/1.1  
//--------------------------
// Bunch of Key -> Value pairs
//---------------------------
// Host: 127.0.0.1:8008  
// Connection: keep-alive  
// Accept: text/html  
// User-Agent: Chrome/41.0.2272.104  
// Accept-Encoding: gzip, deflate, sdch  
// Accept-Language: en-US,en;q=0.8,de;q=0.6
//
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


namespace anasey\Illuminate\PHPWebServer;

class Request
{
    protected $method = null;
    protected $uri = null;
    protected $parameters = [];
    protected $headers = [];


    public function __construct( $method, $uri, $headers = [] )  
    {
        $this->headers = $headers;
        $this->method = strtoupper( $method );

        // split uri and parameters string
        @list( $this->uri, $params ) = explode( '?', $uri );

        // parse the parmeters
        parse_str( $params, $this->parameters );
    }


    /**
     * Getter for method
     */
    public function method()  
    {
        return $this->method;
    }

    /**
     * uri getter
     */
    public function uri()  
    {
        return $this->uri;
    }

    /**
     * Header getter
     */
    public function header( $key, $default = null )  
    {
        if ( !isset( $this->headers[$key] ) )
        {
            return $default;
        }

        return $this->headers[$key];
    }

    /**
     * Param getter
     */
    public function param( $key, $default = null )  
    {
        if ( !isset( $this->parameters[$key] ) )
        {
            return $default;
        }

        return $this->parameters[$key];
    }

    /**
     * parses the request header
     */
    public static function withHeaderString( $header )
    {
        // explode the header string into lines.
        $lines = explode( "\n", $header );

        // extract the method and uri
        list( $method, $uri ) = explode( ' ', array_shift( $lines ) );

        $headers = [];

        foreach( $lines as $line )
        {
            // clean the line
            $line = trim( $line );

            if ( strpos( $line, ': ' ) !== false )
            {
                list( $key, $value ) = explode( ': ', $line );
                $headers[$key] = $value;
            }
        }

        // create new request object
        return new static( $method, $uri, $headers );
    }

}