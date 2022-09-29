<?php

declare(strict_types=1);

use Leaf\Form;

test('has some known default validation rules', function () {
    expect(Form::supportedRules())
      ->toContain('required')
      ->toContain('email');
});

test('show error if no message is provided', function () {
    Form::messages('number');
})->throws(\Whoops\Exception\ErrorException::class);

test('do not use an empty error message', function () {
    Form::messages('number', '');
})->throws(\Whoops\Exception\ErrorException::class);

test('use custom error message (string) for built-in rules', function () {
    expect(Form::validateField('test', 'wrong', 'number'))
      ->toBe(false);
    expect(Form::errors())->toHaveKey('test');
    expect(Form::errors()['test'] ?? '')->toBe('test must only contain numbers');

    Form::messages('number', 'Please make sure that {field} contains a number.');

    expect(Form::validateField('test', 'wrong', 'number'))
      ->toBe(false);
    expect(Form::errors())->toHaveKey('test');
    expect(Form::errors()['test'] ?? '')->toBe('Please make sure that test contains a number.');
});

test('use custom error messages (array) for built-in rules', function () {
    Form::messages([
      'number' => '{field} has a custom message',
      'email' => '{field} also has a custom message',
    ]);

    expect(Form::validateField('test', 'wrong', 'number'))
      ->toBe(false);
    expect(Form::errors())->toHaveKey('test');
    expect(Form::errors()['test'] ?? '')->toBe('test has a custom message');

    expect(Form::validateField('test', 'wrong', 'email'))
      ->toBe(false);
    expect(Form::errors())->toHaveKey('test');
    expect(Form::errors()['test'] ?? '')->toBe('test also has a custom message');
});

test('check if the given rule is supported', function () {
    Form::validateField('test', 'wrong', 'rule-does-not-exist');
})->throws(\Whoops\Exception\ErrorException::class);

test('ensure sanitation of input', function ($input, $expected) {
    expect(Form::sanitizeInput($input))->toBe($expected);
})->with([
  ['this should be unchanged', 'this should be unchanged'],
  ['      ', ''],
  ['  space around  ', 'space around'],
  ['<strong>LeafPHP</strong>', '&lt;strong&gt;LeafPHP&lt;/strong&gt;'],
  ["\\\\This works.", "\\This works."],
  ["Foo\\'bar", "Foo&#039;bar"],
]);
