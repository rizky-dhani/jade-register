<?php

test('the 404 page uses the JADE error design', function () {
    $this->get('/missing-page')
        ->assertNotFound()
        ->assertSee('Page not found')
        ->assertDontSee('JADE 2026');
});

test('the 503 page uses the JADE maintenance design and logo', function () {
    $this->view('errors.503')
        ->assertSee('Under maintenance')
        ->assertDontSee('JADE 2026')
        ->assertSee('assets/images/JADE_PDGI_LightBG.webp');
});

test('the error page layout includes the JADE logo', function () {
    $this->view('errors.404')
        ->assertSee('Page not found')
        ->assertSee('assets/images/JADE_PDGI_LightBG.webp');
});
