<?php

namespace Tests;

use App\CommentClient;
use HttpException;
use JsonException;
use phpmock\Mock;
use phpmock\MockBuilder;
use phpmock\MockEnabledException;
use PHPUnit\Framework\TestCase;

class CommentControllerTest extends TestCase
{
    const string BASE_URL = 'http://example.com/';
    private CommentClient $commentClient;
    private MockBuilder $builder;

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

    /**
     * @throws MockEnabledException
     */
    private function buildMock(): void
    {
        $mock = $this->builder->build();
        $mock->enable();
    }

    /**
     * @throws MockEnabledException
     */
    private function buildGetMock(array $comments): void
    {
        $this->builder->setFunction(function($curl) use ($comments) {
                return json_encode(['status' => 'Success', 'data' => $comments]);
            });

        $this->buildMock();
    }

    /**
     * @throws MockEnabledException
     */
    private function buildPostMock(string $name, string $text): void
    {
        $this->builder->setFunction(function($curl) use ($name, $text) {
                return json_encode(['status' => 'Success', 'data' => ['name' => $name, 'text' => $text]]);
            });

        $this->buildMock();
    }

    /**
     * @throws MockEnabledException
     */
    private function buildPutMock(int $id, string $name, string $text): void
    {
        $this->builder->setFunction(function($curl) use ($id, $name, $text) {
            return json_encode(['status' => 'Success', 'data' => ['id' => $id, 'name' => $name, 'text' => $text]]);
        });

        $this->buildMock();
    }

    protected function tearDown(): void
    {
        Mock::disableAll();
    }

    /**
     * @throws HttpException
     * @throws MockEnabledException
     * @throws JsonException
     */
    public function testGetComments()
    {
        $mockName = 'TestGetCommentName';
        $mockText = 'TestGetCommentText';

        $mockComments = [];
        for ($i = 0; $i < 5; $i++) {
            $mockComments[] = ['id' => $i, 'name' => "$i. $mockName", 'text' => "$i. $mockText"];
        }

        $this->buildGetMock($mockComments);
        $response = $this->commentClient->getComments();

        $this->assertIsArray($response);
        $this->assertEquals('Success', $response['status']);
        $this->assertIsArray($response['data']);
        $this->assertEquals($mockComments, $response['data']);

    }

    /**
     * @throws HttpException
     * @throws MockEnabledException
     * @throws JsonException
     */
    public function testPostComment()
    {
        $mockName = 'TestPostCommentName';
        $mockText = 'TestPostCommentText';

        $this->buildPostMock($mockName, $mockText);
        $response = $this->commentClient->postComment($mockName, $mockText);

        $this->assertIsArray($response);
        $this->assertEquals('Success', $response['status']);
        $this->assertEquals(['name' => $mockName, 'text' => $mockText], $response['data']);
    }

    /**
     * @throws HttpException
     * @throws MockEnabledException
     * @throws JsonException
     */
    public function testPutComment()
    {
        $mockId = 1;
        $mockName = 'TestPutCommentName';
        $mockText = 'TestPutCommentText';

        $this->buildPutMock($mockId, $mockName, $mockText);
        $response = $this->commentClient->putComment($mockId, $mockName, $mockText);

        $this->assertIsArray($response);
        $this->assertEquals('Success', $response['status']);
        $this->assertEquals(['id' => $mockId, 'name' => $mockName, 'text' => $mockText], $response['data']);
    }
}