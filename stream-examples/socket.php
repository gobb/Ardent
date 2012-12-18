<?php

use Ardent\Streams\Events,
    Ardent\Streams\Memory,
    Ardent\Streams\Socket,
    Ardent\Streams\SslSocket,
    Ardent\Streams\StreamException;

spl_autoload_register(function($class) {
    if (0 === strpos($class, 'Ardent\\')) {
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        $file = dirname(__DIR__) . "/src/$class.php";
        require $file;
    }
});


/**
 * A custom filter to prevent output of the response entity body. If not attached to the
 * Socket stream instance the full entity will be output.
 */
class HeadersOnlyBuffer {
    private $buffer;
    private $headersCompleted = false;
    
    public function __invoke($data) {
        $this->buffer .= $data;
        
        if ($this->headersCompleted) {
            return null;
        }

        if (false !== ($headerEndPos = strpos($this->buffer, "\r\n\r\n"))) {
            $this->headersCompleted = true;
            $return = substr($this->buffer, 0, $headerEndPos + 4);
            $this->buffer = null;
            
            return $return;
        }
        
        return null;
    }
    
    public function getBuffer() {
        return $this->buffer;
    }
}


$request = '' .
    "GET / HTTP/1.1\r\n" .
    "Host: www.google.com\r\n" .
    "User-Agent: test\r\n" .
    "Connection: close\r\n\r\n";

$headersOnlyBuffer = new HeadersOnlyBuffer;
$stream = (new Socket('tcp://www.google.com:80'))->filter($headersOnlyBuffer);
$sink = new Memory;

$stream->subscribe([
    Events::READY => function() use ($stream, $request) {
        $stream->add($request);
    },
    Events::DATA => function($data) use ($sink){
        $sink->add($data);
    },
    Events::DONE => function() use ($sink) {
        $sink->rewind();
        echo $sink;
    },
    Events::ERROR => function(StreamException $e) {
        throw $e;
    }
]);


while ($stream->valid()) {
    $stream->current();
    $stream->next();
}

// Wanna see the response entity body? Uncomment this:
//echo $headersOnlyBuffer->getBuffer();