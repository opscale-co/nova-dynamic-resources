<?php

declare(strict_types=1);

namespace Opscale\NovaDynamicResources\Support;

/**
 * Único punto del paquete que ejecuta eval().
 *
 * PHP no permite `new class extends $variable {}` — la clase padre de una
 * declaración de clase debe ser un identificador literal, no una expresión. Para
 * generar en runtime subclases distintas de una clase base determinada
 * dinámicamente (p. ej. el `related_class` de una Template) la única vía nativa
 * es eval(). Esta clase centraliza ese eval junto con toda su validación, de modo
 * que el vector de inyección quede cerrado en un solo lugar auditable.
 */
final class ClassFactory
{
    /**
     * FQCN válido: solo identificadores y separadores de namespace, con un
     * backslash inicial opcional. Impide cualquier carácter de sintaxis PHP
     * (espacios, `{`, `;`, `(`, etc.), por lo que el string interpolado en el
     * eval solo puede ser una cláusula `extends` legítima.
     */
    private const CLASS_NAME_PATTERN =
        '/^\\\\?[A-Za-z_][A-Za-z0-9_]*(\\\\[A-Za-z_][A-Za-z0-9_]*)*$/';

    /**
     * Crea una subclase anónima distinta de $baseClass en runtime y devuelve su
     * FQCN generado, o null si $baseClass no supera la validación de seguridad.
     *
     * SEGURIDAD: $baseClass se valida (regex + class_exists + whitelist por
     * $mustExtend). $body DEBE ser un literal de confianza definido por el
     * llamador — NUNCA entrada de usuario — ya que se interpola sin validar.
     *
     * @param  string  $baseClass  candidato a clase base (p. ej. related_class de BD)
     * @param  string  $mustExtend  ancestro permitido (whitelist)
     * @param  string  $body  cuerpo constante de la clase anónima
     * @return class-string|null FQCN generado, o null si $baseClass es inseguro
     */
    final public static function extend(string $baseClass, string $mustExtend, string $body = ''): ?string
    {
        if (! self::isSafeClass($baseClass, $mustExtend)) {
            return null;
        }

        $anonymous = eval("return new class extends \\{$baseClass} { {$body} };");

        if (! is_object($anonymous)) {
            return null;
        }

        return $anonymous::class;
    }

    /**
     * Determina si $class puede interpolarse de forma segura como clase base y
     * está dentro del whitelist (es $mustExtend o una subclase suya).
     */
    final public static function isSafeClass(string $class, string $mustExtend): bool
    {
        if (preg_match(self::CLASS_NAME_PATTERN, $class) !== 1) {
            return false;
        }

        if (! class_exists($class)) {
            return false;
        }

        return $class === $mustExtend || is_subclass_of($class, $mustExtend);
    }
}
