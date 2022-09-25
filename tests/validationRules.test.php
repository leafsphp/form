<?php

declare(strict_types=1);

use Leaf\Form;

test('has some known default validation rules', function () {
  expect(Form::supportedRules())
    ->toContain('required')
    ->toContain('email');
});
