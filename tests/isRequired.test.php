<?php

declare(strict_types=1);

use Leaf\Form;

test("validates the rule 'required'", function () {
    expect(Form::validateField("test", "some-value", "required"))
      ->toBe(true);
    expect(Form::errors())->not->toHaveKey("test");

    expect(Form::validateField("test", "", "required"))
      ->toBe(false);
    expect(Form::errors())->toHaveKey("test");
    expect(Form::errors()["test"])->toBe("test is required");
});
