<?php

test('the 404 page uses the JADE error design', function () {
    $this->get('/missing-page')
        ->assertNotFound()
        ->assertSee('Page not found')
        ->assertSee('JADE 2026');
});

test('the 503 page uses the JADE maintenance design', function () {
    $this->view('errors.503')
        ->assertSee('Under maintenance')
        ->assertSee('JADE 2026')
        ->assertSee('JAKARTA DENTAL EXHIBITIONS');
});
