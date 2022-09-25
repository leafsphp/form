<?php

declare(strict_types=1);

use Leaf\Form;

test('validates the rule \'max\'', function () {
    expect(Form::validateField('test', 'long', 'max:6'))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey('test');

    expect(Form::validateField('test', 'longer', 'max:6'))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey('test');

    expect(Form::validateField('test', 'longest', 'max:6'))
      ->toBe(false);
    expect(Form::errors())->toHaveKey('test');
    expect(Form::errors()['test'])->toBe('test can\'t be more than 6 characters');
});

test('validates the rule \'min\'', function () {
    expect(Form::validateField('test', 'longest', 'min:6'))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey('test');

    expect(Form::validateField('test', 'longer', 'min:6'))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey('test');

    expect(Form::validateField('test', 'long', 'min:6'))
      ->toBe(false);
    expect(Form::errors())->toHaveKey('test');
    expect(Form::errors()['test'])->toBe('test can\'t be less than 6 characters');
});
