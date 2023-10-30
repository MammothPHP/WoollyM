<?php

declare(strict_types=1);

use CondorcetPHP\Oliphant\DataFrame;
use Pest\Arch\Expectations\ToBeUsedIn;

beforeEach(function (): void {
    $this->expected = [
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ];

    $this->df1 = new DataFrame($this->expected);

    $this->df2 = clone $this->df1;
});

test('theoric reminder', function():void {
    $o = new stdClass;

    $c1 = new class ($o) {
        public WeakReference $weakRef;
        public WeakMap $weakmap;

        public function __construct(Object $obj) {
            $this->weakRef = WeakReference::create($obj);
            $this->weakmap = new WeakMap;
            $this->weakmap[$obj] = true;
        }

        public function __clone()
        {
            $this->weakRef = WeakReference::create($this->weakRef->get());
            $this->weakmap = clone $this->weakmap;
        }
    };

    $c2 = clone $c1;

    expect($c2->weakRef)->toBeInstanceOf(WeakReference::class);
    expect($c2->weakRef)->toBe($c1->weakRef); // reference are not clonable, but always the same (create method serve the same object)
    expect($c2->weakRef->get())->toBe($o)->toBe($c1->weakRef->get());

    expect($c2->weakmap)->not->toBe($c1->weakmap);
    expect($c2->weakmap[$o])->toBe($c1->weakmap[$o]);

    unset($o);

    expect($c2->weakRef->get())->toBeNull()->toBe($c1->weakRef->get());
    expect(count($c2->weakmap))->toBe(count($c1->weakmap))->toBe(0);
});

it('is not the same object', function (): void {
    expect($this->df1)->not->toBe($this->df2);
});

it('has different column representation', function (string $col): void {
    expect($this->df1->col($col))->not->toBe($this->df2->col($col));
})->with(['a','b','c']);

it('is independent', function (): void {
    $col = 'a';
    $newName = 'newName';

    expect($this->df2->col($col)->rename($newName)->name)->toBe($newName);

    expect($this->df2->toArray()[0])->toHaveKey($newName)->not->toHaveKey($col);
    expect($this->df1->toArray())->toBe($this->expected)->not->toBe($this->df2->toArray());
});