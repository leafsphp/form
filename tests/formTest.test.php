<?php

declare(strict_types=1);

use Leaf\Form;

test('can check if data meets expectations', function () {
    $value = '';
    expect(Form::test('required', $value))->toBe(false);
});
