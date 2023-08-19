<?php

declare(strict_types=1);

use Leaf\Form;

test('special validation rules don\'t throw errors when used', function () {
    $itemsToValidate = ['specialItem' => ['wrong', 'wrong2', 'right@example.com']];

    Form::validate($itemsToValidate, ['specialItem' => 'array(email)']);

    expect(Form::errors())->toHaveKey('specialItem');
});

test('array() can be used to validate arrays', function () {
    $itemsToValidate = ['specialItem2' => 'wrong', 'specialItem3' => ['item here']];

    Form::validate($itemsToValidate, ['specialItem2' => 'array()']);

    expect(Form::errors())->toHaveKey('specialItem2');
    expect(Form::errors())->not()->toHaveKey('specialItem3');
});

test('array() can be used to validate array content', function () {
    $itemsToValidate = ['specialItem3' => ['wrong'], 'specialItem4' => ['mail@example.com']];

    Form::validate($itemsToValidate, [
        'specialItem3' => 'array(email)',
        'specialItem4' => 'array(email)',
    ]);

    expect(Form::errors())->toHaveKey('specialItem3');
    expect(Form::errors())->not()->toHaveKey('specialItem4');
});
