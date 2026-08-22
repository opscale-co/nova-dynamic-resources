<?php

declare(strict_types=1);

use Opscale\NovaDynamicResources\Support\ClassFactory;
use Opscale\NovaDynamicResources\Tests\Unit\Support\Fixtures\ClassFactoryTestBase;
use Opscale\NovaDynamicResources\Tests\Unit\Support\Fixtures\ClassFactoryTestChild;
use Opscale\NovaDynamicResources\Tests\Unit\Support\Fixtures\ClassFactoryTestUnrelated;

it('accepts the whitelist base itself and its subclasses', function (): void {
    expect(ClassFactory::isSafeClass(ClassFactoryTestBase::class, ClassFactoryTestBase::class))->toBeTrue();
    expect(ClassFactory::isSafeClass(ClassFactoryTestChild::class, ClassFactoryTestBase::class))->toBeTrue();
});

it('rejects classes outside the whitelist', function (): void {
    expect(ClassFactory::isSafeClass(ClassFactoryTestUnrelated::class, ClassFactoryTestBase::class))->toBeFalse();
});

it('rejects non-existent classes', function (): void {
    expect(ClassFactory::isSafeClass('Totally\\Missing\\Klass', ClassFactoryTestBase::class))->toBeFalse();
});

it('rejects strings containing PHP syntax', function (string $malicious): void {
    expect(ClassFactory::isSafeClass($malicious, ClassFactoryTestBase::class))->toBeFalse();
})->with([
    'space' => ['Foo Bar'],
    'brace' => ['Foo {}'],
    'semicolon' => ['Foo; echo 1'],
    'paren' => ['system(1)'],
    'leading digit' => ['1Foo'],
]);

it('generates a distinct anonymous subclass of a valid base', function (): void {
    $first = ClassFactory::extend(ClassFactoryTestBase::class, ClassFactoryTestBase::class);
    $second = ClassFactory::extend(ClassFactoryTestBase::class, ClassFactoryTestBase::class);

    expect($first)->toBeString();
    expect($second)->toBeString();

    if (! is_string($first) || ! is_string($second)) {
        return;
    }

    expect(is_subclass_of($first, ClassFactoryTestBase::class))->toBeTrue();
    expect($first)->not->toBe($second);
});

it('interpolates a trusted body into the generated class', function (): void {
    $class = ClassFactory::extend(
        ClassFactoryTestBase::class,
        ClassFactoryTestBase::class,
        'public static string $marker = "generated";',
    );

    expect($class)->toBeString();

    if (! is_string($class)) {
        return;
    }

    $reflection = new ReflectionClass($class);
    expect($reflection->getStaticPropertyValue('marker'))->toBe('generated');
});

it('returns null and does not execute an injected base class', function (): void {
    $GLOBALS['class_factory_pwned'] = false;

    $result = ClassFactory::extend(
        'X {} $GLOBALS["class_factory_pwned"] = true; class Y extends ClassFactoryTestBase',
        ClassFactoryTestBase::class,
    );

    expect($result)->toBeNull();
    expect($GLOBALS['class_factory_pwned'])->toBeFalse();

    unset($GLOBALS['class_factory_pwned']);
});
