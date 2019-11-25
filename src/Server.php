<?php

namespace anasey\Illuminate\PHPWebServer;

class Server
{
    protected $host = null;  
    protected $port = null;  
    protected $socket = null;


    public function __construct( $host, $port )
    {
        $this->host = $host;
        $this->port = $port;

        $this->createSocket();
        $this->bind();
    }

    /**
     * Creates socket for communication
     * AF_INET -> iPv4, TCP & UDP Protocol
     * SOCK_STREAM -> full-duplex connection
     */
    protected function createSocket()  
    {
        $this->socket = socket_create( AF_INET, SOCK_STREAM, 0 );
    }

    /**
     * Binds the socket connection
     * 
     * return an exception if false
     */
    protected function bind()  
    {
        if ( !socket_bind( $this->socket, $this->host, $this->port ) )
        {
            throw new Exception( 'Could not bind: '.$this->host.':'.$this->port.' - '.socket_strerror( socket_last_error() ) );
        }
    }

    /**
     * listen for connection
     */
    public function listen( $callback )
    {
        // check if the callback is valid. Throw an exception if not.
        if ( !is_callable( $callback ) )
        {
            throw new Exception('The given argument should be callable.');
        }

        //Continue listening infitely
        while ( 1 ) 
        {
            //listen for socket connection
            socket_listen( $this->socket );

             // try to get the client socket resource
            // if false we get an error then close the connection and skip
            if ( !$client = socket_accept( $this->socket ) ) 
            {
                socket_close( $client ); continue;
            }

             // create new request instance with the clients header.
            // In the real world setting the max size to 1024 is wrong.
            $request = Request::withHeaderString( socket_read( $client, 1024 ) );

            // execute the callback 
            $response = call_user_func( $callback, $request );

             // check if we really recived an Response object
            // if not return a 404 response object
            if ( !$response || !$response instanceof Response )
            {
                $response = Response::error( 404 );
            }

            // make a string out of the response
            $response = (string) $response;

            // write the response to the client socket
            socket_write( $client, $response, strlen( $response ) );

            // close the connetion so we can accept new ones
            socket_close( $client );
        }
    }
}
