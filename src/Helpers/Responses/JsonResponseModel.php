<?php

namespace AWF\Extension\Helpers\Responses;

class JsonResponseModel
{
    protected ResponseData|NextProductResponseData|null $responseData = null;
    protected int  $status = 200;
    protected array  $headers = [];
    protected int  $options = 0;
    protected bool  $json = false;

    public function __construct(ResponseData|NextProductResponseData|null $data = null, int $status = 200)
    {
        $this->responseData = $data;
        $this->status = $status;
    }

    public function getResponseData(): array
    {
        return $this->responseData->get();
    }

    public function setResponseData(ResponseData $responseData): JsonResponseModel
    {
        $this->responseData = $responseData;
        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): JsonResponseModel
    {
        $this->status = $status;
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): JsonResponseModel
    {
        $this->headers = $headers;
        return $this;
    }

    public function getOptions(): int
    {
        return $this->options;
    }

    public function setOptions(int $options): JsonResponseModel
    {
        $this->options = $options;
        return $this;
    }

    public function isJson(): bool
    {
        return $this->json;
    }

    public function setJson(bool $json): JsonResponseModel
    {
        $this->json = $json;
        return $this;
    }
}
