<?php

declare(strict_types=1);

use Leaf\Form;

test('validates the rule \'text\' for correct values', function ($value) {
    expect(Form::validateField('test', $value, 'text'))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey('test');
})->with([
  'Lorem ipsum',
  'ALL CAPS',
  'WORD',
  'all lower',
  'under_score',
  ' ',
]);

test('validates the rule \'text\' for wrong values', function ($value) {
    expect(Form::validateField('test', $value, 'text'))
      ->toBe(false);
    expect(Form::errors())->toHaveKey('test');
    expect(Form::errors()['test'])->toBe('test must only contain text and spaces');
})->with([
  '',
  '1234',
  'invalid 1234',
  'No punctuation.',
]);
