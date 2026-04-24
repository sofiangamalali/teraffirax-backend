<?php

namespace Tests\Unit;

use App\Services\SlugService;
use Tests\TestCase;
use InvalidArgumentException;

class SlugServiceTest extends TestCase
{
    protected $slugService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->slugService = new SlugService();
    }

    public function test_can_generate_valid_slug_from_id()
    {
        $id = 1;
        $slug = $this->slugService->generateSlug($id);

        $this->assertEquals(8, strlen($slug));
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z]+$/', $slug);
    }

    public function test_can_decode_slug_back_to_id()
    {
        $originalId = 123;
        $slug = $this->slugService->generateSlug($originalId);
        $decodedId = $this->slugService->decodeSlug($slug);

        $this->assertEquals($originalId, $decodedId);
    }

    public function test_slug_is_deterministic()
    {
        $id = 500;
        $slug1 = $this->slugService->generateSlug($id);
        $slug2 = $this->slugService->generateSlug($id);

        $this->assertEquals($slug1, $slug2);
    }

    public function test_throws_exception_for_invalid_slug()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->slugService->decodeSlug("invalid!");
    }

    public function test_throws_exception_for_tampered_slug()
    {
        // Generate a valid slug and change one character to likely break the modulo/multiplier check
        $slug = $this->slugService->generateSlug(1);
        $tampered = $slug;
        $tampered[7] = $slug[7] === 'z' ? 'a' : 'z';

        $this->expectException(InvalidArgumentException::class);
        $this->slugService->decodeSlug($tampered);
    }
}
