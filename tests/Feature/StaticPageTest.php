<?php

it('can access the partner policies page', function () {
    $response = $this->get(route('static.partner-policies'));
    $response->assertStatus(200);
});

it('can access the client policies page', function () {
    $response = $this->get(route('static.client-policies'));
    $response->assertStatus(200);
});

it('can access other static pages', function () {
    $this->get(route('static.privacy'))->assertStatus(200);
    $this->get(route('static.shipping'))->assertStatus(200);
    $this->get(route('static.terms'))->assertStatus(200);
    $this->get(route('static.faq'))->assertStatus(200);
});
