<?php

namespace Lanin\Laravel\ApiExceptions;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Str;
use Lanin\Laravel\ApiExceptions\Contracts\ShowsPrevious;
use Lanin\Laravel\ApiExceptions\Contracts\ShowsTrace;

abstract class ApiException extends IdException implements Jsonable, \JsonSerializable, Arrayable
{
    protected $headers = [];

    /**
     * @param int $statusCode
     * @param string $id
     * @param string $message
     * @param \Throwable|null $previous
     * @param array $headers
     */
    public function __construct($statusCode = 0, $id = '', $message = '', ?\Throwable $previous = null, array $headers = [])
    {
        $this->headers = $headers;

        parent::__construct($id, $message, $previous, $statusCode);
    }

    /**
     * Return headers array.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert exception to JSON.
     *
     * @param  int $options
     * @return array
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray());
    }

    /**
     * Convert exception to array.
     *
     * @return array
     */
    public function toArray()
    {
        $e = $this;

        if ($e instanceof ShowsPrevious && $this->getPrevious() !== null) {
            $e = $this->getPrevious();
        }

        $return = [];
        $return['id'] = $e instanceof IdException ? $e->getId() : Str::snake(class_basename($e));
        $return['message'] = $e->getMessage();


        if (env('ShowExceptionMeta', false)) {
            if ($e instanceof ApiException) {
                $meta = $this->getMeta();
                if (!empty($meta)) {
                    $return['meta'] = $meta;
                }
            }
        }

        if (env('ShowExceptionTrace', false)) {
            if ($this instanceof ShowsTrace) {
                $return['trace'] = \Symfony\Component\ErrorHandler\Exception\FlattenException::createFromThrowable($e)->getTrace();
            }
        }

        return $return;
    }

    /**
     * Prepare exception for report.
     *
     * @return string
     */
    public function toReport()
    {
        return $this;
    }

    /**
     * Add extra info to the output.
     *
     * @return mixed
     */
    public function getMeta()
    {
    }
}
