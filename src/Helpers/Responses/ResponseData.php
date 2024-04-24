<?php

namespace AWF\Extension\Helpers\Responses;

use AWF\Extension\Helpers\Models\ObjectToArray;

class ResponseData extends ObjectToArray
{
    protected bool $success = false;
    protected array $data = [];
    protected array|null $next = null;
    protected string $message = '';
    protected string $timestamp;

    public function __construct(bool $success = false, array $data = [], string $message = '')
    {
        $this->success = $success;
        $this->data = $data;
        $this->message = $message;

        $this->timestamp = (new \DateTime())->format(\DateTimeImmutable::ATOM);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): ResponseData
    {
        $this->success = $success;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): ResponseData
    {
        $this->data = $data;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): ResponseData
    {
        $this->message = $message;
        return $this;
    }

    public function getNext(): array|null
    {
        return $this->next;
    }

    public function setNext(array|null $next): ResponseData
    {
        $this->next = $next;
        return $this;
    }
}