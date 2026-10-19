<?php

namespace Tests\Feature;

use Tests\TestCase;

class SpaTest extends TestCase
{
    public function test_rotas_do_front_entregam_a_spa(): void
    {
        $this->withoutVite();

        foreach (['/', '/pedidos', '/resumo'] as $url) {
            $this->get($url)->assertOk()->assertSee('<div id="app"></div>', escape: false);
        }
    }

    public function test_rota_de_api_inexistente_nao_cai_na_spa(): void
    {
        $this->getJson('/api/nao-existe')->assertNotFound();
    }
}
