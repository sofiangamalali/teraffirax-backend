<?php

namespace App\Services;

use App\Models\Vendor;
use App\Repositories\Interfaces\VendorRepositoryInterface;
use App\Exceptions\UnsupportedVendorException;
use Illuminate\Support\Str;

class VendorResolverService
{
    public function __construct(
        protected VendorRepositoryInterface $vendorRepo
    ) {}

    /**
     * Resolve vendor from URL securely.
     *
     * @param string $url
     * @return Vendor
     * @throws UnsupportedVendorException
     */
    public function resolveVendor(string $url): Vendor
    {
        $host = parse_url($url, PHP_URL_HOST);
        
        if (!$host) {
            throw new UnsupportedVendorException();
        }

        $host = Str::lower($host);
        $host = preg_replace('/^www\./', '', $host);

        $vendors = $this->vendorRepo->all();

        foreach ($vendors as $vendor) {
            if (!$vendor->domain) {
                continue;
            }

            // Normalize vendor domain
            $vendorDomain = parse_url($vendor->domain, PHP_URL_HOST) ?? $vendor->domain;
            $vendorDomain = Str::lower($vendorDomain);
            $vendorDomain = preg_replace('/^www\./', '', $vendorDomain);

            if ($host === $vendorDomain || Str::endsWith($host, '.' . $vendorDomain)) {
                return $vendor;
            }
        }

        throw new UnsupportedVendorException();
    }
}
