<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApplicationTest extends TestCase
{
    public function test_root_returns_application_status_as_json(): void
    {
        $this->getJson('/')
            ->assertOk()
            ->assertJsonPath('project', 'UTS Perpustakaan')
            ->assertJsonPath('stage', 'Tahap 3 - REST API kategori dan buku');
    }

    public function test_health_endpoint_is_available(): void
    {
        $this->get('/up')->assertOk();
    }
}
