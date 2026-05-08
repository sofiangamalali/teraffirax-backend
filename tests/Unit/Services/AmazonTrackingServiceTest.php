<?php

namespace Tests\Unit\Services;

use App\Services\AmazonTrackingService;
use PHPUnit\Framework\TestCase;

class AmazonTrackingServiceTest extends TestCase
{
    protected AmazonTrackingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AmazonTrackingService();
    }

    public function test_append_tracking_id_to_url_without_query(): void
    {
        $url = "https://www.amazon.eg/dp/B0FK2QMC7V";
        $trackingId = "dealsegypt21-21";

        $result = $this->service->appendTrackingId($url, $trackingId);

        $this->assertEquals("https://www.amazon.eg/dp/B0FK2QMC7V?tag=dealsegypt21-21", $result);
    }

    public function test_append_tracking_id_to_url_with_existing_query(): void
    {
        $url = "https://www.amazon.eg/dp/B0FK2QMC7V?ref=dp_vse_pvd_0";
        $trackingId = "dealsegypt21-21";

        $result = $this->service->appendTrackingId($url, $trackingId);

        // Order might change due to parse_str and http_build_query, but both should be there.
        $this->assertStringContainsString("ref=dp_vse_pvd_0", $result);
        $this->assertStringContainsString("tag=dealsegypt21-21", $result);
    }

    public function test_overwrite_existing_tag_in_url(): void
    {
        $url = "https://www.amazon.eg/dp/B0FK2QMC7V?tag=oldtag-21";
        $trackingId = "dealsegypt21-21";

        $result = $this->service->appendTrackingId($url, $trackingId);

        $this->assertStringContainsString("tag=dealsegypt21-21", $result);
        $this->assertStringNotContainsString("tag=oldtag-21", $result);
    }
}
