<?php

declare(strict_types=1);

use Leaf\Form;

test('validates the rule \'date\' for correct values', function ($value) {
  expect(Form::validateField('test', $value, 'date'))
    ->toBe(true);
  expect(Form::errors())->not->toHaveKey('test');
})->with([
  'today',
  '2022-09-22',
]);

test('validates the rule \'date\' for wrong values', function ($value) {
  expect(Form::validateField('test', $value, 'date'))
    ->toBe(false);
  expect(Form::errors())->toHaveKey('test');
  expect(Form::errors()['test'])->toBe('test must be a valid date');
})->with([
  '',
  '        ',
  'no date',
  'date with text 2022-09-22',
  'time: 14:39:56',
]);
