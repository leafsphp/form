<?php

declare(strict_types=1);

test('array<> can be used to validate arrays', function () {
    $itemsToValidate = ['specialItem2' => 'wrong', 'specialItem3' => ['item here']];

    $success = validator()->validate($itemsToValidate, ['specialItem2' => 'array']);

    expect($success)->toBe(false);
    expect(validator()->errors())->toHaveKey('specialItem2');
    expect(validator()->errors())->not()->toHaveKey('specialItem3');
});

test('array<> can be used to validate array content', function () {
    $itemsToValidate = ['specialItem3' => ['wrong'], 'specialItem4' => ['mail@example.com']];

    validator()->validate($itemsToValidate, [
        'specialItem3' => 'array<email>',
        'specialItem4' => 'array<email>',
    ]);

    expect(validator()->errors())->toHaveKey('specialItem3');
    expect(validator()->errors())->not()->toHaveKey('specialItem4');
});

test('array<> can be used to check if an associative array is an array', function () {
    $itemsToValidate = ['specialItem5' => ['key' => 'value']];

    validator()->validate($itemsToValidate, ['specialItem5' => 'array<>']);

    expect(validator()->errors())->not()->toHaveKey('specialItem5');
});

test('associative arrays can be validated using dot notation', function () {
    $itemsToValidate = [
        'specialItem6' => [
            'key' => 'value',
            'key2' => 'value2',
        ],
    ];

    validator()->validate($itemsToValidate, ['specialItem6.key' => 'string']);
    validator()->validate($itemsToValidate, ['specialItem6.key2' => 'email']);

    expect(validator()->errors())->toHaveKey('specialItem6.key2');
    expect(validator()->errors())->not()->toHaveKey('specialItem6');
});

test('array can validate associative array', function () {
    $array = ['name' => ['first' => 'Max']];
    $validator = [
        'name' => 'array',
        'name.first' => 'string',
    ];

    $data = validator()->validate($array, $validator);

    if (!$data) {
        $this->fail(json_encode(validator()->errors()));
    }

    expect($data)->toBeArray();
});

test('wildcard validation works with simple array of strings', function () {
    $data = ['tags' => ['php', 'laravel', 'leaf']];
    $rules = ['tags.*' => 'string'];

    $result = validator()->validate($data, $rules);

    expect($result)->not()->toBe(false);
    expect(validator()->errors())->toBeEmpty();
});

test('wildcard validation fails when array contains invalid values', function () {
    $data = ['tags' => ['php', 123, 'leaf']];
    $rules = ['tags.*' => 'string'];

    $result = validator()->validate($data, $rules);

    expect($result)->toBe(false);
    expect(validator()->errors())->toHaveKey('tags.1');
});

test('wildcard validation works with nested arrays', function () {
    $data = [
        'users' => [
            ['name' => 'John', 'email' => 'john@example.com'],
            ['name' => 'Jane', 'email' => 'jane@example.com']
        ]
    ];
    $rules = [
        'users.*' => 'array',
        'users.*.name' => 'string',
        'users.*.email' => 'email'
    ];

    $result = validator()->validate($data, $rules);

    expect($result)->not()->toBe(false);
    expect(validator()->errors())->toBeEmpty();
});

test('wildcard validation fails with invalid nested array data', function () {
    $data = [
        'users' => [
            ['name' => 'John', 'email' => 'john@example.com'],
            ['name' => 123, 'email' => 'invalid-email']
        ]
    ];
    $rules = [
        'users.*' => 'array',
        'users.*.name' => 'string',
        'users.*.email' => 'email'
    ];

    $result = validator()->validate($data, $rules);

    expect($result)->toBe(false);
    expect(validator()->errors())->toHaveKey('users.1.name');
    expect(validator()->errors())->toHaveKey('users.1.email');
});

test('multiple wildcard levels work correctly', function () {
    $data = [
        'categories' => [
            'tech' => [
                ['title' => 'PHP Tutorial', 'tags' => ['php', 'web']],
                ['title' => 'Laravel Guide', 'tags' => ['laravel', 'framework']]
            ],
            'design' => [
                ['title' => 'UI Design', 'tags' => ['ui', 'design']],
                ['title' => 'UX Principles', 'tags' => ['ux', 'design']]
            ]
        ]
    ];
    $rules = [
        'categories.*.*' => 'array',
        'categories.*.*.title' => 'string',
        'categories.*.*.tags' => 'array',
        'categories.*.*.tags.*' => 'string'
    ];

    $result = validator()->validate($data, $rules);

    expect($result)->not()->toBe(false);
    expect(validator()->errors())->toBeEmpty();
});

test('wildcard validation with optional fields', function () {
    $data = [
        'products' => [
            ['name' => 'Product 1', 'description' => 'Description 1'],
            ['name' => 'Product 2'],
            ['name' => 'Product 3', 'description' => null]
        ]
    ];
    $rules = [
        'products.*' => 'array',
        'products.*.name' => 'string',
        'products.*.description' => 'optional|string'
    ];

    $result = validator()->validate($data, $rules);

    expect($result)->not()->toBe(false);
    expect(validator()->errors())->toBeEmpty();
});

test('wildcard validation with array<> syntax', function () {
    $data = [
        'users' => [
            ['emails' => ['user1@example.com', 'user1alt@example.com']],
            ['emails' => ['user2@example.com']]
        ]
    ];
    $rules = [
        'users.*' => 'array',
        'users.*.emails' => 'array<email>'
    ];

    $result = validator()->validate($data, $rules);

    expect($result)->not()->toBe(false);
    expect(validator()->errors())->toBeEmpty();
});

test('wildcard validation fails with invalid array<> content', function () {
    $data = [
        'users' => [
            ['emails' => ['user1@example.com', 'invalid-email']],
            ['emails' => ['user2@example.com']]
        ]
    ];
    $rules = [
        'users.*' => 'array',
        'users.*.emails' => 'array<email>'
    ];

    $result = validator()->validate($data, $rules);

    expect($result)->toBe(false);
    expect(validator()->errors())->toHaveKey('users.0.emails');
});

test('deep nested wildcards with mixed data types', function () {
    $data = [
        'companies' => [
            [
                'name' => 'Company A',
                'departments' => [
                    ['name' => 'Engineering', 'budget' => 100000],
                    ['name' => 'Marketing', 'budget' => 50000]
                ]
            ],
            [
                'name' => 'Company B',
                'departments' => [
                    ['name' => 'Sales', 'budget' => 75000]
                ]
            ]
        ]
    ];
    $rules = [
        'companies.*' => 'array',
        'companies.*.name' => 'string',
        'companies.*.departments' => 'array',
        'companies.*.departments.*' => 'array',
        'companies.*.departments.*.name' => 'string',
        'companies.*.departments.*.budget' => 'number'
    ];

    $result = validator()->validate($data, $rules);

    expect($result)->not()->toBe(false);
    expect(validator()->errors())->toBeEmpty();
});
