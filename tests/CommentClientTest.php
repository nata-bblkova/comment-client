<?php

namespace Tests;

use App\CommentClient;
use JsonException;
use phpmock\Mock;
use phpmock\MockBuilder;
use phpmock\MockEnabledException;
use PHPUnit\Framework\TestCase;

class CommentClientTest extends TestCase
{
    private const BASE_URL = 'http://example.com/';
    private const MOCK_NAME = 'TestCommentName';
    private const MOCK_TEXT = 'TestCommentText';

    private CommentClient $commentClient;
    private MockBuilder $builder;

    /**
     * @throws MockEnabledException
     */
    private function buildMock(): void
    {
        $mock = $this->builder->build();
        $mock->enable();
    }

    private function getMockComments(): array
    {
        $mockComments = [];
        for ($i = 0; $i < 5; $i++) {
            $mockComments[] = ['id' => $i, 'name' => "$i. " . self::MOCK_NAME, 'text' => "$i. " . self::MOCK_TEXT];
        }
        return $mockComments;
    }

    /**
     * @throws MockEnabledException
     */
    private function setMockResponse(array $data): void
    {
        $this->builder->setFunction(function($curl) use ($data) {
            return json_encode(['status' => 'Success', 'data' => $data]);
        });

        $this->buildMock();
    }

    protected function setUp(): void
    {
        $this->commentClient = new CommentClient(self::BASE_URL);

        $builder = new MockBuilder();
        $builder
            ->setNamespace('App')
            ->setName("curl_exec")
        ;

        $this->builder = $builder;
    }

    protected function tearDown(): void
    {
        Mock::disableAll();
    }

    /**
     * @throws MockEnabledException
     * @throws JsonException
     */
    public function testGetComments()
    {
        $mockComments = $this->getMockComments();
        $this->setMockResponse($mockComments);

        $response = $this->commentClient->getComments();

        $this->assertIsArray($response);
        $this->assertEquals('Success', $response['status']);
        $this->assertIsArray($response['data']);
        $this->assertEquals($mockComments, $response['data']);

    }

    /**
     * @throws MockEnabledException
     * @throws JsonException
     */
    public function testPostComment()
    {
        $this->setMockResponse(['name' => self::MOCK_NAME, 'text' => self::MOCK_TEXT]);
        $response = $this->commentClient->postComment(self::MOCK_NAME, self::MOCK_TEXT);

        $this->assertIsArray($response);
        $this->assertEquals('Success', $response['status']);
        $this->assertEquals(['name' => self::MOCK_NAME, 'text' => self::MOCK_TEXT], $response['data']);
    }

    /**
     * @throws MockEnabledException
     * @throws JsonException
     */
    public function testPutComment()
    {
        $mockData = ['id' => 1, 'name' => self::MOCK_NAME, 'text' => self::MOCK_TEXT];
        $this->setMockResponse(['id' => 1, 'name' => self::MOCK_NAME, 'text' => self::MOCK_TEXT]);
        $response = $this->commentClient->putComment(1, self::MOCK_NAME, self::MOCK_TEXT);

        $this->assertIsArray($response);
        $this->assertEquals('Success', $response['status']);
        $this->assertEquals($mockData, $response['data']);
    }
}